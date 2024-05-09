<?php

namespace yii1tech\mailer;

use Symfony\Component\Mime\Email;

/**
 * TemplatedEmail allows specification of the email body parts as a rendering of the templates.
 *
 * Usage example:
 *
 * ```
 * $email = (new TemplatedEmail())
 *     ->addTo('test@example.com')
 *     ->subject('Greetings')
 *     ->textTemplate('greetings-text')
 *     ->htmlTemplate('greetings-html')
 *     ->context([
 *         'name' => 'John Doe',
 *     ]);
 *
 * Yii::app()->mailer->send($email);
 * ```
 *
 * @see \yii1tech\mailer\View
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class TemplatedEmail extends Email implements TemplatedEmailContract
{
    use TemplatedEmailTrait;
}