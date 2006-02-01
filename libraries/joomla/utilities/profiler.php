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
	/** @var int */
	var $start=0;
	/** @var string */
	var $prefix='';
	/** @var array */
	var $buffer= null;

	/**
	 * Constructor
	 * @var string Prefix for mark messages
	 */
	function __construct( $prefix='' ) {
		$this->start = $this->getmicrotime();
		$this->prefix = $prefix;
		$this->buffer = array();
	}

	/**
	 * Output a time mark
	 * @var string A label for the time mark
	 */
	function mark( $label ) {
		$mark = sprintf ( "\n<div class=\"profiler\">$this->prefix %.3f $label</div>", $this->getmicrotime() - $this->start );
		$this->buffer[] = $mark;
		return $mark;
	}

	/**
	 * Reports on the buffered marks
	 * @param string Glue string
	 */
	function report( $glue='' ) {
		return implode( $glue, $this->buffer );
	}

	/**
	 * @return float The current time
	 */
	function getmicrotime(){
		list( $usec, $sec ) = explode( ' ', microtime() );
		return ((float)$usec + (float)$sec);
	}

	function getMemory() {
		static $isWin;

		if (function_exists( 'memory_get_usage' )) {
			return memory_get_usage();
		} else {
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