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
 * @since  __DEPLOY_VERSION__
 */
class PrivacyModelDashboard extends JModelLegacy
{
	/**
	 * Get the information about the published privacy policy
	 *
	 * @return  array  Array containing a status of whether a privacy policy is set and a link to the policy document for editing
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getPrivacyPolicyInfo()
	{
		$policy = array(
			'published' => false,
			'editLink'  => '',
		);

		/*
		 * Prior to __DEPLOY_VERSION__ it was common for a plugin such as the User - Profile plugin to define a privacy policy or
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
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function getRequestFormPublished()
	{
		$app  = JFactory::getApplication();
		$menu = $app->getMenu('site');

		$item = $menu->getItems('link', 'index.php?option=com_privacy&view=request', true);

		$status = array(
			'published' => false,
			'link'      => '',
		);

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
