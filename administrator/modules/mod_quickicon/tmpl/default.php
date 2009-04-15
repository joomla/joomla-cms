<?php defined('_JEXEC') or die('Restricted access'); ?>
<div id="cpanel">
<?php 
foreach ($buttons as $button):
	echo IconHelper::button($button[0], $button[1], $button[2], $button[3]);
endforeach;
?>
</div>