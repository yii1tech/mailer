<?php

namespace yii1tech\mailer\widgets;

use CWidget;

/**
 * ClipWidget records its content and makes it available elsewhere.
 *
 * This is a replacement for standard {@see \CClipWidget}, which can integrate into {@see \yii1tech\mailer\View}.
 *
 * @see \yii1tech\mailer\View::beginClip()
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ClipWidget extends CWidget
{
    /**
     * @var \CBaseController|\yii1tech\mailer\View view, which renders this widget.
     */
    public $view;

    /**
     * @var bool whether to render the clip content in place. Defaults to false,
     * meaning the captured clip will not be displayed.
     */
    public $renderClip = false;

    /**
     * Starts recording a clip.
     */
    public function init()
    {
        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * Ends recording a clip.
     * This method stops output buffering and saves the rendering result as a named clip in the controller.
     */
    public function run()
    {
        $clip = ob_get_clean();
        if ($this->renderClip) {
            echo $clip;
        }

        $this->view->getClips()->add($this->getId(), $clip);
    }
}