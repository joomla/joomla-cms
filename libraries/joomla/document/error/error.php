<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Document
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * DocumentError class, provides an easy interface to parse and display an error page
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
class JDocumentError extends JDocument
{
	/**
	 * Error Object
	 * @var	object
	 */
	var $_error;

	/**
	 * Class constructor
	 *
	 * @access protected
	 * @param	string	$type 		(either html or tex)
	 * @param	array	$attributes Associative array of attributes
	 */
	function __construct($options = array())
	{
		parent::__construct($options);

		//set mime type
		$this->_mime = 'text/html';

		//set document type
		$this->_type = 'error';
	}

	/**
	 * Set error object
	 *
	 * @access	public
	 * @param	object	$error	Error object to set
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function setError($error)
	{
		if (JError::isError($error)) {
			$this->_error = & $error;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Render the document
	 *
	 * @access public
	 * @param boolean 	$cache		If true, cache the output
	 * @param array		$params		Associative array of attributes
	 */
	function render( $cache = false, $params = array())
	{
		global $mainframe;

		// If no error object is set return null
		if (!isset($this->_error)) {
			return;
		}

		// Send error header and set error page file
		switch ($this->_error->code)
		{
			case '403':
				JResponse::setHeader('status', '403 Forbidden');
				$file = "403.php";
				break;

			case '404':
				JResponse::setHeader('status', '404 Not Found');
				$file = "404.php";
				break;

			case '500':
			default:
				JResponse::setHeader('status', '500 Internal Server Error');
				$file = "500.php";
				break;
		}

		// check template
		$directory	= isset($params['directory']) ? $params['directory'] : 'templates';
		$template	= isset($params['template']) ? JFilterInput::clean($params['template'], 'cmd') : 'system';

		if ( !file_exists( $directory.DS.$template.DS.$file) ) {
			$template = 'system';
		}

		//set variables
		$this->baseurl  = JURI::base(true);
		$this->template = $template;
		$this->debug	= isset($params['debug']) ? $params['debug'] : false;
		$this->message	= JText::_($this->_error->message);

		// load
		$data = $this->_loadTemplate($directory.DS.$template, $file);

		parent::render();
		return $data;
	}

	/**
	 * Load a template file
	 *
	 * @param string 	$template	The name of the template
	 * @param string 	$filename	The actual filename
	 * @return string The contents of the template
	 */
	function _loadTemplate($directory, $filename)
	{
		$contents = '';

		//Check to see if we have a valid template file
		if ( file_exists( $directory.DS.$filename ) )
		{
			//store the file path
			$this->_file = $directory.DS.$filename;

			//get the file content
			ob_start();
			require_once $directory.DS.$filename;
			$contents = ob_get_contents();
			ob_end_clean();
		}

		return $contents;
	}

	function renderBacktrace()
	{
		$contents	= null;
		$backtrace	= $this->_error->getBacktrace();
		if( is_array( $backtrace ) )
		{
			ob_start();
			$j	=	1;
			echo  	'<table border="0" cellpadding="0" cellspacing="0" class="Table">';
			echo  	'	<tr>';
			echo  	'		<td colspan="3" align="left" class="TD"><strong>Call stack</strong></td>';
			echo  	'	</tr>';
			echo  	'	<tr>';
			echo  	'		<td class="TD"><strong>#</strong></td>';
			echo  	'		<td class="TD"><strong>Function</strong></td>';
			echo  	'		<td class="TD"><strong>Location</strong></td>';
			echo  	'	</tr>';
			for( $i = count( $backtrace )-1; $i >= 0 ; $i-- )
			{
				echo  	'	<tr>';
				echo  	'		<td class="TD">'.$j.'</td>';
				if( isset( $backtrace[$i]['class'] ) ) {
					echo  	'	<td class="TD">'.$backtrace[$i]['class'].$backtrace[$i]['type'].$backtrace[$i]['function'].'()</td>';
				} else {
					echo  	'	<td class="TD">'.$backtrace[$i]['function'].'()</td>';
				}
				if( isset( $backtrace[$i]['file'] ) ) {
					echo  	'		<td class="TD">'.$backtrace[$i]['file'].':'.$backtrace[$i]['line'].'</td>';
				} else {
					echo  	'		<td class="TD">&nbsp;</td>';
				}
				echo  	'	</tr>';
				$j++;
			}
			echo  	'</table>';
			$contents = ob_get_contents();
			ob_end_clean();
		}
		return $contents;
	}
}