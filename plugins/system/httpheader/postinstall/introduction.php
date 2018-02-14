<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.HttpHeader
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Checks if the plugin is enabled. If not it returns true, meaning that the
 * message concerning the HTTPHeader Plugin should be displayed.
 *
 * @return  integer
 *
 * @since   4.0
 */
function httpheader_postinstall_condition()
{
	return Joomla\CMS\Plugin\PluginHelper::isEnabled('system', 'httpheader');
}

/**
 * Enables the HTTPHeader plugin
 *
 * @return  void
 *
 * @since   4.0
 */
function httpheader_postinstall_action()
{
	// Enable the plugin
	$db = JFactory::getDbo();

	$query = $db->getQuery(true)
		->update($db->qn('#__extensions'))
		->set($db->qn('enabled') . ' = 1')
		->where($db->qn('type') . ' = ' . $db->q('plugin'))
		->where($db->qn('folder') . ' = ' . $db->q('system'))
		->where($db->qn('element') . ' = ' . $db->q('plg_system_httpheader'));
	$db->setQuery($query);
	$db->execute();
}
