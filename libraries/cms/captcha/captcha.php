<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

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
class JCaptcha implements DispatcherAwareInterface
{
	use DispatcherAwareTrait;

	/**
	 * Captcha Plugin object
	 *
	 * @var	   JPlugin
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
	 * @var	   JCaptcha[]
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
	 * @return  JCaptcha|null  Instance of this class.
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
				self::$_instances[$signature] = new static($captcha, $options);
			}
			catch (RuntimeException $e)
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

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
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

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
				'id'    => $id ? $id : $name,
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
		$name = JFilterInput::getInstance()->clean($this->_name, 'cmd');
		$path = JPATH_PLUGINS . '/captcha/' . $name . '/' . $name . '.php';

		if (!is_file($path))
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

		// Check for already loaded params
		if (!($plugin->params instanceof Registry))
		{
			$params = new Registry($plugin->params);
			$plugin->params = $params;
		}

		// Build captcha plugin classname
		$name = 'PlgCaptcha' . $this->_name;
		$dispatcher     = $this->getDispatcher();
		$this->_captcha = new $name($dispatcher, (array) $plugin, $options);
	}
}
