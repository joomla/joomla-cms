<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 */

/**
 * Checks if hathor is the default backend template. If yes we want to show a message
 *
 * @return  bool
 *
 * @since   3.7
 */
function hathormessage_postinstall_condition()
{
	$db = JFactory::getDbo();
	$query = $db->getQuery(true)
		->select('home')
		->from($db->quoteName('#__template_styles'))
		->where($db->quoteName('template') . ' = "hathor"')
		->where($db->quoteName('client_id') . ' = 1');
	$globalHathor = $db->setQuery($query)->loadResult();

	$user       = JFactory::getUser();
	$adminstyle = $user->getParam('admin_style', '');

	if ($adminstyle != '')
	{

		$query = $db->getQuery(true)
			->select('template')
			->from($db->quoteName('#__template_styles'))
			->where($db->quoteName('id') . ' = ' . $adminstyle[0])
			->where($db->quoteName('client_id') . ' = 1');

		$template = $db->setquery($query)->loadResult();
	}

	if (!$globalHathor && ($template != 'hathor'))
	{
		// Hathor is not default not global and not in the user so no message needed
		return false;
	}

	// Hathor is default please add the message
 	return true;
}

/**
 * Set the default backend template back to isis but don't touch the user setting
 *
 * @return  void
 *
 * @since   3.7
 */
function hathormessage_postinstall_action()
{
	$db   = JFactory::getDbo();
	$user = JFactory::getUser();

	$query = $db->getQuery(true)
		->select('id')
		->from($db->quoteName('#__template_styles'))
		->where($db->quoteName('template') . ' = "isis"')
		->where($db->quoteName('client_id') . ' = 1');

	$isisStyle  = $db->setQuery($query)->loadColumn();
	$adminstyle = $user->getParam('admin_style', '');

	// The user uses the system setting so no need to change that.
	if ($adminstyle != '')
	{
		$query = $db->getQuery(true)
			->select('template')
			->from($db->quoteName('#__template_styles'))
			->where($db->quoteName('id') . ' = ' . $adminstyle[0])
			->where($db->quoteName('client_id') . ' = 1');

		$template = $db->setQuery($query)->loadResult();

		// The current user uses hathor
		if ($template == 'hathor')
		{
			$user->setParam('admin_style', $isisStyle['0']);
			$user->save();
		}
	}

	$query = $db->getQuery(true)
		->update($db->quoteName('#__template_styles'))
		->set($db->quoteName('home') . ' = 0')
		->where($db->quoteName('template') . ' = "hathor"')
		->where($db->quoteName('client_id') . ' = 1');

	// Execute
	$db->setQuery($query)->execute();

	$query = $db->getQuery(true)
		->update($db->quoteName('#__template_styles'))
		->set($db->quoteName('home') . ' = 1')
		->where($db->quoteName('template') . ' = "isis"')
		->where($db->quoteName('client_id') . ' = 1')
		->where($db->quoteName('id') . ' = ' . $isisStyle[0]);

	// Execute
	$db->setQuery($query)->execute();

	// Template was successfully changed to isis
	JFactory::getApplication()->enqueueMessage(JText::_('TLP_HATHOR_CHANGED_DEFAULT_TEMPLATE_TO_ISIS'), 'message');
}