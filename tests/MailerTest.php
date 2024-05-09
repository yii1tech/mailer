<?php

namespace yii1tech\mailer\test;

use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mime\Email;
use Yii;
use yii1tech\mailer\Mailer;
use yii1tech\mailer\transport\ArrayTransport;

class MailerTest extends TestCase
{
    public function testCreateTransportFromDsn(): void
    {
        /** @var Mailer $mailer */
        $mailer = Yii::createComponent([
            'class' => Mailer::class,
            'dsn' => 'smtp://user:pass@smtp.example.com:25',
        ]);

        $this->assertTrue($mailer->getTransport() instanceof SmtpTransport);
    }

    public function testCreateTransportFromClassName(): void
    {
        /** @var Mailer $mailer */
        $mailer = Yii::createComponent([
            'class' => Mailer::class,
            'transport' => NullTransport::class,
        ]);

        $this->assertTrue($mailer->getTransport() instanceof NullTransport);
    }

    public function testCreateTransportFromCallable(): void
    {
        /** @var Mailer $mailer */
        $mailer = Yii::createComponent([
            'class' => Mailer::class,
            'transport' => function () {
                return new NullTransport();
            },
        ]);

        $this->assertTrue($mailer->getTransport() instanceof NullTransport);
    }

    public function testCreateArrayTransport(): void
    {
        /** @var Mailer $mailer */
        $mailer = Yii::createComponent([
            'class' => Mailer::class,
            'dsn' => 'array',
        ]);

        $this->assertTrue($mailer->getTransport() instanceof ArrayTransport);

        $mailer = Yii::createComponent([
            'class' => Mailer::class,
            'dsn' => 'array://',
        ]);

        $this->assertTrue($mailer->getTransport() instanceof ArrayTransport);
    }

    public function testSetupSymfonyMailer(): void
    {
        $mailer = new Mailer();

        $symfonyMailer = new SymfonyMailer(new NullTransport());

        $mailer->setSymfonyMailer($symfonyMailer);
        $this->assertSame($symfonyMailer, $mailer->getSymfonyMailer());
    }

    public function testDefaultHeaders(): void
    {
        $transport = new ArrayTransport();

        /** @var Mailer $mailer */
        $mailer = Yii::createComponent([
            'class' => Mailer::class,
            'transport' => $transport,
            'defaultHeaders' => [
                'From' => 'My App<noreply@example.com>',
                'Bcc' => 'test-bcc@example.com',
            ],
        ]);

        $email = new Email();
        $email->addTo('test@example.com');
        $email->subject('Test subject');
        $email->text('Test body');

        $mailer->send($email);

        $sentMessage = $transport->getLastSentMessage();

        $this->assertSame('noreply@example.com', $sentMessage->getFrom()[0]->getAddress());
        $this->assertSame('My App', $sentMessage->getFrom()[0]->getName());
        $this->assertSame('test-bcc@example.com', $sentMessage->getBcc()[0]->getAddress());
    }
}