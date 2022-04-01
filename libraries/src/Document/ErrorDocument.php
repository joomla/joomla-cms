<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

/**
 * ErrorDocument class, provides an easy interface to parse and display an error page
 *
 * @since  1.7.0
 */
class ErrorDocument extends Document
{
	/**
	 * Document base URL
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $baseurl = '';

	/**
	 * Flag if debug mode has been enabled
	 *
	 * @var    boolean
	 * @since  1.7.0
	 */
	public $debug = false;

	/**
	 * Error Object
	 *
	 * @var    \Exception|\Throwable
	 * @since  1.7.0
	 */
	public $error;

	/**
	 * Name of the template
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $template = null;

	/**
	 * File name
	 *
	 * @var    array
	 * @since  1.7.0
	 */
	public $_file = null;

	/**
	 * Error Object
	 *
	 * @var    \Exception|\Throwable
	 * @since  1.7.0
	 */
	protected $_error;

	/**
	 * Class constructor
	 *
	 * @param   array  $options  Associative array of attributes
	 *
	 * @since   1.7.0
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
	 * @param   \Exception|\Throwable  $error  Error object to set
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.7.0
	 */
	public function setError($error)
	{
		$expectedClass = PHP_MAJOR_VERSION >= 7 ? '\\Throwable' : '\\Exception';

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
	 * @since   1.7.0
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

		$errorReporting = \JFactory::getConfig()->get('error_reporting');

		if ($errorReporting === "development" || $errorReporting === "maximum")
		{
			$status .= ' ' . str_replace("\n", ' ', $this->_error->getMessage());
		}

		\JFactory::getApplication()->setHeader('status', $status);

		$file = 'error.php';

		// Check template
		$directory = isset($params['directory']) ? $params['directory'] : 'templates';
		$template = isset($params['template']) ? \JFilterInput::getInstance()->clean($params['template'], 'cmd') : 'system';

		if (!file_exists($directory . '/' . $template . '/' . $file))
		{
			$template = 'system';
		}

		// Set variables
		$this->baseurl = Uri::base(true);
		$this->template = $template;
		$this->debug = isset($params['debug']) ? $params['debug'] : false;
		$this->error = $this->_error;

		// Load the language file for the template if able
		if (\JFactory::$language)
		{
			$lang = \JFactory::getLanguage();

			// 1.5 or core then 1.6
			$lang->load('tpl_' . $template, JPATH_BASE, null, false, true)
				|| $lang->load('tpl_' . $template, $directory . '/' . $template, null, false, true);
		}

		// Load
		$data = $this->_loadTemplate($directory . '/' . $template, $file);

		parent::render($cache, $params);

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
	 * @since   1.7.0
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
	 * @since   1.7.0
	 */
	public function renderBacktrace()
	{
		// If no error object is set return null
		if (!isset($this->_error))
		{
			return;
		}

		// The back trace
		$backtrace = $this->_error->getTrace();

		// Add the position of the actual file
		array_unshift($backtrace, array('file' => $this->_error->getFile(), 'line' => $this->_error->getLine(), 'function' => ''));

		return LayoutHelper::render('joomla.error.backtrace', array('backtrace' => $backtrace));
	}
}
