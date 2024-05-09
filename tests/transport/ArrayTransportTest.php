<?php

namespace yii1tech\mailer\test\transport;

use Symfony\Component\Mime\Email;
use Yii;
use yii1tech\mailer\Mailer;
use yii1tech\mailer\test\TestCase;
use yii1tech\mailer\transport\ArrayTransport;

class ArrayTransportTest extends TestCase
{
    public function testSend(): void
    {
        $transport = new ArrayTransport();

        /** @var Mailer $mailer */
        $mailer = Yii::createComponent([
            'class' => Mailer::class,
            'transport' => $transport,
        ]);

        $email = (new Email())
            ->from('noreply@example.com')
            ->addTo('test@example.com')
            ->subject('Test subject')
            ->text('Test body');

        $mailer->send($email);

        $this->assertCount(1, $transport->getSentMessages());

        $sentMessage = $transport->getLastSentMessage();
        $this->assertSame('noreply@example.com', $sentMessage->getFrom()[0]->getAddress());

        $messages = $transport->filterSentMessages(function (Email $message) {
            return $message->getSubject() === 'Test subject';
        });
        $this->assertCount(1, $messages);

        $messages = $transport->filterSentMessages(function (Email $message) {
            return $message->getSubject() === 'Fake';
        });
        $this->assertCount(0, $messages);
    }
}