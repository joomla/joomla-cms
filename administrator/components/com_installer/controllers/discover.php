<?php
/**
 * @version		$Id: discover.php 22338 2011-11-04 17:24:53Z github_bot $
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 */
class InstallerControllerDiscover extends JController
{
	/**
	 * Refreshes the cache of discovered extensions.
	 *
	 * @since	1.6
	 */
	public function refresh()
	{
		$model = $this->getModel('discover');
		$model->discover();
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=discover',false));
	}

	/**
	 * Install a discovered extension.
	 *
	 * @since	1.6
	 */
	function install()
	{
		$model = $this->getModel('discover');
		$model->discover_install();
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=discover',false));
	}

	/**
	 * Clean out the discovered extension cache.
	 *
	 * @since	1.6
	 */
	function purge()
	{
		$model = $this->getModel('discover');
		$model->purge();
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=discover',false), $model->_message);
	}
}
