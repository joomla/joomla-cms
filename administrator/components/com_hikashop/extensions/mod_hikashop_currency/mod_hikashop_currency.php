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
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);
if(!include_once(rtrim(JPATH_ADMINISTRATOR,DS).DS.'components'.DS.'com_hikashop'.DS.'helpers'.DS.'helper.php')){
	echo 'This module can not work without the Hikashop Component';
	return;
};

$mode_noform = $params->get('mode_noform', 0);
$currency = hikashop_get('type.currency');
$config =& hikashop_config();
$redirectUrl = hikashop_currentURL();
require(JModuleHelper::getLayoutPath('mod_hikashop_currency'));
