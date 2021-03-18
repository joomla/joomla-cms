<?php
/**
 * Part of the Joomla Framework Controller Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Controller;

use Joomla\Application\AbstractApplication;
use Joomla\Input\Input;

/**
 * Joomla Framework Base Controller Class
 *
 * @since  1.0
 */
abstract class AbstractController implements ControllerInterface
{
	/**
	 * The application object.
	 *
	 * @var    AbstractApplication
	 * @since  1.0
	 */
	private $app;

	/**
	 * The input object.
	 *
	 * @var    Input
	 * @since  1.0
	 */
	private $input;

	/**
	 * Instantiate the controller.
	 *
	 * @param   Input                $input  The input object.
	 * @param   AbstractApplication  $app    The application object.
	 *
	 * @since   1.0
	 */
	public function __construct(?Input $input = null, ?AbstractApplication $app = null)
	{
		$this->input = $input;
		$this->app   = $app;
	}

	/**
	 * Get the application object.
	 *
	 * @return  AbstractApplication|null
	 *
	 * @since   1.0
	 */
	public function getApplication()
	{
		return $this->app;
	}

	/**
	 * Get the input object.
	 *
	 * @return  Input|null
	 *
	 * @since   1.0
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Set the application object.
	 *
	 * @param   AbstractApplication  $app  The application object.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setApplication(AbstractApplication $app)
	{
		$this->app = $app;

		return $this;
	}

	/**
	 * Set the input object.
	 *
	 * @param   Input  $input  The input object.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setInput(Input $input)
	{
		$this->input = $input;

		return $this;
	}
}
