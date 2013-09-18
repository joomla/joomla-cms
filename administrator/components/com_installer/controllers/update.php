<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Installer Update Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       1.6
 */
class InstallerControllerUpdate extends JControllerLegacy
{
	/**
	 * Update a set of extensions.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function update()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('update');
		$uid   = $this->input->get('cid', array(), 'array');

		JArrayHelper::toInteger($uid, array());
		if ($model->update($uid))
		{
			$cache = JFactory::getCache('mod_menu');
			$cache->clean();
		}

		$app = JFactory::getApplication();
		$redirect_url = $app->getUserState('com_installer.redirect_url');
		if (empty($redirect_url))
		{
			$redirect_url = JRoute::_('index.php?option=com_installer&view=update', false);
		}
		else
		{
			// Wipe out the user state when we're going to redirect
			$app->setUserState('com_installer.redirect_url', '');
			$app->setUserState('com_installer.message', '');
			$app->setUserState('com_installer.extension_message', '');
		}
		$this->setRedirect($redirect_url);
	}

	/**
	 * Find new updates.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function find()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the caching duration
		$component = JComponentHelper::getComponent('com_installer');
		$params = $component->params;
		$cache_timeout = $params->get('cachetimeout', 6, 'int');
		$cache_timeout = 3600 * $cache_timeout;

		// Find updates
		$model	= $this->getModel('update');
		$model->findUpdates(0, $cache_timeout);
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=update', false));
	}

	/**
	 * Purges updates.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function purge()
	{
		// Purge updates
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$model = $this->getModel('update');
		$model->purge();
		$model->enableSites();
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=update', false), $model->_message);
	}

	/**
	 * Fetch and report updates in JSON format, for AJAX requests
	 *
	 * @return void
	 *
	 * @since 2.5
	 */
	public function ajax()
	{
		/*
		 * Note: we don't do a token check as we're fetching information
		 * asynchronously. This means that between requests the token might
		 * change, making it impossible for AJAX to work.
		 */

		$eid  = $this->input->getInt('eid', 0);
		$skip = $this->input->get('skip', array(), 'array');

		$cache_timeout = $this->input->getInt('cache_timeout', 0);
		if ($cache_timeout == 0)
		{
			$component = JComponentHelper::getComponent('com_installer');
			$params = $component->params;
			$cache_timeout = $params->get('cachetimeout', 6, 'int');
			$cache_timeout = 3600 * $cache_timeout;
		}

		$model = $this->getModel('update');
		$model->findUpdates($eid, $cache_timeout);

		$model->setState('list.start', 0);
		$model->setState('list.limit', 0);
		if ($eid != 0)
		{
			$model->setState('filter.extension_id', $eid);
		}
		$updates = $model->getItems();

		if (!empty($skip))
		{
			$unfiltered_updates = $updates;
			$updates = array();
			foreach ($unfiltered_updates as $update)
			{
				if (!in_array($update->extension_id, $skip))
				{
					$updates[] = $update;
				}
			}
		}
		echo json_encode($updates);

		JFactory::getApplication()->close();
	}
}
