<?php
/**
 * Part of the Joomla Framework Controller Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Controller;

use Joomla\Input\Input;
use Joomla\Application\AbstractApplication;

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
	 * @since  1.0
	 */
	public function __construct(Input $input = null, AbstractApplication $app = null)
	{
		$this->input = $input;
		$this->app   = $app;
	}

	/**
	 * Get the application object.
	 *
	 * @return  AbstractApplication  The application object.
	 *
	 * @since   1.0
	 * @throws  \UnexpectedValueException if the application has not been set.
	 */
	public function getApplication()
	{
		if ($this->app)
		{
			return $this->app;
		}

		throw new \UnexpectedValueException('Application not set in ' . __CLASS__);
	}

	/**
	 * Get the input object.
	 *
	 * @return  Input  The input object.
	 *
	 * @since   1.0
	 * @throws  \UnexpectedValueException
	 */
	public function getInput()
	{
		if ($this->input)
		{
			return $this->input;
		}

		throw new \UnexpectedValueException('Input not set in ' . __CLASS__);
	}

	/**
	 * Serialize the controller.
	 *
	 * @return  string  The serialized controller.
	 *
	 * @since   1.0
	 */
	public function serialize()
	{
		return serialize($this->getInput());
	}

	/**
	 * Set the application object.
	 *
	 * @param   AbstractApplication  $app  The application object.
	 *
	 * @return  AbstractController  Returns itself to support chaining.
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
	 * @return  AbstractController  Returns itself to support chaining.
	 *
	 * @since   1.0
	 */
	public function setInput(Input $input)
	{
		$this->input = $input;

		return $this;
	}

	/**
	 * Unserialize the controller.
	 *
	 * @param   string  $input  The serialized controller.
	 *
	 * @return  AbstractController  Returns itself to support chaining.
	 *
	 * @since   1.0
	 * @throws  \UnexpectedValueException if input is not the right class.
	 */
	public function unserialize($input)
	{
		$input = unserialize($input);

		if (!($input instanceof Input))
		{
			throw new \UnexpectedValueException(sprintf('%s would not accept a `%s`.', __METHOD__, gettype($this->input)));
		}

		$this->setInput($input);

		return $this;
	}
}
