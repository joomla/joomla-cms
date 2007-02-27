<?php
defined('_JEXEC') or die('Restricted access');

echo '<div class="message_title">';
echo $this->message->title ;
echo '</div>';

echo '<div class="message">';
echo  $this->message->text ;
echo '</div>';
?>