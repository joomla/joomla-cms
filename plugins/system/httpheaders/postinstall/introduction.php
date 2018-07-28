<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.HttpHeaders
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Checks if the plugin is enabled. If not it returns true, meaning that the
 * message concerning the HTTPHeaders Plugin should be displayed.
 *
 * @return  integer
 *
 * @since   4.0
 */
function httpheaders_postinstall_condition()
{
	return !Joomla\CMS\Plugin\PluginHelper::isEnabled('system', 'httpheaders');
}

/**
 * Enables the HTTPHeaders plugin
 *
 * @return  void
 *
 * @since   4.0
 */
function httpheaders_postinstall_action()
{
	// Enable the plugin
	$db = Factory::getDbo();

	$query = $db->getQuery(true)
		->update($db->qn('#__extensions'))
		->set($db->qn('enabled') . ' = 1')
		->where($db->qn('type') . ' = ' . $db->q('plugin'))
		->where($db->qn('folder') . ' = ' . $db->q('system'))
		->where($db->qn('element') . ' = ' . $db->q('httpheaders'));
	$db->setQuery($query);
	$db->execute();

	$query = $db->getQuery(true)
		->select('extension_id')
		->from($db->qn('#__extensions'))
		->where($db->qn('type') . ' = ' . $db->q('plugin'))
		->where($db->qn('folder') . ' = ' . $db->q('system'))
		->where($db->qn('element') . ' = ' . $db->q('httpheaders'));
	$db->setQuery($query);
	$extensionId = $db->loadResult();

	$url = 'index.php?option=com_plugins&task=plugin.edit&extension_id=' . $extensionId;
	Factory::getApplication()->redirect($url);
}
