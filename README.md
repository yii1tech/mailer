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
            'class' => yii1tech\mailer\Mailer,
            'dsn' => 'smtp://user:pass@smtp.example.com:25',
        ],
    ],
    // ...
];
```

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

Application configuration example:

```php
<?php

return [
    'components' => [
        'mailer' => [
            'class' => yii1tech\mailer\Mailer,
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

```php
<?php

use yii1tech\mailer\TemplatedEmail;

$email = (new TemplatedEmail())
    ->addFrom('noreply@example.com')
    ->addTo('johndoe@example.com')
    ->subject('Greetings')
    ->textTemplate('greetings-text')
    ->htmlTemplate('greetings-html')
    ->context([
        'name' => 'John Doe',
    ]);

Yii::app()->mailer->send($email);
```


### Writing unit tests <span id="writing-unit-tests"></span>

```php
<?php

return [
    'components' => [
        'mailer' => [
            'class' => yii1tech\mailer\Mailer,
            'dsn' => 'array://',
            // ...
        ],
    ],
    // ...
];
```

```php
<?php

class MailTest extends TestCase
{
    public function testMail(): void
    {
        // code under test here
        
        /** @var \Symfony\Component\Mime\Email $sentMessage */
        $sentMessage = Yii::app()->mailer->getTransport()->getLastSentMessage();
        
        $this->assetNotNull($sentMessage);
        $this->assertSame('johndoe@example.com', $sentMessage->getTo()[0]->getAddress());
        // ...
    }
}
```
