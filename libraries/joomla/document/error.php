<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * DocumentError class, provides an easy interface to parse and display an error page
 *
 * @since  11.1
 */
class JDocumentError extends JDocument
{
	/**
	 * Document base URL
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $baseurl = '';

	/**
	 * Flag if debug mode has been enabled
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	public $debug = false;

	/**
	 * Error Object
	 *
	 * @var    Exception|Throwable
	 * @since  11.1
	 */
	public $error;

	/**
	 * Name of the template
	 *
	 * @var    string
	 * @since  11.1
	 */
	public $template = null;

	/**
	 * File name
	 *
	 * @var    array
	 * @since  11.1
	 */
	public $_file = null;

	/**
	 * Error Object
	 *
	 * @var    Exception|Throwable
	 * @since  11.1
	 */
	protected $_error;

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Associative array of attributes
	 *
	 * @since   11.1
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		// Set mime type
		$this->_mime = 'text/html';

		// Set document type
		$this->_type = 'error';
	}

	/**
	 * Set error object
	 *
	 * @param   Exception|Throwable  $error  Error object to set
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function setError($error)
	{
		$expectedClass = PHP_MAJOR_VERSION >= 7 ? 'Throwable' : 'Exception';

		if ($error instanceof $expectedClass)
		{
			$this->_error = & $error;

			return true;
		}

		return false;
	}

	/**
	 * Render the document
	 *
	 * @param   boolean  $cache   If true, cache the output
	 * @param   array    $params  Associative array of attributes
	 *
	 * @return  string   The rendered data
	 *
	 * @since   11.1
	 */
	public function render($cache = false, $params = array())
	{
		// If no error object is set return null
		if (!isset($this->_error))
		{
			return;
		}

		// Set the status header
		$status = $this->_error->getCode();

		if ($status < 400 || $status > 599)
		{
			$status = 500;
		}

		$errorReporting = JFactory::getConfig()->get('error_reporting');

		if ($errorReporting === "development" || $errorReporting === "maximum")
		{
			$status .= ' ' . str_replace("\n", ' ', $this->_error->getMessage());
		}

		JFactory::getApplication()->setHeader('status', $status);

		$file = 'error.php';

		// Check template
		$directory = isset($params['directory']) ? $params['directory'] : 'templates';
		$template = isset($params['template']) ? JFilterInput::getInstance()->clean($params['template'], 'cmd') : 'system';

		if (!file_exists($directory . '/' . $template . '/' . $file))
		{
			$template = 'system';
		}

		// Set variables
		$this->baseurl = JUri::base(true);
		$this->template = $template;
		$this->debug = isset($params['debug']) ? $params['debug'] : false;
		$this->error = $this->_error;

		// Load the language file for the template if able
		if (JFactory::$language)
		{
			$lang = JFactory::getLanguage();
	
			// 1.5 or core then 1.6
			$lang->load('tpl_' . $template, JPATH_BASE, null, false, true)
				|| $lang->load('tpl_' . $template, $directory . '/' . $template, null, false, true);
		}

		// Load
		$data = $this->_loadTemplate($directory . '/' . $template, $file);

		parent::render();

		return $data;
	}

	/**
	 * Load a template file
	 *
	 * @param   string  $directory  The name of the template
	 * @param   string  $filename   The actual filename
	 *
	 * @return  string  The contents of the template
	 *
	 * @since   11.1
	 */
	public function _loadTemplate($directory, $filename)
	{
		$contents = '';

		// Check to see if we have a valid template file
		if (file_exists($directory . '/' . $filename))
		{
			// Store the file path
			$this->_file = $directory . '/' . $filename;

			// Get the file content
			ob_start();
			require_once $directory . '/' . $filename;
			$contents = ob_get_contents();
			ob_end_clean();
		}

		return $contents;
	}

	/**
	 * Render the backtrace
	 *
	 * @return  string  The contents of the backtrace
	 *
	 * @since   11.1
	 */
	public function renderBacktrace()
	{
		// If no error object is set return null
		if (!isset($this->_error))
		{
			return;
		}

		$contents = null;
		$backtrace = $this->_error->getTrace();

		if (is_array($backtrace))
		{
			ob_start();
			$j = 1;
			echo '<table cellpadding="0" cellspacing="0" class="Table">';
			echo '	<tr>';
			echo '		<td colspan="3" class="TD"><strong>Call stack</strong></td>';
			echo '	</tr>';
			echo '	<tr>';
			echo '		<td class="TD"><strong>#</strong></td>';
			echo '		<td class="TD"><strong>Function</strong></td>';
			echo '		<td class="TD"><strong>Location</strong></td>';
			echo '	</tr>';

			for ($i = count($backtrace) - 1; $i >= 0; $i--)
			{
				echo '	<tr>';
				echo '		<td class="TD">' . $j . '</td>';

				if (isset($backtrace[$i]['class']))
				{
					echo '	<td class="TD">' . $backtrace[$i]['class'] . $backtrace[$i]['type'] . $backtrace[$i]['function'] . '()</td>';
				}
				else
				{
					echo '	<td class="TD">' . $backtrace[$i]['function'] . '()</td>';
				}

				if (isset($backtrace[$i]['file']))
				{
					echo '		<td class="TD">' . $backtrace[$i]['file'] . ':' . $backtrace[$i]['line'] . '</td>';
				}
				else
				{
					echo '		<td class="TD">&#160;</td>';
				}

				echo '	</tr>';
				$j++;
			}

			echo '</table>';
			$contents = ob_get_contents();
			ob_end_clean();
		}

		return $contents;
	}
}
