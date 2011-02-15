<?php
defined('_JEXEC') or die();

// Include our parent class which is another adapter
require_once(dirname(__FILE__).'/formattedtext.php');

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
	protected $_format = "{DATE}\t{TIME}\t{PRIORITY}\t{CLIENTIP}\t{TYPE}\t{MESSAGE}";

	function setProperties($options=Array())
	{
                if(!isset($options['file'])) {
			$options['file'] = 'error.w3c.php';
		}
		parent::setProperties($options);
	}
}
