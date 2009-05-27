<?php // @version $Id: default.php 10381 2008-06-01 03:35:53Z pasamio $
defined('_JEXEC') or die;
?>

<?php
srand((double) microtime() * 1000000);
$flashnum = rand(0, $items - 1);
$item = $list[$flashnum];
modNewsFlashHelper::renderItem($item, $params, $access);
?>
