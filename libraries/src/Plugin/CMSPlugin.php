<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Plugin;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\Event\AbstractEvent;
use Joomla\Event\Dispatcher;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\Priority;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

/**
 * Plugin Class
 *
 * @since  1.5
 */
abstract class CMSPlugin implements DispatcherAwareInterface
{
	use DispatcherAwareTrait;

	/**
	 * A Registry object holding the parameters for the plugin
	 *
	 * @var    Registry
	 * @since  1.5
	 */
	public $params = null;

	/**
	 * The name of the plugin
	 *
	 * @var    string
	 * @since  1.5
	 */
	protected $_name = null;

	/**
	 * The plugin type
	 *
	 * @var    string
	 * @since  1.5
	 */
	protected $_type = null;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	protected $autoloadLanguage = false;

	/**
	 * Should I try to detect and register legacy event listeners, i.e. methods which accept unwrapped arguments? While
	 * this maintains a great degree of backwards compatibility to Joomla! 3.x-style plugins it is much slower. You are
	 * advised to implement your plugins using proper Listeners, methods accepting an AbstractEvent as their sole
	 * parameter, for best performance. Also bear in mind that Joomla! 5.x onwards will only allow proper listeners,
	 * removing support for legacy Listeners.
	 *
	 * @var    boolean
	 * @since  4.0
	 *
	 * @deprecated
	 */
	protected $allowLegacyListeners = true;

	/**
	 * Constructor
	 *
	 * @param   DispatcherInterface  &$subject  The object to observe
	 * @param   array                $config    An optional associative array of configuration settings.
	 *                                          Recognized key values include 'name', 'group', 'params', 'language'
	 *                                         (this list is not meant to be comprehensive).
	 *
	 * @since   1.5
	 */
	public function __construct(&$subject, $config = array())
	{
		// Get the parameters.
		if (isset($config['params']))
		{
			if ($config['params'] instanceof Registry)
			{
				$this->params = $config['params'];
			}
			else
			{
				$this->params = new Registry($config['params']);
			}
		}

		// Get the plugin name.
		if (isset($config['name']))
		{
			$this->_name = $config['name'];
		}

		// Get the plugin type.
		if (isset($config['type']))
		{
			$this->_type = $config['type'];
		}

		// Load the language files if needed.
		if ($this->autoloadLanguage)
		{
			$this->loadLanguage();
		}

		if (property_exists($this, 'app'))
		{
			$reflection = new \ReflectionClass($this);
			$appProperty = $reflection->getProperty('app');

			if ($appProperty->isPrivate() === false && is_null($this->app))
			{
				$this->app = JFactory::getApplication();
			}
		}

		if (property_exists($this, 'db'))
		{
			$reflection = new \ReflectionClass($this);
			$dbProperty = $reflection->getProperty('db');

			if ($dbProperty->isPrivate() === false && is_null($this->db))
			{
				$this->db = JFactory::getDbo();
			}
		}

		// Set the dispatcher we are to register our listeners with
		$this->setDispatcher($subject);

		// Register the event listeners with the dispatcher. Override the registerListeners method to customise.
		$this->registerListeners();
	}

	/**
	 * Loads the plugin language file
	 *
	 * @param   string  $extension  The extension for which a language file should be loaded
	 * @param   string  $basePath   The basepath to use
	 *
	 * @return  boolean  True, if the file has successfully loaded.
	 *
	 * @since   1.5
	 */
	public function loadLanguage($extension = '', $basePath = JPATH_ADMINISTRATOR)
	{
		if (empty($extension))
		{
			$extension = 'Plg_' . $this->_type . '_' . $this->_name;
		}

		$extension = strtolower($extension);
		$lang      = JFactory::getLanguage();

		// If language already loaded, don't load it again.
		if ($lang->getPaths($extension))
		{
			return true;
		}

		return $lang->load($extension, $basePath, null, false, true)
			|| $lang->load($extension, JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name, null, false, true);
	}

