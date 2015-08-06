<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Plugin
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * JPlugin Class
 *
 * @since  1.5
 */
abstract class JPlugin extends JEvent
{
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
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
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
				$this->params = new Registry;
				$this->params->loadString($config['params']);
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
			$reflection = new ReflectionClass($this);
			$appProperty = $reflection->getProperty('app');

			if ($appProperty->isPrivate() === false && is_null($this->app))
			{
				$this->app = JFactory::getApplication();
			}
		}

		if (property_exists($this, 'db'))
		{
			$reflection = new ReflectionClass($this);
			$dbProperty = $reflection->getProperty('db');

			if ($dbProperty->isPrivate() === false && is_null($this->db))
			{
				$this->db = JFactory::getDbo();
			}
		}

		parent::__construct($subject);
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

		$lang = JFactory::getLanguage();

		return $lang->load(strtolower($extension), $basePath, null, false, true)
			|| $lang->load(strtolower($extension), JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name, null, false, true);
	}
}
