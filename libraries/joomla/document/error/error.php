<?php
/**
* @version $Id$
* @package Joomla.Framework
* @subpackage Document
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

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

		// If no error object is set return null
		if (!isset($this->_error)) {
			return;
		}

		// Send error header and set error page file
		switch ($this->_error->code)
		{
			case '403':
				header('HTTP/1.1 403 Forbidden');
				$file = "403.php";
				break;

			case '404':
				header('HTTP/1.1 404 Not Found');
				$file = "404.php";
				break;

			case '500':
			default:
				header('HTTP/1.1 500 Internal Server Error');
				$file = "500.php";
				break;
		}

		// check template
		$directory = isset($params['directory']) ? $params['directory'] : 'templates';
		$template  = isset($params['template']) ? $params['template'] : '_system';
		if ( !file_exists( $directory.DS.$template.DS.$file) ) {
			$template = '_system';
		}

		// create the document engine
		$this->_engine = $this->_initEngine($template);
		if (file_exists($directory.DS.$template.DS.$file))
		{
			//get the file content
			ob_start();
			?>
			<jdoc:tmpl name="document" autoclear="yes">
			<?php require_once($directory.DS.$template.DS.$file); ?>
			</jdoc:tmpl>
			<?php
			$contents = ob_get_contents();
			ob_end_clean();
			$this->_engine->readTemplatesFromInput( $contents, 'String' );
		}
		// set the base href message
		$this->_engine->addVar('document', 'base_href', JURI::base());

		$this->_engine->addVar('document', 'sitename', $mainframe->getCfg( 'sitename' ));

		// set the error message
		$this->_engine->addVar('document', 'message', JText::_($this->_error->message));

		// if debugging is enabled set the error backtrace text
		$debug = isset($params['debug']) ? $params['debug'] : false;
		if ($debug) {
			$this->_engine->addVar('document', 'backtrace', $this->_fetchBacktrace());
		}

		// render the page
		$this->_engine->display('document');
	}

	/**
	 * Create document engine
	 *
	 * @access public
	 * @param string 	$template 	The actual template name
	 * @param boolean 	$caching	If true, cache the template
	 * @return object
	 */
	function _initEngine($template)
	{
		jimport('joomla.template.template');
		$instance = new JTemplate();

		//set a reference to the document in the engine
		$instance->doc =& $this;

		//set the namespace
		$instance->setNamespace( 'jdoc' );

		//Add template variables
		$instance->addVar( 'document', 'lang_tag', $this->getLanguage() );
		$instance->addVar( 'document', 'lang_dir', $this->getDirection() );
		$instance->addVar( 'document', 'template', $template );

		return $instance;
	}

	function _fetchBacktrace()
	{
		$contents  = null;
		$backtrace = $this->_error->getBacktrace();
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
?>
