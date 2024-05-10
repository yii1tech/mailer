<?php

namespace yii1tech\mailer;

use CFileHelper;
use CMap;
use Yii;
use yii1tech\mailer\widgets\ClipWidget;

/**
 * View is a email template view renderer.
 *
 * Instance of this class will be available inside the view templates as `$this` variable.
 *
 * This class uses the custom view renderer, if it is set at the application level.
 *
 * @property string $viewPath the root directory of view files. Defaults to 'views/mail' under the application base path.
 * @property \IViewRenderer|\CViewRenderer|array|string|null|false $viewRenderer view renderer or its array configuration.
 * @property \CMap $clips The list of clips.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class View extends \CBaseController
{
    /**
     * @var string|null the name of the layout to be applied to the views.
     * Defaults to `null`, meaning no layout should be applied.
     *
     * > Tip: you may change this value within particular view template - its original value will be restored after rendering.
     */
    public $layout;

    /**
     * @var string the root directory of view files. Defaults to 'views/mail' under the application base path.
     */
    private $_viewPath;

    /**
     * @var \IViewRenderer|\CViewRenderer|array|string|null|false view renderer or its array configuration.
     */
    private $_viewRenderer;

    /**
     * @var \CMap|null list of clips.
     * @see \CClipWidget
     */
    private $_clips;

    /**
     * @return string the root directory of view files. Defaults to 'views/mail' under the application base path.
     */
    public function getViewPath(): string
    {
        if ($this->_viewPath === null) {
            $this->_viewPath = Yii::app()->getBasePath() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'mail';
        }

        return $this->_viewPath;
    }

    /**
     * @param string $viewPath the root directory of view files.
     * @return static self reference.
     */
    public function setViewPath(string $viewPath): self
    {
        $this->_viewPath = $viewPath;

        return $this;
    }

    /**
     * Returns the view renderer - a component whose wants to replace the default view rendering logic.
     * By default, the application 'viewRenderer' component will be used, if it has been set.
     *
     * @return \IViewRenderer|\CViewRenderer|null|false view renderer.
     */
    public function getViewRenderer()
    {
        if (!is_object($this->_viewRenderer)) {
            if ($this->_viewRenderer === null) {
                if (Yii::app()->hasComponent('viewRenderer')) {
                    return Yii::app()->getComponent('viewRenderer');
                }
            } elseif (is_string($this->_viewRenderer) || is_array($this->_viewRenderer)) {
                $renderer = Yii::createComponent($this->_viewRenderer);
                if ($renderer instanceof \IApplicationComponent) {
                    $renderer->init();
                }

                $this->_viewRenderer = $renderer;
            }
        }

        return $this->_viewRenderer;
    }

    /**
     * Sets the view renderer - a component whose wants to replace the default view rendering logic.
     * Component can be provided as an object or its array configuration.
     * If `null` is given - the application 'viewRenderer' component will be used, if it has been set.
     * If `false` is given - no custom view renderer will be used.
     *
     * @see \IViewRenderer
     *
     * @param \IViewRenderer|\CViewRenderer|array|string|null|false $viewRenderer view renderer.
     * @return static self reference.
     */
    public function setViewRenderer($viewRenderer): self
    {
        $this->_viewRenderer = $viewRenderer;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getViewFile($viewName)
    {
        if (empty($viewName)) {
            return false;
        }

        if (!empty($renderer = $this->getViewRenderer())) {
            $extension = $renderer->fileExtension;
        } else {
            $extension = '.php';
        }

        if (strpos($viewName, '.')) {
            $viewFile = Yii::getPathOfAlias($viewName);
        } else {
            $viewFile = $this->getViewPath() . DIRECTORY_SEPARATOR . $viewName;
        }

        $viewFile .= $extension;

        if (!file_exists($viewFile)) {
            return false;
        }

        return $viewFile;
    }

    /**
     * {@inheritDoc}
     */
    public function renderFile($viewFile, $data = null, $return = false)
    {
        if (!empty($renderer = $this->getViewRenderer())) {
            if ($renderer->fileExtension === '.' . CFileHelper::getExtension($viewFile)) {
                return $renderer->renderFile($this, $viewFile, $data, $return);
            }
        }

        return $this->renderInternal($viewFile, $data, $return);
    }

    /**
     * Renders a view with a layout.
     *
     * This method first calls {@see renderPartial()} to render the view (called content view).
     * It then renders the layout view which may embed the content view at appropriate place.
     * In the layout view, the content view rendering result can be accessed via variable `$content`.
     *
     * @param string $view name of the view to be rendered. See {@see getViewFile()} for details about how the view script is resolved.
     * @param array|null $data data to be extracted into PHP variables and made available to the view script.
     * @param string|null $locale locale to be used while template rendering.
     * @return string the rendering result.
     */
    public function render(string $view, ?array $data = null, ?string $locale = null): string
    {
        $originalLayout = $this->layout;
        $originalLocale = Yii::app()->getLanguage();
        $obInitialLevel = ob_get_level();

        try {
            if ($locale !== null) {
                Yii::app()->setLanguage($locale);
            }

            $content = $this->renderPartial($view, $data, true);

            if (!empty($this->layout)) {
                $content = $this->renderPartial($this->layout, ['content' => $content], true);
            }
        } catch (\Throwable $e) {
            throw $e;
        } finally {
            while (ob_get_level() > $obInitialLevel) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }

            $this->layout = $originalLayout;
            $this->_clips = null;
            Yii::app()->setLanguage($originalLocale);
        }

        return $content;
    }

    /**
     * Renders a view.
     *
     * The named view refers to a PHP script (resolved via {@see getViewFile()})
     * that is included by this method. If $data is an associative array,
     * it will be extracted as PHP variables and made available to the script.
     *
     * This method differs from {@see render()} in that it does not apply a layout to the rendered result.
     *
     * @param string $view name of the view to be rendered. See {@see getViewFile()} for details about how the view script is resolved.
     * @param array|null $data data to be extracted into PHP variables and made available to the view script.
     * @param bool $return whether the rendering result should be returned instead of being sent to output.
     * @return string|null the rendering result. `Null` if the rendering result is not required.
     */
    public function renderPartial(string $view, ?array $data = null, bool $return = false)
    {
        $viewFile = $this->getViewFile($view);

        if ($viewFile === false) {
            throw new \InvalidArgumentException(get_class($this) . ' cannot find the requested view "' . $view . '".');
        }

        return $this->renderFile($viewFile, $data, $return);
    }

    /**
     * Returns the list of clips.
     * A clip is a named piece of rendering result that can be inserted at different places.
     *
     * @see \yii1tech\mailer\widgets\ClipWidget
     *
     * @return \CMap the list of clips
     */
    public function getClips(): CMap
    {
        if ($this->_clips === null) {
            $this->_clips = new CMap();
        }

        return $this->_clips;
    }

    /**
     * Begins recording a clip.
     * This method is a shortcut to beginning {@see \yii1tech\mailer\widgets\ClipWidget}.
     *
     * @param string $id the clip ID.
     * @param array $properties initial property values for {@see \yii1tech\mailer\widgets\ClipWidget}.
     */
    public function beginClip($id, $properties = []): void
    {
        $properties['id'] = $id;
        $properties['view'] = $this;
        $this->beginWidget(ClipWidget::class, $properties);
    }

    /**
     * Ends recording a clip.
     * This method is an alias to {@see endWidget()}.
     */
    public function endClip(): void
    {
        $this->endWidget(ClipWidget::class);
    }
}