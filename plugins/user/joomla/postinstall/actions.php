<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.joomla
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file contains the functions used by the com_postinstall code to deliver
 * the necessary post-installation messages concerning the activation of the
 * strong password storage option.
 */

/**
 * Checks if the strong password storage is enabled. If not it returns true,
 * meaning that the message concerning strong passwords should be displayed.
 *
 * @return  integer
 *
 * @since   3.2
 */
function plguserjoomla_postinstall_condition()
{
	$data = JPluginHelper::getPlugin('user', 'joomla');
	$params = new JRegistry($data->params);

	$strong_passwords = $params->get('strong_passwords', 0);

	return $strong_passwords == 0;
}

/**
 * Enables the strong password storage and redirects the user back to the
 * message page.
 *
 * @return  void
 *
 * @since   3.2
 */
function plguserjoomla_postinstall_action()
{
	$data = JPluginHelper::getPlugin('user', 'joomla');
	$params = new JRegistry($data->params);

	$params->set('strong_passwords', 1);

	$db = JFactory::getDbo();

	$query = $db->getQuery(true)
		->update($db->qn('#__extensions'))
		->set($db->qn('params') . ' = ' . $db->q($params->toString('JSON')))
		->where($db->qn('type') . ' = ' . $db->q('plugin'))
		->where($db->qn('folder') . ' = ' . $db->q('user'))
		->where($db->qn('element') . ' = ' . $db->q('joomla'));
	$db->setQuery($query);
	$db->execute();

	// Redirect the user to their profile editor page
	$url = 'index.php?option=com_postinstall';
	JFactory::getApplication()->redirect($url);
}
