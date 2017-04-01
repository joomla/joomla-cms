<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Cms\Captcha;

defined('JPATH_PLATFORM') or die;

use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\Dispatcher;
use Joomla\Event\Event;
use Joomla\Registry\Registry;

/**
 * Joomla! Captcha base object
 *
 * @abstract
 * @package     Joomla.Libraries
 * @subpackage  Captcha
 * @since       2.5
 */
<<<<<<< HEAD:libraries/cms/captcha/captcha.php
class JCaptcha implements DispatcherAwareInterface
=======
class Captcha extends JObject
>>>>>>> 3.8-dev:libraries/src/Joomla/Cms/Captcha/Captcha.php
{
	use DispatcherAwareTrait;

	/**
	 * Captcha Plugin object
	 *
	 * @var	   \JPlugin
	 * @since  2.5
	 */
	private $_captcha;

	/**
	 * Editor Plugin name
	 *
	 * @var    string
	 * @since  2.5
	 */
	private $_name;

	/**
	 * Array of instances of this class.
	 *
	 * @var	   Captcha[]
	 * @since  2.5
	 */
	private static $_instances = array();

	/**
	 * Class constructor.
	 *
	 * @param   string  $captcha  The editor to use.
	 * @param   array   $options  Associative array of options.
	 *
	 * @since   2.5
	 */
	public function __construct($captcha, $options)
	{
		$this->_name = $captcha;
		$this->setDispatcher(JFactory::getApplication()->getDispatcher());
		$this->_load($options);
	}

	/**
	 * Returns the global Captcha object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string  $captcha  The plugin to use.
	 * @param   array   $options  Associative array of options.
	 *
	 * @return  Captcha|null  Instance of this class.
	 *
	 * @since   2.5
	 */
	public static function getInstance($captcha, array $options = array())
	{
		$signature = md5(serialize(array($captcha, $options)));

		if (empty(self::$_instances[$signature]))
		{
			try
			{
<<<<<<< HEAD:libraries/cms/captcha/captcha.php
				self::$_instances[$signature] = new static($captcha, $options);
=======
				self::$_instances[$signature] = new Captcha($captcha, $options);
>>>>>>> 3.8-dev:libraries/src/Joomla/Cms/Captcha/Captcha.php
			}
			catch (RuntimeException $e)
			{
				\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

				return;
			}
		}

		return self::$_instances[$signature];
	}

	/**
	 * Fire the onInit event to initialise the captcha plugin.
	 *
	 * @param   string  $id  The id of the field.
	 *
	 * @return  boolean  True on success
	 *
	 * @since	2.5
	 */
	public function initialise($id)
	{
		$event = new Event(
			'onInit',
			['id' => $id]
		);

		try
		{
			$this->getDispatcher()->dispatch('onInit', $event);
		}
		catch (Exception $e)
		{
			\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Get the HTML for the captcha.
	 *
	 * @param   string  $name   The control name.
	 * @param   string  $id     The id for the control.
	 * @param   string  $class  Value for the HTML class attribute
	 *
	 * @return  string  The return value of the function "onDisplay" of the selected Plugin.
	 *
	 * @since   2.5
	 */
	public function display($name, $id, $class = '')
	{
		// Check if captcha is already loaded.
		if (is_null($this->_captcha))
		{
			return '';
		}

		// Initialise the Captcha.
		if (!$this->initialise($id))
		{
			return '';
		}

		$event = new Event(
			'onDisplay',
			[
				'name'  => $name,
				'id'    => $id ?: $name,
				'class' => $class ? 'class="' . $class . '"' : '',
			]
		);

		$result = $this->getDispatcher()->dispatch('onInit', $event);

		// TODO REFACTOR ME! This is Ye Olde Way of returning plugin results192
		return $result['result'][0];
	}

	/**
	 * Checks if the answer is correct.
	 *
	 * @param   string  $code  The answer.
	 *
	 * @return  bool   The return value of the function "onCheckAnswer" of the selected Plugin.
	 *
	 * @since	2.5
	 */
	public function checkAnswer($code)
	{
		// Check if captcha is already loaded
		if (is_null(($this->_captcha)))
		{
			return false;
		}

		$event = new Event(
			'onCheckAnswer',
			['code'	=> $code]
		);

		$result = $this->getDispatcher()->dispatch('onCheckAnswer', $event);

		// TODO REFACTOR ME! This is Ye Olde Way of returning plugin results
		return $result['result'][0];
	}

	/**
	 * Load the Captcha plugin.
	 *
	 * @param   array  $options  Associative array of options.
	 *
	 * @return  void
	 *
	 * @since	2.5
	 * @throws  RuntimeException
	 */
	private function _load(array $options = array())
	{
		// Build the path to the needed captcha plugin
		$name = \JFilterInput::getInstance()->clean($this->_name, 'cmd');
		$path = JPATH_PLUGINS . '/captcha/' . $name . '/' . $name . '.php';

		if (!is_file($path))
		{
			throw new RuntimeException(\JText::sprintf('JLIB_CAPTCHA_ERROR_PLUGIN_NOT_FOUND', $name));
		}

		// Require plugin file
		require_once $path;

		// Get the plugin
		$plugin = \JPluginHelper::getPlugin('captcha', $this->_name);

		if (!$plugin)
		{
			throw new RuntimeException(\JText::sprintf('JLIB_CAPTCHA_ERROR_PLUGIN_NOT_FOUND', $name));
		}

		// Check for already loaded params
		if (!($plugin->params instanceof Registry))
		{
			$params = new Registry($plugin->params);
			$plugin->params = $params;
		}

		// Build captcha plugin classname
		$name = 'PlgCaptcha' . $this->_name;
<<<<<<< HEAD:libraries/cms/captcha/captcha.php
		$dispatcher     = $this->getDispatcher();
		$this->_captcha = new $name($dispatcher, (array) $plugin, $options);
=======
		$this->_captcha = new $name($this, (array) $plugin, $options);
	}

	/**
	 * Get the state of the \JEditor object
	 *
	 * @return  mixed  The state of the object.
	 *
	 * @since   2.5
	 */
	public function getState()
	{
		return $this->_state;
	}

	/**
	 * Attach an observer object
	 *
	 * @param   object  $observer  An observer object to attach
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function attach($observer)
	{
		if (is_array($observer))
		{
			if (!isset($observer['handler']) || !isset($observer['event']) || !is_callable($observer['handler']))
			{
				return;
			}

			// Make sure we haven't already attached this array as an observer
			foreach ($this->_observers as $check)
			{
				if (is_array($check) && $check['event'] == $observer['event'] && $check['handler'] == $observer['handler'])
				{
					return;
				}
			}

			$this->_observers[] = $observer;
			end($this->_observers);
			$methods = array($observer['event']);
		}
		else
		{
			if (!($observer instanceof \JEditor))
			{
				return;
			}

			// Make sure we haven't already attached this object as an observer
			$class = get_class($observer);

			foreach ($this->_observers as $check)
			{
				if ($check instanceof $class)
				{
					return;
				}
			}

			$this->_observers[] = $observer;
			$methods = array_diff(get_class_methods($observer), get_class_methods('\JPlugin'));
		}

		$key = key($this->_observers);

		foreach ($methods as $method)
		{
			$method = strtolower($method);

			if (!isset($this->_methods[$method]))
			{
				$this->_methods[$method] = array();
			}

			$this->_methods[$method][] = $key;
		}
	}

	/**
	 * Detach an observer object
	 *
	 * @param   object  $observer  An observer object to detach.
	 *
	 * @return  boolean  True if the observer object was detached.
	 *
	 * @since   2.5
	 */
	public function detach($observer)
	{
		$retval = false;

		$key = array_search($observer, $this->_observers);

		if ($key !== false)
		{
			unset($this->_observers[$key]);
			$retval = true;

			foreach ($this->_methods as &$method)
			{
				$k = array_search($key, $method);

				if ($k !== false)
				{
					unset($method[$k]);
				}
			}
		}

		return $retval;
>>>>>>> 3.8-dev:libraries/src/Joomla/Cms/Captcha/Captcha.php
	}
}
