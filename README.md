<p align="center">
    <a href="https://github.com/yii1tech" target="_blank">
        <img src="https://avatars.githubusercontent.com/u/134691944" height="100px">
    </a>
    <h1 align="center">Symfony Mailer Extension for Yii 1</h1>
    <br>
</p>

This extension provides integration of [Symfony Mailer](https://symfony.com/doc/current/mailer.html) in the Yii1 application.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://img.shields.io/packagist/v/yii1tech/mailer.svg)](https://packagist.org/packages/yii1tech/mailer)
[![Total Downloads](https://img.shields.io/packagist/dt/yii1tech/mailer.svg)](https://packagist.org/packages/yii1tech/mailer)
[![Build Status](https://github.com/yii1tech/mailer/workflows/build/badge.svg)](https://github.com/yii1tech/mailer/actions)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii1tech/mailer
```

or add

```json
"yii1tech/mailer": "*"
```

to the "require" section of your composer.json.


Usage
-----

This extension provides integration of [Symfony Mailer](https://symfony.com/doc/current/mailer.html) in the Yii1 application.
Application configuration example:

```php
<?php

return [
    'components' => [
        'mailer' => [
            'class' => yii1tech\mailer\Mailer::class,
            'dsn' => 'smtp://user:pass@smtp.example.com:25',
        ],
    ],
    // ...
];
```

> Note: please refer to the [Symfony Mailer Manual](https://symfony.com/doc/current/mailer.html#transport-setup) for the instructions
  regarding the transport DSN specification.

Usage example:

```php
<?php

use Symfony\Component\Mime\Email;

$email = (new Email())
    ->addFrom('noreply@example.com')
    ->addTo('johndoe@example.com')
    ->subject('Greetings')
    ->text('Welcome to our application')
    ->html('<h1>Welcome to our application</h1>');

Yii::app()->mailer->send($email);
```


### Configuring Emails Globally <span id="configuring-emails-globally"></span>

In order to simplify the code and avoid its duplication, you can set the default headers, which should be applied for
each sending email.
Application configuration example:

```php
<?php

return [
    'components' => [
        'mailer' => [
            'class' => yii1tech\mailer\Mailer::class,
            'defaultHeaders' => [
                'From' => 'My Application<noreply@example.com>',
                'Bcc' => 'test-via-bcc@example.com',
                'X-Custom-Header' => 'foobar',
            ],
            // ...
        ],
    ],
    // ...
];
```


### Template rendering <span id="template-rendering"></span>

You may specify the content for the email body to be a result of rendering a particular view templates.
It works in the similar way as rendering the views in your Web controllers.
You'll need to use `\yii1tech\mailer\TemplatedEmail` in order to specify the templates.

```php
<?php

use yii1tech\mailer\TemplatedEmail;

$email = (new TemplatedEmail())
    ->addFrom('noreply@example.com')
    ->addTo('johndoe@example.com')
    ->subject('Greetings')
    ->textTemplate('greetings-text') // Text Body will be set as render of 'views/mail/greetings-text.php'
    ->htmlTemplate('greetings-html') // HTML Body will be set as render of 'views/mail/greetings-html.php'
    ->context([
        'name' => 'John Doe', // variables, which will be available inside the templates
    ]);

Yii::app()->mailer->send($email); // actual rendering takes place just before the sending
```

By default, the view templates will be searched in the directory "views/mail" in the application base path.
But you may configure `\yii1tech\mailer\Mailer::$view` component to use a different folder.
You can also set a global layout for all template rendering.
Application configuration example:

```php
<?php

return [
    'components' => [
        'mailer' => [
            'class' => yii1tech\mailer\Mailer::class,
            'view' => [
                'viewPath' => dirname(__DIR__) . '/views/mail',
                'layout' => 'default-layout',
            ],
            // ...
        ],
    ],
    // ...
];
```

Inside the template file the following variables are always accessible:

- `$this` - reference to `\yii1tech\mailer\View` instance.
- `$_message` - reference to email message instance, which is rendered.

Template example:

```php
<?php
/**
 * @var $this \yii1tech\mailer\View
 * @var $_message \yii1tech\mailer\TemplatedEmail
 * @var $name string
 */
 
$_message->subject('Email subject defined within the template');

$this->layout = 'particular-layout';
?>
<h1>Greetings</h1>
<p>Context var "name" = <?php echo CHtml::encode($name) ?></p>
```


### Writing unit tests <span id="writing-unit-tests"></span>

You can use `\yii1tech\mailer\transport\ArrayTransport` while writing unit tests for your application.
This transport will store all incoming email messages inside the internal field instead of actual sending them.
Application configuration example:

```php
<?php

return [
    'components' => [
        'mailer' => [
            'class' => yii1tech\mailer\Mailer::class,
            'dsn' => 'array://',
            // ...
        ],
    ],
    // ...
];
```

Unit test example:

```php
<?php

use Symfony\Component\Mime\Email;

class MailTest extends TestCase
{
    public function testMail(): void
    {
        // code under test here

        /** @var \yii1tech\mailer\transport\ArrayTransport $transport */
        $transport = Yii::app()->mailer->getTransport();
        
        // retrieve the last sent email message:
        $sentMessage = $transport->getLastSentMessage();
        
        $this->assetNotNull($sentMessage);
        $this->assertSame('johndoe@example.com', $sentMessage->getTo()[0]->getAddress());
        // ...
        
        // retrieve the all sent email messages. matching the callback condition:
        $messages = $transport->filterSentMessages(function (Email $message) {
            return $message->getSubject() === 'Greetings';
        });
        $this->assertCount(1, $messages);
        // ...
    }
}
```
