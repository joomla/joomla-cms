<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Dashboard model class.
 *
 * @since  3.9.0
 */
class PrivacyModelDashboard extends JModelLegacy
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
		$policy = array(
			'published'         => false,
			'articlePublished'  => false,
			'editLink'          => '',
		);

		/*
		 * Prior to 3.9.0 it was common for a plugin such as the User - Profile plugin to define a privacy policy or
		 * terms of service article, therefore we will also import the user plugin group to process this event.
		 */
		JPluginHelper::importPlugin('privacy');
		JPluginHelper::importPlugin('user');

		JFactory::getApplication()->triggerEvent('onPrivacyCheckPrivacyPolicyPublished', array(&$policy));

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
				array(
					'COUNT(*) AS count',
					$db->quoteName('status'),
					$db->quoteName('request_type'),
				)
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
		$app  = JFactory::getApplication();
		$menu = $app->getMenu('site');

		$item = $menu->getItems('link', 'index.php?option=com_privacy&view=request', true);

		$status = array(
			'exists'    => false,
			'published' => false,
			'link'      => '',
		);

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

		if (!($item instanceof JMenuItem))
		{
			$status['link'] = JRoute::link('site', 'index.php?option=com_privacy&view=request', true, $linkMode);
		}
		else
		{
			$status['published'] = true;
			$status['link']      = JRoute::link('site', 'index.php?Itemid=' . $item->id, true, $linkMode);
		}

		return $status;
	}
}
