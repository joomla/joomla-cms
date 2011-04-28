<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * DocumentError class, provides an easy interface to parse and display an error page
 *
 * @package		Joomla.Platform
 * @subpackage	Document
 * @since		11.1
 */

jimport('joomla.document.document');

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
	 * @param	string	$type		(either html or text)
	 * @param	array	$attributes Associative array of attributes
	 */
	protected function __construct($options = array())
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
	 * @param	object	$error	Error object to set
	 *
	 * @return	boolean	True on success
	 * @since	11.1
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
	 * @param boolean	$cache		If true, cache the output
	 * @param array		$params		Associative array of attributes
	 */
	public function render($cache = false, $params = array())
	{
		// If no error object is set return null
		if (!isset($this->_error)) {
			return;
		}

		//Set the status header
		JResponse::setHeader('status', $this->_error->getCode().' '.str_replace("\n", ' ', $this->_error->getMessage()));
		$file = 'error.php';

		// check template
		$directory	= isset($params['directory']) ? $params['directory'] : 'templates';
		$template	= isset($params['template']) ? JFilterInput::getInstance()->clean($params['template'], 'cmd') : 'system';

		if (!file_exists($directory.DS.$template.DS.$file)) {
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
	 * @param string	$template	The name of the template
	 * @param string	$filename	The actual filename
	 *
	 * @return string The contents of the template
	 */
	function _loadTemplate($directory, $filename)
	{
		$contents = '';

		// Check to see if we have a valid template file
		if (file_exists($directory.DS.$filename))
		{
			// Store the file path
			$this->_file = $directory.DS.$filename;

			// Get the file content
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
		$backtrace	= $this->_error->getTrace();
		if (is_array($backtrace))
		{
			ob_start();
			$j	=	1;
			echo	'<table border="0" cellpadding="0" cellspacing="0" class="Table">';
			echo	'	<tr>';
			echo	'		<td colspan="3" class="TD"><strong>Call stack</strong></td>';
			echo	'	</tr>';
			echo	'	<tr>';
			echo	'		<td class="TD"><strong>#</strong></td>';
			echo	'		<td class="TD"><strong>Function</strong></td>';
			echo	'		<td class="TD"><strong>Location</strong></td>';
			echo	'	</tr>';
			for ($i = count($backtrace)-1; $i >= 0 ; $i--)
			{
				echo	'	<tr>';
				echo	'		<td class="TD">'.$j.'</td>';
				if (isset($backtrace[$i]['class'])) {
					echo	'	<td class="TD">'.$backtrace[$i]['class'].$backtrace[$i]['type'].$backtrace[$i]['function'].'()</td>';
				} else {
					echo	'	<td class="TD">'.$backtrace[$i]['function'].'()</td>';
				}
				if (isset($backtrace[$i]['file'])) {
					echo	'		<td class="TD">'.$backtrace[$i]['file'].':'.$backtrace[$i]['line'].'</td>';
				} else {
					echo	'		<td class="TD">&#160;</td>';
				}
				echo	'	</tr>';
				$j++;
			}
			echo	'</table>';
			$contents = ob_get_contents();
			ob_end_clean();
		}
		return $contents;
	}
}
