<?php
defined('_JEXEC') or die('Restricted access');

echo '<h3>';
echo $this->message->title ;
echo '</h3>';

echo '<p class="message">';
echo  $this->message->text ;
echo '</p>';
?>