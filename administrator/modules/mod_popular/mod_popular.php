<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_popular
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the mod_popular functions only once.
JLoader::register('ModPopularHelper', __DIR__ . '/helper.php');

JLoader::register('ContentDispatcher', JPATH_ADMINISTRATOR . '/components/com_content/dispatcher.php');
$oldScope = $app->scope;
$app->scope = 'com_content';
$namespace = \Joomla\CMS\Component\ComponentHelper::getComponent($app->scope)->namespace;
$dispatcher = new ContentDispatcher($namespace, JFactory::getApplication());

$list = ModPopularHelper::getList($params, $dispatcher->getFactory());
$app->scope = $oldScope;

// Get module data.
if ($params->get('automatic_title', 0))
{
	$module->title = ModPopularHelper::getTitle($params);
}

// Render the module
require JModuleHelper::getLayoutPath('mod_popular', $params->get('layout', 'default'));
