<?php
defined('_JEXEC') or die();

jimport('joomla.log.formattedtext');


/**
 * Joomla! W3C Logging class
 *
 * This class is designed to build log files based on the
 * W3C specification at: http://www.w3.org/TR/WD-logfile.html
 *
 * @package 	Joomla.Framework
 * @subpackage	Log
 * @since		1.7
 */
class JLogW3C extends JLogFormattedText {
	/**
	 * Log Format
	 * @var	string
	 */
	var $_format = "{DATE}\t{TIME}\t{PRIORITY}\t{CLIENTIP}\t{TYPE}\t{MESSAGE}";

	/**
	 * Constructor
	 *
	 * @access	protected
	 * @param	array	$options	Log file options
	 * @since	1.5
	 */
	function JLogW3C($options) {
		// Set default values
		//$this->_path = $path; //* @param	string	$path		Log file path
		$this->setOptions($options);
	}

	function & getInstance($options = null) {
		static $instances;
		
		$file = $options['file'] ? $options['file'] : 'error.w3c.log'; 
		$path = $options['path'] ? $options['path'] : null;
		
		// Set default path if not set
		if (!$path) {
			$config = & JFactory :: getConfig();
			$path = $config->getValue('config.log_path');
		}

		jimport('joomla.filesystem.path');
		$path = JPath :: clean($path . DS . $file, false);
		$sig = md5($path);
		$options['path'] = $path;
		
		if (!isset ($instances)) {
			$instances = array ();
		}
		
		if (empty ($instances[$sig])) {
			$instances[$sig] = new JLogW3C($options);
		}

		return $instances[$sig];
	}
}