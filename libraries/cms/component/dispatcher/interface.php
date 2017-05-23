<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Dispatcher
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Interface for a generic dispatcher
 *
 * @since  3.4
 */
interface JComponentDispatcherInterface
{
	/**
	 * Method to execute the controller and perform the redirects.
	 *
	 * @param   JApplicationCms $app The application instance to pass to the controller.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	public function dispatch(JApplicationCms $app = null);

	/**
	 * Gets the controller
	 *
	 * @param   JApplicationCms  $app  The application object.
	 *
	 * @return  JControllerCmsInterface
	 */
	public function getController(JApplicationCms $app = null);
}
