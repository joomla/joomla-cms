<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<span class="breadcrumbs pathway">
<?php for ($i = 0; $i < $count; $i ++) :
	
	// If not the last item in the breadcrumbs add the separator
	if ($i < $count -1) {
		echo $list[$i]->link;
		echo ' '.$separator.' ';
	}  else { // when $i == $count -1
	    echo $list[$i]->name;
	}
endfor; ?>
</span>