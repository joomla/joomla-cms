<?php
/**
 * @version		$Id: log.php 6256 2007-01-11 17:30:32Z louis $
 * @package		Joomla.Framework
 * @subpackage	Log
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

defined('_JEXEC') or die();

/**
 * Joomla! Log Class
 *
 * This class hooks into the global log configuration
 * settings to allow for user configured logging events to be sent
 * to where the user wishes it to be sent. On high load sites
 * SysLog is probably the best (pure PHP function), then the text
 * file based formats (CSV, W3C or plain FormattedText) and finally
 * MySQL offers the most features (e.g. rapid searching) but will incur
 * a performance hit due to INSERT being issued.
 *
 * @package Joomla.Framework
 * @subpackage Log
 * @final
 * @since 1.6
 */
class JLog extends JAdapter {
	
	/** @var array formats references to formatting objects
	 *  @access private */
	var $_formats = Array();
	/** @var array entries a list of logged entries
	 * @access protected
	 */
	var $_entries = Array();
	
	/**
	 * Constructor
	 * @param $options Array of options
	 * @param $formats Array of formats 
	 */
	function __construct($options, $formats) {
		// adapter base path, class prefix
		parent::__construct(dirname(__FILE__),'JLog');
		
		if(is_string($formats)) {
			$formats = explode(',', $formats);
			foreach($formats as $key=>$format) {
				$formats[$key] = trim($format);
			}
		}
		
		if(is_array($formats)) {
			// We should have an array here
			// Params allow for CSV or Array
			foreach($formats as $format) {
				if($format) {
					$this->loadAdapter($format);
				}
			}
		}
	}
	
	/**
	 * Returns a reference to the global log object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $log = & JLog::getInstance();</pre>
	 *
	 * @static
	 * @return	object	The JLog object.
	 * @since	1.5
	 */
	function & getInstance($options = null, $formats = null) {
		static $instances;
		$config = & JFactory :: getConfig();
		if(!$options) {
			$options = $config->getValue('config.log_options');
		}
		
		if(!$formats) { 
			$formats = $config->getValue('config.log_formats');
		}
		
		$sig = md5(print_r($options,1).print_r($formats,1));		
//		jimport('joomla.filesystem.path');
//		$path = JPath :: clean($path . DS . $file, false);
//		$sig = md5($path);

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$sig])) {
			$instances[$sig] = new JLog($options, $formats);
		}

		return $instances[$sig];
	}
	
	/**
	 * Adds a log entry to sub formats and log cache
	 */
	function addEntry($entry) {
		foreach($this->_formats as $format) {
			$format->addLogEntry($entry);
		}
		$this->_entries[] = $entry;
	}
}

