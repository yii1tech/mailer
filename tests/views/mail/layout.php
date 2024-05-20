<?php
/**
 * @var $this \yii1tech\mailer\View
 * @var $_message \yii1tech\mailer\TemplatedEmail|null
 * @var $content string
 */
?>
<!--Header-->
<?php echo $content; ?>
Clip = <?php echo $this->getClips()->itemAt('test-clip') ?>
Layout-Subject = <?php echo isset($_message) ? $_message->getSubject() : ''; ?>
<!--Footer-->
