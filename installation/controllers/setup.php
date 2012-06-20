<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Setup controller for the Joomla Core Installer.
 *
 * @package  Joomla.Installation
 * @since    3.0
 */
class InstallationControllerSetup extends JControllerLegacy
{
	/**
	 * @return	void
	 *
	 * @since	3.0
	 */
	public function preinstall()
	{
		// Redirect to the page.
		$this->setRedirect('index.php?view=preinstall');
	}
}
