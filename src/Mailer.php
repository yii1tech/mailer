<?php

namespace yii1tech\mailer;

use CApplicationComponent;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\Mime\RawMessage;

/**
 * @see https://symfony.com/doc/current/mailer.html
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
     * Note: this property will have no effect in case {@see $transport} property is explicitly set.
     */
    public $dsn;

    /**
     * @var \Symfony\Component\Mailer\Mailer Swift mailer instance.
     */
    private $_symfonyMailer;
    /**
     * @var \Symfony\Component\Mailer\Transport\TransportInterface|\Closure|string|null transport instance or its class name or factory PHP callback.
     */
    private $_transport;

    public function send(RawMessage $message, ?Envelope $envelope = null): void
    {
        $this->getSymfonyMailer()->send($message, $envelope);
    }

    public function setSymfonyMailer(?SymfonyMailer $symfonyMailer): self
    {
        $this->_symfonyMailer = $symfonyMailer;

        return $this;
    }

    public function getSymfonyMailer(): SymfonyMailer
    {
        if ($this->_symfonyMailer === null) {
            $this->_symfonyMailer = $this->createSymfonyMailer();
        }

        return $this->_symfonyMailer;
    }

    protected function createSymfonyMailer(): SymfonyMailer
    {
        return new SymfonyMailer($this->getTransport());
    }

    /**
     * @param \Symfony\Component\Mailer\Transport\TransportInterface|\Closure|string|null $transport transport instance or its class name or factory PHP callback.
     * @return static self reference.
     */
    public function setTransport($transport): self
    {
        $this->_transport = $transport;

        return $this;
    }

    /**
     * @return \Symfony\Component\Mailer\Transport\TransportInterface mail transport instance.
     */
    public function getTransport(): TransportInterface
    {
        if (empty($this->_transport)) {
            if (empty($this->dsn)) {
                throw new \LogicException('Either "' . get_class($this) . '::$dsn" or "' . get_class($this) . '::$transport" property should be set.');
            }

            $this->_transport = Transport::fromDsn($this->dsn);

            return $this->_transport;
        }

        if (!is_object($this->_transport) || $this->_transport instanceof \Closure) {
            $this->_transport = $this->createTransport($this->_transport);
        }

        return $this->_transport;
    }

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
}