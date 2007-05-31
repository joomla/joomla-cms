<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<span class="breadcrumbs pathway">
<?php echo $list[0]->name; ?>
<?php if ($count > 1) : 
	echo ' '.$separator.' ';
endif; ?>
<?php for ($i = 1; $i < $count; $i ++) :
	echo $list[$i]->link;
	// If not the last item in the breadcrumbs add the separator
	if ($i < $count -1) {
		echo ' '.$separator.' ';
	}
endfor; ?>
</span>