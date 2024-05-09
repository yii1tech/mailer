<?php

namespace yii1tech\mailer;

use Symfony\Component\Mime\Email;

/**
 * TemplatedEmail allows specification of the email body parts as a rendering of the templates.
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