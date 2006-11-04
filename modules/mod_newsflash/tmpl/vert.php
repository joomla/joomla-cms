<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php foreach ($list as $item) :
	modNewsFlashHelper::renderItem($item, $params, $access);
endforeach; ?>