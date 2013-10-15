<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Cancel Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  Controller
 * @since       3.2
 */
class ConfigControllerCancel extends JControllerBase
{

	/**
	 * Method to handle cancel
	 *
	 * @return  bool	True on success.
	 *
	 * @since   3.2
	 */
	public function execute()
	{

		// Redirect back to home(base) page
		$this->app->redirect(JURI::base());

		return true;
	}
}
