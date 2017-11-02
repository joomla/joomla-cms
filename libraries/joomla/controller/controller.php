<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Application\AbstractApplication;
use Joomla\Controller\ControllerInterface;
use Joomla\Input\Input;

/**
 * Joomla Platform Controller Interface
 *
 * @since       12.1
 * @deprecated  5.0  Implement Joomla\CMS\MVC\Controller\ControllerInterface instead
 */
interface JController extends ControllerInterface
{
	/**
	 * Get the application object.
	 *
	 * @return  AbstractApplication  The application object.
	 *
	 * @since   12.1
	 */
	public function getApplication();

	/**
	 * Get the input object.
	 *
	 * @return  Input  The input object.
	 *
	 * @since   12.1
	 */
	public function getInput();
}
