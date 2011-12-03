<?php
/**
 * @version		$Id: captcha.php
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Joomla! Captcha base object
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Captcha
 * @since		1.6
 */
class JCaptcha extends JObservable
{
	/**
	 * Captcha Plugin object
	 *
	 * @var	object
	 */
	protected $_captcha;

	/**
	 * Editor Plugin name
	 *
	 * @var string
	 */
	protected $_name;

	/**
	 * Class constructor.
	 *
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
	 * @param	string	$editor  The editor to use.
	 * @return	object	The JCaptcha object.
	 */
	public static function getInstance($captcha = '', $options = array())
	{
		static $instances;

		if (is_null($instances)) $instances = array();

		$signature = md5(serialize(array($captcha, $options)));

		if (empty($instances[$signature])) {
			$instances[$signature] = new JCaptcha($captcha, $options);
		}

		return $instances[$signature];
	}

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
	 * @since	1.6
	 */
	public function display($name, $id, $class = '')
	{
		// Check if captcha is already loaded.
		if(is_null(($this->_captcha))) {
			return;
		}

		// Initialise the Captcha.
		if(!$this->initialise($id)) {
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
	 * @since	1.6
	 */
	public function checkAnswer($code)
	{
		//check if captcha is already loaded
		if (is_null(($this->_captcha))) {
			return;
		}

		$args['code'] = $code;
		$args['event'] = 'onCheckAnswer';

		return $this->_captcha->update($args);
	}

	/**
	 * Load the Captcha.
	 *
	 * @since	1.6
	 */
	protected function _load($options)
	{
		jimport('joomla.filesystem.file');

		// Build the path to the needed captcha plugin
		$name = JFilterInput::getInstance()->clean($this->_name, 'cmd');
		$path = JPATH_SITE.'/plugins/captcha/'.$name.'/'.$name.'.php';

		if (!JFile::exists($path))
		{
			$path = JPATH_SITE.'/plugins/captcha/'.$name.'.php';
			if (!JFile::exists($path))
			{
				throw new Exception(JText::sprintf('JLIB_CAPTCHA_ERROR_PLUGIN_NOT_FOUND', $name));
			}
		}

		// Require plugin file
		require_once $path;

		// Get the plugin
		$plugin   = JPluginHelper::getPlugin('captcha', $this->_name);
		if (!$plugin) throw new Exception(JText::sprintf('JLIB_CAPTCHA_ERROR_LOADING', $name));
		$params   = new JRegistry($plugin->params);
		$plugin->params = $params;

		// Build captcha plugin classname
		$name = 'plgCaptcha'.$this->_name;
		$this->_captcha = new $name($this, (array)$plugin, $options);
	}
}
