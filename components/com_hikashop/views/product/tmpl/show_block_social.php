<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$pluginsClass = hikashop_get('class.plugins');
$plugin = $pluginsClass->getByName('system', 'hikashopsocial');
if (@ $plugin->published || @ $plugin->enabled) {
	echo '{hikashop_social}';
}else{ //backward compatibility added on 31/07/2014
	$plugin = $pluginsClass->getByName('content', 'hikashopsocial');
	if (@ $plugin->published || @ $plugin->enabled) {
		echo '{hikashop_social}';
	}
}
