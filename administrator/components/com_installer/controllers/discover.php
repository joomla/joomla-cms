<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Discover Installation Controller
 *
 * @since  1.6
 */
class InstallerControllerDiscover extends JControllerLegacy
{
	/**
	 * Refreshes the cache of discovered extensions.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function refresh()
	{
		$this->checkToken();

		$model = $this->getModel('discover');
		$model->discover();
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=discover', false));
	}

	/**
	 * Install a discovered extension.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function install()
	{
		$this->checkToken();

		$this->getModel('discover')->discover_install();
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=discover', false));
	}

	/**
	 * Clean out the discovered extension cache.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function purge()
	{
		$this->checkToken();

		$model = $this->getModel('discover');
		$model->purge();
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=discover', false), $model->_message);
	}
}
