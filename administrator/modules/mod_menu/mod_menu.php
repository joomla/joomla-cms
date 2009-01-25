<?php
/**
* @version		$Id:mod_menu.php 2463 2006-02-18 06:05:38Z webImagery $
* @package		Joomla.Administrator
* @copyright		Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!class_exists('MenuModuleHelper')) {
	require dirname(__FILE__).DS.'helper.php';
}

MenuModuleHelper::buildMenu(JRequest::getInt('hidemainmenu') ? false : true);
