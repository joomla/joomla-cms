<?php
defined('_JEXEC') or die('Restricted access');

if (count($list)) {
	if (count($list) == 1) {
		$item = $list[0];
		modNewsFlashHelper :: renderItem($item, $params, $access);
	} else {
		echo '<ul class="horiz' . $params->get('moduleclass_sfx') . '">';
		foreach ($list as $item) {
			echo '<li>';
			modNewsFlashHelper :: renderItem($item, $params, $access);
			echo '</li>';
		}
	}
}
?>