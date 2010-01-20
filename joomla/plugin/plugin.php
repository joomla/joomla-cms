<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Plugin
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.event.event');

/**
 * JPlugin Class
 *
 * @abstract
 * @package		Joomla.Framework
 * @subpackage	Plugin
 * @since		1.5
 */
abstract class JPlugin extends JEvent
{
	/**
	 * A JParameter object holding the parameters for the plugin
	 *
	 * @var		A JParameter object
	 * @access	public
	 * @since	1.5
	 */
	public $params = null;

	/**
	 * The name of the plugin
	 *
	 * @var		sring
	 * @access	protected
	 */
	protected $_name = null;

	/**
	 * The plugin type
	 *
	 * @var		string
	 * @access	protected
	 */
	protected $_type = null;

	/**
	 * Constructor
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'group', 'params'
	 * (this list is not meant to be comprehensive).
	 * @since 1.5
	 */
	public function __construct(&$subject, $config = array())
	{
		// Get the parameters.
		if (isset($config['params']))
		{
			if ($config['params'] instanceof JParameter) {
				$this->params = $config['params'];
			} else {
				$this->params = new JParameter($config['params']);
			}
		}

		// Get the plugin name.
		if (isset($config['name'])) {
			$this->_name = $config['name'];
		}

		// Get the plugin type.
		if (isset($config['type'])) {
			$this->_type = $config['type'];
		}
		parent::__construct($subject);
	}

	/**
	 * Loads the plugin language file
	 *
	 * @access	public
	 * @param	string 	$extension 	The extension for which a language file should be loaded
	 * @param	string 	$basePath  	The basepath to use
	 * @return	boolean	True, if the file has successfully loaded.
	 * @since	1.5
	 */
	public function loadLanguage($extension = '', $basePath = JPATH_BASE)
	{
		if (empty($extension)) {
			$extension = 'plg_'.$this->_type.'_'.$this->_name;
		}

		$lang = &JFactory::getLanguage();
		return $lang->load (strtolower($extension), $basePath.DS.'plugins'.DS.$this->_type.DS.$this->_name) || $lang->load(strtolower($extension), $basePath);
	}
}
