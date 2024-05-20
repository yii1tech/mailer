<?php
/**
 * @var $this \yii1tech\mailer\View
 * @var $name string
 */

$this->layout = 'layout';
?>
Test switch mail template
Name = <?php echo $name; ?>
Locale = <?php echo Yii::app()->getLanguage(); ?>
<?php $this->beginClip('test-clip'); ?>
Test Clip Content
<?php $this->endClip(); ?>

