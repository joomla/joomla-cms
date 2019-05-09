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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
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
		$status = [
			'exists'    => false,
			'published' => false,
			'link'      => '',
		];

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id') . ', ' . $db->quoteName('published') . ', ' . $db->quoteName('language'))
			->from($db->quoteName('#__menu'))
			->where($db->quoteName('client_id') . ' = 0')
			->where($db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_privacy&view=request'));
		$db->setQuery($query);

		$menuItem = $db->loadObject();

		// Check if the menu item exists in database
		if ($menuItem)
		{
			$status['exists'] = true;

			// Check if the menu item is published
			if ($menuItem->published == 1)
			{
				$status['published'] = true;
			}

			// Add language to the url if the site is multilingual
			if (Multilanguage::isEnabled() && $menuItem->language && $menuItem->language !== '*')
			{
				$lang = '&lang=' . $menuItem->language;
			}
			else
			{
				$lang = '';
			}
		}

		$linkMode = Factory::getApplication()->get('force_ssl', 0) == 2 ? 1 : -1;

		if (!$menuItem)
		{
			if (Multilanguage::isEnabled())
			{
				// Find the Itemid of the home menu item tagged to the site default language
				$params = ComponentHelper::getParams('com_languages');
				$defaultSiteLanguage = $params->get('site');

				$db    = $this->getDbo();
				$query = $db->getQuery(true)
					->select($db->quoteName('id'))
					->from($db->quoteName('#__menu'))
					->where($db->quoteName('client_id') . ' = 0')
					->where($db->quoteName('home') . ' = 1')
					->where($db->quoteName('language') . ' = ' . $db->quote($defaultSiteLanguage));
				$db->setQuery($query);

				$homeId = (int) $db->loadResult();
				$itemId = $homeId ? '&Itemid=' . $homeId : '';
			}
			else
			{
				$itemId = '';
			}

			$status['link'] = Route::link('site', 'index.php?option=com_privacy&view=request' . $itemId, true, $linkMode);
		}
		else
		{
			$status['link'] = Route::link('site', 'index.php?Itemid=' . $menuItem->id . $lang, true, $linkMode);
		}

		return $status;
	}
}
