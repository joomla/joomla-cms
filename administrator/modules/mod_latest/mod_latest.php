<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_latest
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include dependencies.
JLoader::register('ModLatestHelper', __DIR__ . '/helper.php');

JLoader::register('ContentDispatcher', JPATH_ADMINISTRATOR . '/components/com_content/dispatcher.php');
$oldScope = $app->scope;
$app->scope = 'com_content';
$namespace = \Joomla\CMS\Component\ComponentHelper::getComponent($app->scope)->namespace;
$dispatcher = new ContentDispatcher($namespace, JFactory::getApplication());

$list = ModLatestHelper::getList($params, $dispatcher->getFactory());
$app->scope = $oldScope;

if ($params->get('automatic_title', 0))
{
	$module->title = ModLatestHelper::getTitle($params);
}

require JModuleHelper::getLayoutPath('mod_latest', $params->get('layout', 'default'));
