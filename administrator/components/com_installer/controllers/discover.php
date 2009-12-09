<?php
/**
 * @version		$Id: controller.php 13265 2009-10-21 10:08:19Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

class InstallerControllerDiscover extends JController {
	/**
	 * Discover handler
	 */
	public function refresh()
	{
		$model	= &$this->getModel('discover');
		$model->discover();
		$this->setRedirect('index.php?option=com_installer&view=discover');
	}

	function install()
	{
		$model	= &$this->getModel('discover');
		$model->discover_install();
		$model->saveState(); // Save the state because this is where our messages are stored
		$this->setRedirect('index.php?option=com_installer&view=discover');
	}

	function purge()
	{
		$model = &$this->getModel('discover');
		$model->purge();
		$this->setRedirect('index.php?option=com_installer&view=discover', $model->_message);
	}
}