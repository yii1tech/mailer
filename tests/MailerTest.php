<?php

namespace yii1tech\mailer\test;

use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\NullTransport;
use Symfony\Component\Mime\Email;
use Yii;
use yii1tech\mailer\Mailer;
use yii1tech\mailer\TemplatedEmail;
use yii1tech\mailer\transport\ArrayTransport;
use yii1tech\mailer\View;

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

        $email = (new Email())
            ->addTo('test@example.com')
            ->subject('Test subject')
            ->text('Test body');

        $mailer->send($email);

        $sentMessage = $transport->getLastSentMessage();

        $this->assertSame('noreply@example.com', $sentMessage->getFrom()[0]->getAddress());
        $this->assertSame('My App', $sentMessage->getFrom()[0]->getName());
        $this->assertSame('test-bcc@example.com', $sentMessage->getBcc()[0]->getAddress());
    }

    public function testSetupView(): void
    {
        /** @var Mailer $mailer */
        $mailer = Yii::createComponent([
            'class' => Mailer::class,
            'view' => [
                'layout' => 'test-layout',
            ],
        ]);

        $view = $mailer->getView();

        $this->assertTrue($view instanceof View);
        $this->assertSame('test-layout', $view->layout);
    }

    public function testRender(): void
    {
        $transport = new ArrayTransport();

        /** @var Mailer $mailer */
        $mailer = Yii::createComponent([
            'class' => Mailer::class,
            'transport' => $transport,
        ]);

        $email = (new TemplatedEmail())
            ->addFrom('noreply@example.com')
            ->addTo('test@example.com')
            ->subject('Test subject')
            ->textTemplate('plain')
            ->htmlTemplate('plain')
            ->context([
                'name' => 'John Doe',
            ]);

        $mailer->send($email);

        $sentMessage = $transport->getLastSentMessage();

        $this->assertStringContainsString('Name = John Doe', $sentMessage->getTextBody());
        $this->assertStringContainsString('Name = John Doe', $sentMessage->getHtmlBody());
    }
}