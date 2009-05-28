<?php defined('_JEXEC') or die; ?>
<div id="cpanel">
<?php 
foreach ($buttons as $button):
	echo QuickIconHelper::button($button);
endforeach;
?>
</div>
