<?php

namespace yii1tech\mailer\test;

use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\NullTransport;
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
}