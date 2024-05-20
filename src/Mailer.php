<?php

namespace yii1tech\mailer;

use CApplicationComponent;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;
use Yii;
use yii1tech\mailer\transport\ArrayTransport;

/**
 * Mailer allows email sending.
 *
 * Application configuration example:
 *
 * ```
 * return [
 *     'components' => [
 *         'mailer' => [
 *             'class' => yii1tech\mailer\Mailer::class,
 *             'dsn' => 'smtp://user:pass@smtp.example.com:25',
 *             'view' => [
 *                 'layout' => 'default-layout',
 *             ],
 *         ],
 *     ],
 *     // ...
 * ];
 * ```
 *
 * Usage example:
 *
 * ```
 * use Symfony\Component\Mime\Email;
 *
 * $email = (new Email())
 *     ->addFrom('noreply@example.com')
 *     ->addTo('test@example.com')
 *     ->subject('Test subject')
 *     ->text('Test body');
 *
 * Yii::app()->mailer->send($email);
 * ```
 *
 * @see https://symfony.com/doc/current/mailer.html
 *
 * @property \Symfony\Component\Mailer\Transport\TransportInterface|\Closure|string $transport mail transport to be used.
 * @property \yii1tech\mailer\View|array|string $view view instance to be used for template rendering.
 * @property \Symfony\Component\Mailer\Mailer $symfonyMailer related Symfony mailer instance.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Mailer extends CApplicationComponent
{
    /**
     * @var string the DSN string for the mail transport creation.
     * For example:
     *
     * - 'smtp://user:pass@smtp.example.com:25'
     * - 'sendmail://default'
     *
     * > Note: this property will have no effect in case {@see $transport} property is explicitly set.
     */
    public $dsn;

    /**
     * @var array<string, string|string[]> the default headers to be applied for each sending email.
     * For example:
     *
     * ```
     * [
     *     'From' => 'My Application<noreply@example.com>',
     *     'Bcc' => 'test-via-bcc@example.com',
     *     'X-Custom-Header' => 'foobar',
     * ]
     * ```
     */
    public $defaultHeaders = [];

    /**
     * @var \Psr\Log\LoggerInterface|null PSR logger to be applied to the mail transport.
     * @see https://github.com/yii1tech/psr-log
     */
    public $logger;

    /**
     * @var \Symfony\Component\Mailer\Mailer Symfony mailer instance.
     */
    private $_symfonyMailer;

    /**
     * @var \Symfony\Component\Mailer\Transport\TransportInterface|\Closure|string|null transport instance or its class name or factory PHP callback.
     */
    private $_transport;

    /**
     * @var \yii1tech\mailer\View|array view instance or its array configuration.
     */
    private $_view = [];

    /**
     * Sends the given message.
     *
     * @param \Symfony\Component\Mime\RawMessage|\Symfony\Component\Mime\Email|\yii1tech\mailer\TemplatedEmailContract $message message to be sent.
     * @param \Symfony\Component\Mailer\Envelope|null $envelope envelope instance.
     * @return void
     */
    public function send(RawMessage $message, ?Envelope $envelope = null): void
    {
        foreach ($this->defaultHeaders as $name => $value) {
            if (in_array(strtolower($name), ['from', 'to', 'cc', 'bcc', 'reply-to'])) {
                $value = (array) $value;
            }
            $message->getHeaders()->addHeader($name, $value);
        }

        if ($message instanceof TemplatedEmailContract) {
            $message = $this->render($message);
        }

        $this->getSymfonyMailer()->send($message, $envelope);
    }

    /**
     * Sets the Symfony mailer to be used directly.
     *
     * Using this method makes {@see $dsn} and {@see $transport} values to be ignored.
     *
     * @param \Symfony\Component\Mailer\Mailer|null $symfonyMailer Symfony mailer instance.
     * @return static self reference.
     */
    public function setSymfonyMailer(?SymfonyMailer $symfonyMailer): self
    {
        $this->_symfonyMailer = $symfonyMailer;

        return $this;
    }

    /**
     * @return \Symfony\Component\Mailer\Mailer Symfony mailer instance.
     */
    public function getSymfonyMailer(): SymfonyMailer
    {
        if ($this->_symfonyMailer === null) {
            $this->_symfonyMailer = $this->createSymfonyMailer();
        }

        return $this->_symfonyMailer;
    }

    /**
     * Creates default Symfony mailer instance with pre-configured transport.
     *
     * @return \Symfony\Component\Mailer\Mailer Symfony mailer instance.
     */
    protected function createSymfonyMailer(): SymfonyMailer
    {
        return new SymfonyMailer($this->getTransport());
    }

    /**
     * Sets the mail transport to be used.
     *
     * @param \Symfony\Component\Mailer\Transport\TransportInterface|\Closure|string|null $transport transport instance or its class name or factory PHP callback.
     * @return static self reference.
     */
    public function setTransport($transport): self
    {
        $this->_transport = $transport;

        return $this;
    }

    /**
     * Returns the configured mail transport.
     *
     * @return \Symfony\Component\Mailer\Transport\TransportInterface mail transport instance.
     */
    public function getTransport(): TransportInterface
    {
        if (empty($this->_transport)) {
            if (empty($this->dsn)) {
                throw new \LogicException('Either "' . get_class($this) . '::$dsn" or "' . get_class($this) . '::$transport" property should be set.');
            }

            if ($this->dsn === 'array://') {
                $this->_transport = new ArrayTransport(null, $this->logger);
            } else {
                $this->_transport = Transport::fromDsn($this->dsn, null, null, $this->logger);
            }

            return $this->_transport;
        }

        if (!is_object($this->_transport) || $this->_transport instanceof \Closure) {
            $this->_transport = $this->createTransport($this->_transport);
        }

        return $this->_transport;
    }

    /**
     * Creates new transport instance from configuration.
     *
     * @param callable|string $config transport class name or factory callback.
     * @return \Symfony\Component\Mailer\Transport\TransportInterface transport instance.
     */
    protected function createTransport($config): TransportInterface
    {
        if (is_callable($config)) {
            return call_user_func($config);
        }

        if (is_string($config)) {
            return new $config;
        }

        throw new \LogicException('Transport configuration should be either a factory callback or a string class name.');
    }

    /**
     * @return \yii1tech\mailer\View view instance.
     */
    public function getView()
    {
        if (!is_object($this->_view)) {
            $config = $this->_view;
            if (is_array($config) && !isset($config['class'])) {
                $config['class'] = View::class;
            }

            $this->_view = Yii::createComponent($config);
        }

        return $this->_view;
    }

    /**
     * @param \yii1tech\mailer\View|array|string $view view instance or its configuration.
     * @return static self reference.
     */
    public function setView($view): self
    {
        $this->_view = $view;

        return $this;
    }

    /**
     * Renders the email message, populating its body parts from the templates.
     *
     * @param \Symfony\Component\Mime\RawMessage|\Symfony\Component\Mime\Email|\yii1tech\mailer\TemplatedEmailContract $message raw message.
     * @return \Symfony\Component\Mime\RawMessage rendered message.
     */
    protected function render(RawMessage $message): RawMessage
    {
        if ($message->isRendered()) {
            return $message;
        }

        if (($textTemplate = $message->getTextTemplate()) !== null) {
            $text = $this->getView()->render(
                $textTemplate,
                array_merge($message->getContext(), ['_message' => $message]),
                $message->getLocale()
            );

            $message->text($text);
        }

        if (($htmlTemplate = $message->getHtmlTemplate()) !== null) {
            $html = $this->getView()->render(
                $htmlTemplate,
                array_merge($message->getContext(), ['_message' => $message]),
                $message->getLocale()
            );

            $message->html($html);
        }

        $message->markAsRendered();

        return $message;
    }
}