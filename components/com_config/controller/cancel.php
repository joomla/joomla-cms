<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Cancel Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigControllerCancel extends JControllerBase
{
	/**
	 * Application object - Redeclared for proper typehinting
	 *
	 * @var    JApplicationCms
	 * @since  3.2
	 */
	protected $app;

	/**
	 * Method to handle cancel
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		// Redirect back to home(base) page
		$this->app->redirect(JUri::base());
	}
}
