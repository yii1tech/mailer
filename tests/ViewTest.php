<?php

namespace yii1tech\mailer\test;

use yii1tech\mailer\View;

class ViewTest extends TestCase
{
    public function testGetViewFile(): void
    {
        $view = new View();

        $filename = $view->getViewFile('plain');
        $this->assertSame(__DIR__ . '/views/mail/plain.php', $filename);

        $filename = $view->getViewFile('application.bootstrap');
        $this->assertSame(__DIR__ . '/bootstrap.php', $filename);
    }

    public function testRenderWithoutLayout(): void
    {
        $view = new View();
        $view->layout = null;

        $content = $view->render('plain', [
            'name' => 'John Doe',
        ]);

        $this->assertStringContainsString('Name = John Doe', $content);
    }

    public function testRenderWithLayout(): void
    {
        $view = new View();
        $view->layout = 'layout';

        $content = $view->render('plain', [
            'name' => 'John Doe',
        ]);

        $this->assertStringContainsString('Name = John Doe', $content);
        $this->assertStringContainsString('<!--Header-->', $content);
        $this->assertStringContainsString('<!--Footer-->', $content);
    }

    public function testSetupViewRenderer(): void
    {
        $view = new View();
        $view->setViewRenderer([
            'class' => \CPradoViewRenderer::class,
        ]);

        $viewRenderer = $view->getViewRenderer();

        $this->assertTrue($viewRenderer instanceof \CPradoViewRenderer);
    }
}