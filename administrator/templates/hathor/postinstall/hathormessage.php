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
 * Checks if hathor is the default backend template or currently used as default style.
 * If yes we want to show a message and action button.
 *
 * @return  bool
 *
 * @since   3.7
 */
function hathormessage_postinstall_condition()
{
	$db             = JFactory::getDbo();
	$user           = JFactory::getUser();
	$globalTemplate = 'n/a';
	$template       = 'n/a';

	// We can only do that if you have edit permissions in com_templates
	if ($user->authorise('core.edit.state', 'com_templates'))
	{
		$query = $db->getQuery(true)
			->select('template')
			->from($db->quoteName('#__template_styles'))
			->where($db->quoteName('home') . ' = ' . $db->quote('1'))
			->where($db->quoteName('client_id') . ' = 1');

		// Get the global setting about the default template
		$globalTemplate = $db->setQuery($query)->loadResult();
	}

	// Get the current user admin style
	$adminstyle = $user->getParam('admin_style', '');

	if ($adminstyle != '')
	{
		$query = $db->getQuery(true)
			->select('template')
			->from($db->quoteName('#__template_styles'))
			->where($db->quoteName('id') . ' = ' . $adminstyle[0])
			->where($db->quoteName('client_id') . ' = 1');

		// Get the template name associated to the admin style
		$template = $db->setquery($query)->loadResult();
	}

	if (($globalTemplate != 'hathor') && ($template != 'hathor'))
	{
		// Hathor is not default not global and not in the user so no message needed
		return false;
	}

	// Hathor is default please add the message
	return true;
}

/**
 * Set the default backend template back to isis if you are allowed to do this
 * This also sets the current user setting to isis if not done yet
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
		->select(array('id', 'title'))
		->from($db->quoteName('#__template_styles'))
		->where($db->quoteName('template') . ' = "isis"')
		->where($db->quoteName('client_id') . ' = 1');

	$isisStyleId   = $db->setQuery($query)->loadColumn();
	$isisStyleName = $db->setQuery($query)->loadColumn(1);
	$adminstyle    = $user->getParam('admin_style', '');

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
			$user->setParam('admin_style', $isisStyleId['0']);
			$user->save();
		}
	}

	// We can only do that if you have edit permissions in com_templates
	if ($user->authorise('core.edit.state', 'com_templates'))
	{
		$query = $db->getQuery(true)
			->update($db->quoteName('#__template_styles'))
			->set($db->quoteName('home') . ' = ' . $db->quote('0'))
			->where($db->quoteName('template') . ' = "hathor"')
			->where($db->quoteName('client_id') . ' = 1');

		// Execute
		$db->setQuery($query)->execute();

		$query = $db->getQuery(true)
			->update($db->quoteName('#__template_styles'))
			->set($db->quoteName('home') . ' = ' . $db->quote('1'))
			->where($db->quoteName('template') . ' = "isis"')
			->where($db->quoteName('client_id') . ' = 1')
			->where($db->quoteName('id') . ' = ' . $isisStyleId[0]);

		// Execute
		$db->setQuery($query)->execute();
	}

	// The postinstall component load the language to late... so we need to make sure it is loaded here.
	JFactory::getLanguage()->load('tpl_hathor', JPATH_ADMINISTRATOR, null, false, true);

	// Template was successfully changed to isis
	JFactory::getApplication()->enqueueMessage(JText::sprintf('TPL_HATHOR_CHANGED_DEFAULT_TEMPLATE_TO_ISIS', $isisStyleName[0]), 'message');
}
