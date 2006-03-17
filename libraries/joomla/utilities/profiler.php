<?php

/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.common.base.object' );

/**
 * Page generation time
 * 
 * @package 	Joomla.Framework
 * @subpackage	Utilities
 * @since 1.0
 */
class JProfiler extends JObject
{
	/** 
	 * 
	 * @var int 
	 */
	var $_start = 0;
	
	/** 
	 * 
	 * @var string 
	 */
	var $_prefix = '';
	
	/** 
	 * 
	 * @var array 
	 */
	var $_buffer= null;

	/**
	 * Constructor
	 * 
	 * @access protected
	 * @param string Prefix for mark messages
	 */
	function __construct( $prefix = '' ) 
	{
		$this->_start = $this->getmicrotime();
		$this->_prefix = $prefix;
		$this->_buffer = array();
	}
	
	/**
	 * Returns a reference to the global Profiler object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $browser = &JProfiler::getInstance([$prefix]);</pre>
	 *
	 * @access public
	 * @return JProfiler  The Profiler object.
	 */
	function &getInstance($prefix = '')
	{
		static $instances;

		if (!isset($instances)) {
			$instances = array();
		}

		if (empty($instances[$prefix])) {
			$instances[$prefix] = new JProfiler($prefix);
		}

		return $instances[$prefix];
	}

	/**
	 * Output a time mark
	 * 
	 * @access public
	 * @var string A label for the time mark
	 */
	function mark( $label ) 
	{
		$mark = sprintf ( "\n<div class=\"profiler\">$this->_prefix %.3f $label</div>", $this->getmicrotime() - $this->_start );
		$this->_buffer[] = $mark;
		return $mark;
	}

	/**
	 * Reports on the buffered marks
	 * 
	 * @access public
	 * @param string Glue string
	 */
	function report( $glue='' )  {
		return implode( $glue, $this->_buffer );
	}

	/**
	 * 
	 * @access public
	 * @return float The current time
	 */
	function getmicrotime()
	{
		list( $usec, $sec ) = explode( ' ', microtime() );
		return ((float)$usec + (float)$sec);
	}

	/**
	 * 
	 * @access public
	 * @return int The memory usage
	 */
	function getMemory() 
	{
		static $isWin;

		if (function_exists( 'memory_get_usage' )) 
		{
			return memory_get_usage();
		} 
		else 
		{
			if (is_null( $isWin )) {
				$isWin = (substr(PHP_OS, 0, 3) == 'WIN');
			}
			if ($isWin) {
				// Windows workaround
				$output = array();
				$pid = getmypid();
				exec( 'tasklist /FI "PID eq ' . $pid . '" /FO LIST', $output );
				return substr( $output[5], strpos( $output[5], ':' ) + 1 );
			} else {
				return 0;
			}
		}
	}
}

?>