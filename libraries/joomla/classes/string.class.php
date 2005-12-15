<?php

/**
* @version $Id: string.php 
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport('phputf8.utf8');

/**
 * String handling class for utf-8 data
 * 
 * @static
 * @author David Gal <david@joomla.co.il>
 * @package  Joomla
 * @subpackage Language
 * @since 1.1
 */
class JString
{
	/**
	 * Version of strpos supporting utf-8
	 * 
	 * @access public
	 * @see strpos
	 */
	function strpos($str, $search, $offset = FALSE){
	    if ( $offset === FALSE ) {
	        return utf8_strpos($str, $search);
	    } else {
	        return utf8_strpos($str, $search, $offset);
	    }
	}

	/**
	 * Version of strrpos supporting utf-8
	 * 
	 * @access public
	 * @see strepos
	 */
	function strrpos($str, $search, $offset = FALSE){
	    if ( $offset === FALSE ) {
	        return utf8_strrpos($str, $search);
	    } else {
	        return utf8_strrpos($str, $search, $offset);
	    }
	}

	/**
	 * Version of substr supporting utf-8
	 * 
	 * @access public
	 * @see substr
	 */
	function substr($str, $offset, $length = FALSE){
	    if ( $length === FALSE ) {
	        return utf8_substr($str, $offset);
	    } else {
	        return utf8_substr($str, $offset, $length);
	    }
	}	 

	/**
	 * Version of strtolower supporting utf-8
	 * 
	 * @access public
	 * @see strtolower
	 */
	function strtolower($str){
	    return utf8_strtolower($str);
	}
	
	/**
	 * Version of strtoupper supporting utf-8
	 * 
	 * @access public
	 * @see strtoupper
	 */
	function strtoupper($str){
	    return utf8_strtoupper($str);
	}

	/**
	 * Version of strlen supporting utf-8
	 * 
	 * @access public
	 * @see strlen
	 */
	function strlen($str){
	    return utf8_strlen($str);
	}


}
?>