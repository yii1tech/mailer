<?php

namespace yii1tech\mailer;

/**
 * Satisfies {@see \yii1tech\mailer\TemplatedEmailContract} interface.
 *
 * @mixin \Symfony\Component\Mime\Email
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
trait TemplatedEmailTrait
{
    private $htmlTemplate = null;

    private $textTemplate = null;

    private $locale = null;

    private $context = [];

    private $isRendered = false;

    /**
     * @param string|null $template template name for text body.
     * @return static self reference.
     */
    public function textTemplate(?string $template): self
    {
        $this->textTemplate = $template;

        return $this;
    }

    /**
     * @param string|null $template template name for HTML body.
     * @return static self reference.
     */
    public function htmlTemplate(?string $template): self
    {
        $this->htmlTemplate = $template;

        return $this;
    }

    /**
     * @param string|null $locale
     * @return static self reference.
     */
    public function locale(?string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @param array<string, mixed> $context template context variables.
     * @return static self reference.
     */
    public function context(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return string|null template name for text body.
     */
    public function getTextTemplate(): ?string
    {
        return $this->textTemplate;
    }

    /**
     * @return string|null template name for HTML body.
     */
    public function getHtmlTemplate(): ?string
    {
        return $this->htmlTemplate;
    }

    /**
     * @return array<string, mixed> template context variables.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * @return string|null locale to be used during template rendering.
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @return bool whether the templates have been already rendered.
     */
    public function isRendered(): bool
    {
        return $this->isRendered;
    }

    /**
     * Marks this email as already rendered.
     */
    public function markAsRendered(): void
    {
        $this->isRendered = true;
    }
}