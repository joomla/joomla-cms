<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_services
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Cancel Controller for global configuration
 *
 * @package     Joomla.Site
 * @subpackage  com_services
 * @since       3.2
 */
class ServicesControllerConfigCancel extends JControllerBase
{

	/**
	 * Method to cancel global configuration.
	 *
	 * @return  bool	True on success.
	 *
	 * @since   3.2
	 */
	public function execute()
	{

		// Redirect back to home(base) page
		JFactory::getApplication()->redirect(JURI::base());

		return true;
	}
}
