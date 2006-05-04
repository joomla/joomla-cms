<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * DocumentRAW class, provides an easy interface to parse and display raw output
 *
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */

class JDocumentRAW extends JDocument
{
	
	/**
	 * Class constructore
	 *
	 * @access protected
	 * @param	string	$type 		(either html or tex)
	 * @param	array	$attributes Associative array of attributes
	 */
	function __construct($attributes = array())
	{
		parent::__construct($attributes);

		//set mime type
		$this->_mime = 'text/html';
		
		//set document type
		$this->_type = 'raw';
	}

	/**
	 * Outputs the document to the browser.
	 *
	 * @access public
	 * @param boolean 	$cache		If true, cache the output 
	 * @param boolean 	$compress	If true, compress the output
	 * @param array		$params	    Associative array of attributes
	 */
	function display( $cache = false, $compress = false, $params = array())
	{
		global $mainframe;

		$option = $mainframe->getOption();

		$path 	= JApplicationHelper::getPath( 'front', $option );
		$task 	= JRequest::getVar( 'task' );

		//load common language files
		$lang =& $mainframe->getLanguage();
		$lang->load($option);
		require_once( $path );

		parent::display( $template, $file, $compress, $params );
	}
}
?>