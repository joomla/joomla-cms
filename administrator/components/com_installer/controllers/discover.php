<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

class InstallerControllerDiscover extends JController {
	/**
	 * Refreshes the cache of discovered extensions
	 */
	public function refresh()
	{
		$model	= &$this->getModel('discover');
		$model->discover();
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=discover',false));
	}

	/**
	 * Install a discovered extension
	 */
	function install()
	{
		$model	= &$this->getModel('discover');
		$model->discover_install();
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=discover',false));
	}

	/**
	 * Clean out the discovered extension cache
	 */
	function purge()
	{
		$model = &$this->getModel('discover');
		$model->purge();
		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=discover',false), $model->_message);
	}
}
