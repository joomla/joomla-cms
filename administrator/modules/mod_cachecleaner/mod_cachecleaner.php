<?php
/**
 * Main Module File
 * Does all the magic!
 *
 * @package			Cache Cleaner
 * @version			2.2.0
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		        Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Module that cleans cache
 */

// return if NoNumber Framework plugin is not installed
jimport('joomla.filesystem.file');
if (!JFile::exists(JPATH_PLUGINS.'/system/nnframework/nnframework.php')) {
	return;
}

// return if NoNumber Framework plugin is not enabled
$nnep = JPluginHelper::getPlugin('system', 'nnframework');
if (!isset($nnep->name)) {
	return;
}

// Include the syndicate functions only once
require_once dirname(__FILE__).'/cachecleaner/helper.php';

$helper = new modCacheCleaner();
$helper->render();
