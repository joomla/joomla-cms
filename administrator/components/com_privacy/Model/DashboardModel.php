<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

/**
 * Dashboard model class.
 *
 * @since  3.9.0
 */
class DashboardModel extends BaseDatabaseModel
{
	/**
	 * Get the information about the published privacy policy
	 *
	 * @return  array  Array containing a status of whether a privacy policy is set and a link to the policy document for editing
	 *
	 * @since   3.9.0
	 */
	public function getPrivacyPolicyInfo()
	{
		$policy = [
			'published'        => false,
			'articlePublished' => false,
			'editLink'         => '',
		];

		/*
		 * Prior to 3.9.0 it was common for a plugin such as the User - Profile plugin to define a privacy policy or
		 * terms of service article, therefore we will also import the user plugin group to process this event.
		 */
		PluginHelper::importPlugin('privacy');
		PluginHelper::importPlugin('user');

		Factory::getApplication()->triggerEvent('onPrivacyCheckPrivacyPolicyPublished', [&$policy]);

		return $policy;
	}

	/**
	 * Get a count of the active information requests grouped by type and status
	 *
	 * @return  array  Array containing site privacy requests
	 *
	 * @since   3.9.0
	 */
	public function getRequestCounts()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				[
					'COUNT(*) AS count',
					$db->quoteName('status'),
					$db->quoteName('request_type'),
				]
			)
			->from($db->quoteName('#__privacy_requests'))
			->group($db->quoteName('status'))
			->group($db->quoteName('request_type'));

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Check whether there is a menu item for the request form
	 *
	 * @return  array  Array containing a status of whether a menu is published for the request form and its current link
	 *
	 * @since   3.9.0
	 */
	public function getRequestFormPublished()
	{
		$app  = Factory::getApplication();
		$menu = $app->getMenu('site');

		$item = $menu->getItems('link', 'index.php?option=com_privacy&view=request', true);

		$status = [
			'exists'    => false,
			'published' => false,
			'link'      => '',
		];

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__menu'))
			->where($db->quoteName('client_id') . ' = 0')
			->where($db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_privacy&view=request'));
		$db->setQuery($query);

		// Check if the menu item exists in database
		if ($db->loadResult())
		{
			$status['exists'] = true;
		}

		$linkMode = $app->get('force_ssl', 0) == 2 ? 1 : -1;

		if (!($item instanceof MenuItem))
		{
			$status['link'] = Route::link('site', 'index.php?option=com_privacy&view=request', true, $linkMode);
		}
		else
		{
			$status['published'] = true;
			$status['link']      = Route::link('site', 'index.php?Itemid=' . $item->id, true, $linkMode);
		}

		return $status;
	}
}
