<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');

/**
 * Joomla! Captcha base object
 *
 * @abstract
 * @package		Joomla.Libraries
 * @subpackage	Captcha
 * @since		2.5
 */
class JCaptcha extends JObject
{
	/**
	 * An array of Observer objects to notify
	 *
	 * @var    array
	 * @since  2.5
	 */
	protected $_observers = array();

	/**
	 * The state of the observable object
	 *
	 * @var    mixed
	 * @since  2.5
	 */
	protected $_state = null;

	/**
	 * A multi dimensional array of [function][] = key for observers
	 *
	 * @var    array
	 * @since  2.5
	 */
	protected $_methods = array();

	/**
	 * Captcha Plugin object
	 *
	 * @var	object
	 * @since  2.5
	 */
	private $_captcha;

	/**
	 * Editor Plugin name
	 *
	 * @var string
	 * @since  2.5
	 */
	private $_name;

	/**
	 * Captcha Plugin object
	 *
	 * @var	array
	 */
	private static $_instances = array();

	/**
	 * Class constructor.
	 *
	 * @param	string	$editor  The editor to use.
	 * @param	array	$options  Associative array of options.
	 *
	 * @since 2.5
	 */
	public function __construct($captcha, $options)
	{
		$this->_name = $captcha;
		$this->_load($options);
	}

	/**
	 * Returns the global Captcha object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param	string	$captcha  The plugin to use.
	 * @param	array	$options  Associative array of options.
	 *
	 * @return	object	The JCaptcha object.
	 *
	 * @since 2.5
	 */
	public static function getInstance($captcha, array $options = array())
	{
		$signature = md5(serialize(array($captcha, $options)));

		if (empty(self::$_instances[$signature]))
		{
			try
			{
				self::$_instances[$signature] = new JCaptcha($captcha, $options);
			}
			catch (RuntimeException $e)
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				return null;
			}
		}

		return self::$_instances[$signature];
	}

	/**
	 * @return boolean True on success
	 *
	 * @since	2.5
	 */
	public function initialise($id)
	{
		$args['id']		= $id ;
		$args['event']	= 'onInit';

		try
		{
			$this->_captcha->update($args);
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			return false;
		}

		return true;
	}

	/**
	 * Get the HTML for the captcha.
	 *
	 * @return 	the return value of the function "onDisplay" of the selected Plugin.
	 *
	 * @since	2.5
	 */
	public function display($name, $id, $class = '')
	{
		// Check if captcha is already loaded.
		if (is_null($this->_captcha))
		{
			return;
		}

		// Initialise the Captcha.
		if (!$this->initialise($id))
		{
			return;
		}

		$args['name']		= $name;
		$args['id']			= $id ? $id : $name;
		$args['class']		= $class ? 'class="'.$class.'"' : '';
		$args['event']		= 'onDisplay';

		return $this->_captcha->update($args);
	}

	/**
	 * Checks if the answer is correct.
	 *
	 * @return 	the return value of the function "onCheckAnswer" of the selected Plugin.
	 *
	 * @since	2.5
	 */
	public function checkAnswer($code)
	{
		//check if captcha is already loaded
		if (is_null(($this->_captcha)))
		{
			return;
		}

		$args['code'] = $code;
		$args['event'] = 'onCheckAnswer';

		return $this->_captcha->update($args);
	}

	/**
	 * Load the Captcha plug-in.
	 *
	 * @param	array	$options  Associative array of options.
	 *
	 * @return  void
	 *
	 * @since	2.5
	 * @throws  RuntimeException
	 */
	private function _load(array $options = array())
	{
		// Build the path to the needed captcha plugin
		$name = JFilterInput::getInstance()->clean($this->_name, 'cmd');
		$path = JPATH_PLUGINS . '/captcha/' . $name . '/' . $name . '.php';

		if (!JFile::exists($path))
		{
			throw new RuntimeException(JText::sprintf('JLIB_CAPTCHA_ERROR_PLUGIN_NOT_FOUND', $name));
		}

		// Require plugin file
		require_once $path;

		// Get the plugin
		$plugin = JPluginHelper::getPlugin('captcha', $this->_name);
		if (!$plugin)
		{
			throw new RuntimeException(JText::sprintf('JLIB_CAPTCHA_ERROR_PLUGIN_NOT_FOUND', $name));
		}
		$params = new JRegistry($plugin->params);
		$plugin->params = $params;

		// Build captcha plugin classname
		$name = 'plgCaptcha' . $this->_name;
		$this->_captcha = new $name($this, (array)$plugin, $options);
	}

		/**
	 * Get the state of the JEditor object
	 *
	 * @return  mixed    The state of the object.
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
			if (!($observer instanceof JEditor))
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
			$methods = array_diff(get_class_methods($observer), get_class_methods('JPlugin'));
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
		// Initialise variables.
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
	}
}
