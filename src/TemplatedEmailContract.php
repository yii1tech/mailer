<?php

namespace yii1tech\mailer;

/**
 * TemplatedEmailContract defines the email message, which content, should be rendered from the templates.
 *
 * This interface should be applied to descendant of {@see \Symfony\Component\Mime\Email}.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
interface TemplatedEmailContract
{
    /**
     * @return string|null template name for text body.
     */
    public function getTextTemplate(): ?string;

    /**
     * @return string|null template name for HTML body.
     */
    public function getHtmlTemplate(): ?string;

    /**
     * @return array<string, mixed> template context variables.
     */
    public function getContext(): array;

    /**
     * @return string|null locale to be used during template rendering.
     */
    public function getLocale(): ?string;

    /**
     * @return bool whether the templates have been already rendered.
     */
    public function isRendered(): bool;

    /**
     * Marks this email as already rendered.
     */
    public function markAsRendered(): void;
}