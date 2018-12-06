<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Application\AbstractApplication;

/**
 * Joomla Platform Controller Interface
 *
 * @since       3.0.0
 * @deprecated  5.0 Use the default MVC library
 */
interface JController extends Serializable
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @since   3.0.0
	 * @throws  LogicException
	 * @throws  RuntimeException
	 */
	public function execute();

	/**
	 * Get the application object.
	 *
	 * @return  AbstractApplication  The application object.
	 *
	 * @since   3.0.0
	 */
	public function getApplication();

	/**
	 * Get the input object.
	 *
	 * @return  JInput  The input object.
	 *
	 * @since   3.0.0
	 */
	public function getInput();
}
