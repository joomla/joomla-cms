<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application;

defined('JPATH_PLATFORM') or die;

use Joomla\Application\AbstractCliApplication;
use Joomla\Application\Cli\CliInput;
use Joomla\Application\Cli\CliOutput;
use Joomla\Application\Cli\Output\Stdout;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;
use Joomla\Session\SessionInterface;

/**
 * Base class for a Joomla! command line application.
 *
 * @since  11.4
 */
abstract class CliApplication extends AbstractCliApplication implements DispatcherAwareInterface, CMSApplicationInterface
{
	use Autoconfigurable, DispatcherAwareTrait, EventAware, IdentityAware, ContainerAwareTrait;

	/**
	 * The application instance.
	 *
	 * @var    CliApplication
	 * @since  11.1
	 */
	protected static $instance;

	/**
	 * Class constructor.
	 *
	 * @param   \JInputCli           $input       An optional argument to provide dependency injection for the application's
	 *                                            input object.  If the argument is a JInputCli object that object will become
	 *                                            the application's input object, otherwise a default input object is created.
	 * @param   Registry             $config      An optional argument to provide dependency injection for the application's
	 *                                            config object.  If the argument is a Registry object that object will become
	 *                                            the application's config object, otherwise a default config object is created.
	 * @param   CliOutput            $output      The output handler.
	 * @param   CliInput             $cliInput    The CLI input handler.
	 * @param   DispatcherInterface  $dispatcher  An optional argument to provide dependency injection for the application's
	 *                                            event dispatcher.  If the argument is a DispatcherInterface object that object will become
	 *                                            the application's event dispatcher, if it is null then the default event dispatcher
	 *                                            will be created based on the application's loadDispatcher() method.
	 * @param   Container            $container   Dependency injection container.
	 *
	 * @since   11.1
	 */
	public function __construct(\JInputCli $input = null, Registry $config = null, CliOutput $output = null, CliInput $cliInput = null,
		DispatcherInterface $dispatcher = null, Container $container = null)
	{
		// Close the application if we are not executed from the command line.
		if (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']))
		{
			$this->close();
		}

		$container = $container ?: \JFactory::getContainer();
		$this->setContainer($container);

		$this->input    = $input ?: new \JInputCli;
		$this->config   = $config ?: new Registry;
		$this->output   = $output ?: new Stdout;
		$this->cliInput = $cliInput ?: new CliInput;

		if ($dispatcher)
		{
			$this->setDispatcher($dispatcher);
		}

		// Load the configuration object.
		$this->loadConfiguration($this->fetchConfigurationData());

		// Set the execution datetime and timestamp;
		$this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
		$this->set('execution.timestamp', time());
		$this->set('execution.microtimestamp', microtime(true));

		// Set the current directory.
		$this->set('cwd', getcwd());

		// Set up the environment
		$this->input->set('format', 'cli');
	}

	/**
	 * Returns a reference to the global CliApplication object, only creating it if it doesn't already exist.
	 *
	 * This method must be invoked as: $cli = CliApplication::getInstance();
	 *
	 * @param   string  $name  The name (optional) of the JApplicationCli class to instantiate.
	 *
	 * @return  CliApplication
	 *
	 * @since       11.1
	 * @deprecated  5.0 Load the app through the container
	 * @throws  \RuntimeException
	 */
	public static function getInstance($name = null)
	{
		// Only create the object if it doesn't exist.
		if (empty(static::$instance))
		{
			if (!class_exists($name))
			{
				throw new \RuntimeException(sprintf('Unable to load application: %s', $name), 500);
			}

			static::$instance = new $name;
		}

		return static::$instance;
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function execute()
	{
		// Trigger the onBeforeExecute event.
		$this->triggerEvent('onBeforeExecute');

		// Perform application routines.
		$this->doExecute();

		// Trigger the onAfterExecute event.
		$this->triggerEvent('onAfterExecute');
	}

	/**
	 * Set an output object.
	 *
	 * @param   CliOutput  $output  CliOutput object
	 *
	 * @return  $this
	 *
	 * @since   3.3
	 */
	public function setOutput(CliOutput $output)
	{
		$this->output = $output;

		return $this;
	}

	/**
	 * Enqueue a system message.
	 *
	 * @param   string  $msg   The message to enqueue.
	 * @param   string  $type  The message type.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function enqueueMessage($msg, $type = self::MSG_INFO)
	{
		if (!key_exists($type, $this->messages))
		{
			$this->messages[$type] = [];
		}

		$this->messages[$type][] = $msg;
	}

	/**
	 * Get the system message queue.
	 *
	 * @return  array  The system message queue.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getMessageQueue()
	{
		return $this->messages;
	}

	/**
	 * Check the client interface by name.
	 *
	 * @param   string  $identifier  String identifier for the application interface
	 *
	 * @return  boolean  True if this application is of the given type client interface.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isClient($identifier)
	{
		return $identifier == 'cli';
	}

	/**
	 * Method to get the application session object.
	 *
	 * @return  SessionInterface  The session object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getSession()
	{
		return $this->container->get(SessionInterface::class);
	}

	/**
	 * Flag if the application instance is a CLI or web based application.
	 *
	 * Helper function, you should use the native PHP functions to detect if it is a CLI application.
	 *
	 * @return  boolean
	 *
	 * @since       __DEPLOY_VERSION__
	 * @deprecated  5.0  Will be removed without replacements
	 */
	public function isCli()
	{
		return true;
	}
}