	/**
	 * Registers legacy Listeners to the Dispatcher, emulating how plugins worked under Joomla! 3.x and below.
	 *
	 * By default, this method will look for all public methods whose name starts with "on". It will register
	 * lambda functions (closures) which try to unwrap the arguments of the dispatched Event into method call
	 * arguments and call your on<Something> method. The result will be passed back to the Event into its 'result'
	 * argument.
	 *
	 * This method additionally supports Joomla\Event\SubscriberInterface and plugins implementing this will be
	 * registered to the dispatcher as a subscriber.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	protected function registerListeners()
	{
		// Plugins which are SubscriberInterface implementations are handled without legacy layer support
		if ($this instanceof SubscriberInterface)
		{
			// The addSubscriber method isn't part of the DispatcherInterface, emulate it if need be
			if ($this->getDispatcher() instanceof Dispatcher)
			{
				$this->getDispatcher()->addSubscriber($this);
			}
			else
			{
				foreach ($this->getSubscribedEvents() as $eventName => $params)
				{
					if (is_array($params))
					{
						$this->getDispatcher()->addListener($eventName, [$this, $params[0]], $params[1] ?? Priority::NORMAL);
					}
					else
					{
						$this->getDispatcher()->addListener($eventName, [$this, $params]);
					}
				}
			}

			return;
		}

		$reflectedObject = new \ReflectionObject($this);
		$methods = $reflectedObject->getMethods(\ReflectionMethod::IS_PUBLIC);

		/** @var \ReflectionMethod $method */
		foreach ($methods as $method)
		{
			if (substr($method->name, 0, 2) != 'on')
			{
				continue;
			}

			// Save time if I'm not to detect legacy listeners
			if (!$this->allowLegacyListeners)
			{
				$this->registerListener($method->name);

				continue;
			}

			/** @var \ReflectionParameter[] $parameters */
			$parameters = $method->getParameters();

			// If the parameter count is not 1 it is by definition a legacy listener
			if (count($parameters) != 1)
			{
				$this->registerLegacyListener($method->name);

				continue;
			}

			/** @var \ReflectionParameter $param */
			$param = array_shift($parameters);
			$typeHint = $param->getClass();
			$paramName = $param->getName();

			// No type hint / type hint class not an event and parameter name is not "event"? It's a legacy listener.
			if ((empty($typeHint) || !$typeHint->implementsInterface('Joomla\\Event\\EventInterface')) && ($paramName != 'event'))
			{
				$this->registerLegacyListener($method->name);

				continue;
			}

			// Everything checks out, this is a proper listener.
			$this->registerListener($method->name);
		}
	}

	/**
	 * Registers a legacy event listener, i.e. a method which accepts individual arguments instead of an AbstractEvent
	 * in its arguments. This provides backwards compatibility to Joomla! 3.x-style plugins.
	 *
	 * This method will register lambda functions (closures) which try to unwrap the arguments of the dispatched Event
	 * into old style method arguments and call your on<Something> method with them. The result will be passed back to
	 * the Event, as an element into an array argument called 'result'.
	 *
	 * @param   string  $methodName  The method name to register
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	final protected function registerLegacyListener(string $methodName)
	{
		$this->getDispatcher()->addListener(
			$methodName,
			function (AbstractEvent $event) use ($methodName)
			{
				// Get the event arguments
				$arguments = $event->getArguments();

				// Extract any old results; they must not be part of the method call.
				$allResults = [];

				if (isset($arguments['result']))
				{
					$allResults = $arguments['result'];

					unset($arguments['result']);
				}

				// Map the associative argument array to a numeric indexed array for efficiency (see the switch statement below).
				$arguments = array_values($arguments);

				$result = $this->{$methodName}(...$arguments);

				// Restore the old results and add the new result from our method call
				array_push($allResults, $result);
				$event['result'] = $allResults;
			}
		);
	}

	/**
	 * Registers a proper event listener, i.e. a method which accepts an AbstractEvent as its sole argument. This is the
	 * preferred way to implement plugins in Joomla! 4.x and will be the only possible method with Joomla! 5.x onwards.
	 *
	 * @param   string  $methodName  The method name to register
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	final protected function registerListener(string $methodName)
	{
		$this->getDispatcher()->addListener($methodName, [$this, $methodName]);
	}
}
