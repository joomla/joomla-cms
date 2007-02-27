<?php
defined('_JEXEC') or die('Restricted access');

if (count($list))
{
	echo '<ul class="latestnews'. $params->get('moduleclass_sfx').'">';
	foreach ($list as $item)
	{
		echo '<li class="latestnews'. $params->get('moduleclass_sfx').'">';
		echo '<a href="'.$item->link.'" class="latestnews'.$params->get('moduleclass_sfx').'>">';
		echo $item->text;
		echo '</a></li>';
	}
	echo '</ul>';
}
?>