<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Users component helper.
 *
 * @since  1.6
 */
class UsersHelper
{
	/**
	 * @var    JObject  A cache for the available actions.
	 * @since  1.6
	 */
	protected static $actions;

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_USERS_SUBMENU_USERS'),
			'index.php?option=com_users&view=users',
			$vName == 'users'
		);

		// Groups and Levels are restricted to core.admin
		$canDo = JHelperContent::getActions('com_users');

		if ($canDo->get('core.admin'))
		{
			JHtmlSidebar::addEntry(
				JText::_('COM_USERS_SUBMENU_GROUPS'),
				'index.php?option=com_users&view=groups',
				$vName == 'groups'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_USERS_SUBMENU_LEVELS'),
				'index.php?option=com_users&view=levels',
				$vName == 'levels'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_USERS_SUBMENU_NOTES'),
				'index.php?option=com_users&view=notes',
				$vName == 'notes'
			);
			JHtmlSidebar::addEntry(
				JText::_('COM_USERS_SUBMENU_NOTE_CATEGORIES'),
				'index.php?option=com_categories&extension=com_users',
				$vName == 'categories'
			);
		}

		if (JComponentHelper::isEnabled('com_fields') && JComponentHelper::getParams('com_users')->get('custom_fields_enable', '1'))
		{
			JHtmlSidebar::addEntry(
				JText::_('JGLOBAL_FIELDS'),
				'index.php?option=com_fields&context=com_users.user',
				$vName == 'fields.fields'
			);
			JHtmlSidebar::addEntry(
				JText::_('JGLOBAL_FIELD_GROUPS'),
				'index.php?option=com_fields&view=groups&extension=com_users',
				$vName == 'fields.groups'
			);
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject
	 *
	 * @deprecated  3.2  Use JHelperContent::getActions() instead
	 */
	public static function getActions()
	{
		// Log usage of deprecated function
		JLog::add(__METHOD__ . '() is deprecated, use JHelperContent::getActions() with new arguments order instead.', JLog::WARNING, 'deprecated');

		// Get list of actions
		$result = JHelperContent::getActions('com_users');

		return $result;
	}

	/**
	 * Get a list of filter options for the blocked state of a user.
	 *
	 * @return  array  An array of JHtmlOption elements.
	 *
	 * @since   1.6
	 */
	public static function getStateOptions()
	{
		// Build the filter options.
		$options = array();
		$options[] = JHtml::_('select.option', '0', JText::_('JENABLED'));
		$options[] = JHtml::_('select.option', '1', JText::_('JDISABLED'));

		return $options;
	}

	/**
	 * Get a list of filter options for the activated state of a user.
	 *
	 * @return  array  An array of JHtmlOption elements.
	 *
	 * @since   1.6
	 */
	public static function getActiveOptions()
	{
		// Build the filter options.
		$options = array();
		$options[] = JHtml::_('select.option', '0', JText::_('COM_USERS_ACTIVATED'));
		$options[] = JHtml::_('select.option', '1', JText::_('COM_USERS_UNACTIVATED'));

		return $options;
	}

	/**
	 * Get a list of the user groups for filtering.
	 *
	 * @return  array  An array of JHtmlOption elements.
	 *
	 * @since   1.6
	 */
	public static function getGroups()
	{
		$options = JHelperUsergroups::getInstance()->getAll();

		foreach ($options as &$option)
		{
			$option->value = $option->id;
			$option->text = str_repeat('- ', $option->level) . $option->title;
		}

		return $options;
	}

	/**
	 * Creates a list of range options used in filter select list
	 * used in com_users on users view
	 *
	 * @return  array
	 *
	 * @since   2.5
	 */
	public static function getRangeOptions()
	{
		$options = array(
			JHtml::_('select.option', 'today', JText::_('COM_USERS_OPTION_RANGE_TODAY')),
			JHtml::_('select.option', 'past_week', JText::_('COM_USERS_OPTION_RANGE_PAST_WEEK')),
			JHtml::_('select.option', 'past_1month', JText::_('COM_USERS_OPTION_RANGE_PAST_1MONTH')),
			JHtml::_('select.option', 'past_3month', JText::_('COM_USERS_OPTION_RANGE_PAST_3MONTH')),
			JHtml::_('select.option', 'past_6month', JText::_('COM_USERS_OPTION_RANGE_PAST_6MONTH')),
			JHtml::_('select.option', 'past_year', JText::_('COM_USERS_OPTION_RANGE_PAST_YEAR')),
			JHtml::_('select.option', 'post_year', JText::_('COM_USERS_OPTION_RANGE_POST_YEAR')),
		);

		return $options;
	}

	/**
	 * Creates a list of two factor authentication methods used in com_users
	 * on user view
	 *
	 * @return  array
	 *
	 * @since   3.2.0
	 */
	public static function getTwoFactorMethods()
	{
		FOFPlatform::getInstance()->importPlugin('twofactorauth');
		$identities = FOFPlatform::getInstance()->runPlugins('onUserTwofactorIdentify', array());

		$options = array(
			JHtml::_('select.option', 'none', JText::_('JGLOBAL_OTPMETHOD_NONE'), 'value', 'text'),
		);

		if (!empty($identities))
		{
			foreach ($identities as $identity)
			{
				if (!is_object($identity))
				{
					continue;
				}

				$options[] = JHtml::_('select.option', $identity->method, $identity->title, 'value', 'text');
			}
		}

		return $options;
	}

	/**
	 * Get a list of the User Groups for Viewing Access Levels
	 *
	 * @param   string  $rules  User Groups in JSON format
	 *
	 * @return  string  $groups  Comma separated list of User Groups
	 *
	 * @since   3.6
	 */
	public static function getVisibleByGroups($rules)
	{
		$rules = json_decode($rules);

		if (!$rules)
		{
			return false;
		}

		$rules = implode(',', $rules);

		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('a.title AS text')
			->from('#__usergroups as a')
			->where('a.id IN (' . $rules . ')');
		$db->setQuery($query);

		$groups = $db->loadColumn();
		$groups = implode(', ', $groups);

		return $groups;
	}

	/**
	 * Adds Count Items for Tag Manager.
	 *
	 * @param   stdClass[]  &$items     The user note tag objects
	 * @param   string      $extension  The name of the active view.
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.6
	 */
	public static function countTagItems(&$items, $extension)
	{
		$db = JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed = 0;
			$item->count_archived = 0;
			$item->count_unpublished = 0;
			$item->count_published = 0;
			$query = $db->getQuery(true);
			$query->select('published as state, count(*) AS count')
				->from($db->qn('#__contentitem_tag_map') . 'AS ct ')
				->where('ct.tag_id = ' . (int) $item->id)
				->where('ct.type_alias =' . $db->q($extension))
				->join('LEFT', $db->qn('#__categories') . ' AS c ON ct.content_item_id=c.id')
				->group('c.published');

			$db->setQuery($query);
			$users = $db->loadObjectList();

			foreach ($users as $user)
			{
				if ($user->state == 1)
				{
					$item->count_published = $user->count;
				}

				if ($user->state == 0)
				{
					$item->count_unpublished = $user->count;
				}

				if ($user->state == 2)
				{
					$item->count_archived = $user->count;
				}

				if ($user->state == -2)
				{
					$item->count_trashed = $user->count;
				}
			}
		}

		return $items;
	}

	/**
	 * Returns valid contexts
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getContexts()
	{
		JFactory::getLanguage()->load('com_users', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_users.user' => JText::_('COM_USERS'),
		);

		return $contexts;
	}
}
