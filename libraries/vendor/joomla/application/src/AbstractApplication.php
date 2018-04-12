<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application;

use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Joomla Framework Base Application Class
 *
 * @since  1.0
 */
abstract class AbstractApplication implements LoggerAwareInterface, DispatcherAwareInterface
{
	use LoggerAwareTrait, DispatcherAwareTrait;

	/**
	 * The application configuration object.
	 *
	 * @var    Registry
	 * @since  1.0
	 */
	protected $config;

	/**
	 * The application input object.
	 *
	 * @var    Input
	 * @since  1.0
	 */
	public $input = null;

	/**
	 * Class constructor.
	 *
	 * @param   Input     $input   An optional argument to provide dependency injection for the application's input object.  If the argument is an
	 *                             Input object that object will become the application's input object, otherwise a default input object is created.
	 * @param   Registry  $config  An optional argument to provide dependency injection for the application's config object.  If the argument
	 *                             is a Registry object that object will become the application's config object, otherwise a default config
	 *                             object is created.
	 *
	 * @since   1.0
	 */
	public function __construct(Input $input = null, Registry $config = null)
	{
		$this->input  = $input ?: new Input;
		$this->config = $config ?: new Registry;

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());
		$this->set('execution.microtimestamp', microtime(true));

		$this->initialise();
	}

	/**
	 * Method to close the application.
	 *
	 * @param   integer  $code  The exit code (optional; default is 0).
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	public function close($code = 0)
	{
		exit($code);
	}

	/**
	 * Dispatches an application event if the dispatcher has been set.
	 *
	 * @param   string  $eventName  The event to dispatch.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function dispatchEvent(string $eventName)
	{
		try
		{
			$dispatcher = $this->getDispatcher();
		}
		catch (\UnexpectedValueException $exception)
		{
			return;
		}

		$dispatcher->dispatch($eventName, new Event\ApplicationEvent($eventName, $this));
	}

	/**
	 * Method to run the application routines.
	 *
	 * Most likely you will want to instantiate a controller and execute it, or perform some sort of task directly.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	abstract protected function doExecute();

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		$this->dispatchEvent(ApplicationEvents::BEFORE_EXECUTE);

		// Perform application routines.
		$this->doExecute();

		$this->dispatchEvent(ApplicationEvents::AFTER_EXECUTE);
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string  $key      The name of the property.
	 * @param   mixed   $default  The default value (optional) if none is set.
	 *
	 * @return  mixed   The value of the configuration.
	 *
	 * @since   1.0
	 */
	public function get($key, $default = null)
	{
		return $this->config->get($key, $default);
	}

	/**
	 * Get the logger.
	 *
	 * @return  LoggerInterface
	 *
	 * @since   1.0
	 */
	public function getLogger()
	{
		// If a logger hasn't been set, use NullLogger
		if (!($this->logger instanceof LoggerInterface))
		{
			$this->setLogger(new NullLogger);
		}

		return $this->logger;
	}

	/**
	 * Custom initialisation method.
	 *
	 * Called at the end of the AbstractApplication::__construct method.
	 * This is for developers to inject initialisation code for their application classes.
	 *
	 * @return  void
	 *
	 * @codeCoverageIgnore
	 * @since   1.0
	 */
	protected function initialise()
	{
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string  $key    The name of the property.
	 * @param   mixed   $value  The value of the property to set (optional).
	 *
	 * @return  mixed   Previous value of the property
	 *
	 * @since   1.0
	 */
	public function set($key, $value = null)
	{
		$previous = $this->config->get($key);
		$this->config->set($key, $value);

		return $previous;
	}

	/**
	 * Sets the configuration for the application.
	 *
	 * @param   Registry  $config  A registry object holding the configuration.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function setConfiguration(Registry $config)
	{
		$this->config = $config;

		return $this;
	}
}
