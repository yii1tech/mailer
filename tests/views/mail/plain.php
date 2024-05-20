<?php
/**
 * @var $this \yii1tech\mailer\View
 * @var $_message \yii1tech\mailer\TemplatedEmail|null
 */
?>
Test plain mail template
Name = <?php echo $name; ?>
Subject = <?php echo isset($_message) ? $_message->getSubject() : ''; ?>