<?php

namespace yii1tech\mailer\transport;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\RawMessage;

/**
 * ArrayTransport stores incoming messages in the internal list, instead of sending them.
 *
 * This transport can be useful while writing unit tests.
 *
 * In {@see \yii1tech\mailer\Mailer::getTransport()} this transport will be created for DSN 'array://'.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ArrayTransport extends AbstractTransport
{
    /**
     * @var \Symfony\Component\Mime\RawMessage[]|\Symfony\Component\Mime\Email[]|\yii1tech\mailer\TemplatedEmail[]
     */
    private $sentMessages = [];

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $this->sentMessages[] = $message->getOriginalMessage();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return 'array://';
    }

    /**
     * @return \Symfony\Component\Mime\RawMessage[]|\Symfony\Component\Mime\Email[]|\yii1tech\mailer\TemplatedEmail[] sent messages.
     */
    public function getSentMessages(): array
    {
        return $this->sentMessages;
    }

    /**
     * @return \Symfony\Component\Mime\RawMessage|\Symfony\Component\Mime\Email|\yii1tech\mailer\TemplatedEmail|null last sent message.
     */
    public function getLastSentMessage(): ?RawMessage
    {
        if (empty($this->sentMessages)) {
            return null;
        }

        $sentMessages = $this->sentMessages;

        return array_pop($sentMessages);
    }

    /**
     * Clears the internal list of sent messages.
     *
     * @return static self reference.
     */
    public function clearSentMessages(): self
    {
        $this->sentMessages = [];

        return $this;
    }

    /**
     * Filters the internal list of sent messages according to the given callback.
     *
     * Callback signature:
     *
     * ```
     * function (\Symfony\Component\Mailer\SentMessage $message): bool
     * ```
     *
     * The callback is applied over each sent message.
     * In case the callback returns `true` the message will be returned in result set.
     *
     * @param callable $filterCallback filter callback.
     * @return \Symfony\Component\Mime\RawMessage[]|\Symfony\Component\Mime\Email[]|\yii1tech\mailer\TemplatedEmail[] sent messages.
     */
    public function filterSentMessages(callable $filterCallback): array
    {
        $result = [];

        foreach ($this->sentMessages as $message) {
            if (call_user_func($filterCallback, $message)) {
                $result[] = $message;
            }
        }

        return $result;
    }
}