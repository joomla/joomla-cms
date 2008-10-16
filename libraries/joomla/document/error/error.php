<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Document
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
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
	protected $_error;
	protected $template;
	protected $baseurl;
	protected $debug;
	protected $error;
	protected $_file;
	

	/**
	 * Class constructor
	 *
	 * @access public
	 * @param	string	$type 		(either html or tex)
	 * @param	array	$attributes Associative array of attributes
	 */
	public function __construct($options = array())
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
	public function setError($error)
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
	public function render( $cache = false, $params = array())
	{
		// If no error object is set return null
		if (!isset($this->_error)) {
			return;
		}

		//Set the status header
		JResponse::setHeader('status', $this->_error->get('code').' '.str_replace( "\n", ' ', $this->_error->get('message') ));
		$file = 'error.php';

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
		$this->error	= $this->_error;

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
	public function _loadTemplate($directory, $filename)
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

	public function renderBacktrace()
	{
		return JError::renderBacktrace($this->_error);
	}

	
	/**
	 * Get the document head data
	 *
	 * @access	public
	 * @return	array	The document head data in array form
	 */
	public function getHeadData(){
		return false;
	}

	/**
	 * Set the document head data
	 *
	 * @access	public
	 * @param	array	$data	The document head data in array form
	 */
	public function setHeadData($data) {
		return false;
	}
}
