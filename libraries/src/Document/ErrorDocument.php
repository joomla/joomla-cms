<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

use Joomla\CMS\Factory as CmsFactory;
use Joomla\CMS\Layout\LayoutHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * ErrorDocument class, provides an easy interface to parse and display an HTML based error page
 *
 * @since  1.7.0
 */
class ErrorDocument extends HtmlDocument
{
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
     * @var    \Throwable
     * @since  1.7.0
     */
    public $error;

    /**
     * Error Object
     *
     * @var    \Throwable
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
    public function __construct($options = [])
    {
        parent::__construct($options);

        // Set document type
        $this->_type = 'error';
    }

    /**
     * Set error object
     *
     * @param   \Throwable  $error  Error object to set
     *
     * @return  boolean  True on success
     *
     * @since   1.7.0
     */
    public function setError($error)
    {
        if ($error instanceof \Throwable) {
            $this->_error = & $error;

            return true;
        }

        return false;
    }

    /**
     * Load a renderer
     *
     * @param   string  $type  The renderer type
     *
     * @return  RendererInterface
     *
     * @since   4.0.0
     * @throws  \RuntimeException
     */
    public function loadRenderer($type)
    {
        // Need to force everything to go to the HTML renderers or we duplicate all the things
        return $this->factory->createRenderer($this, $type, 'html');
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
    public function render($cache = false, $params = [])
    {
        // If no error object is set return null
        if (!isset($this->_error)) {
            return;
        }

        // Set the status header
        $status = $this->_error->getCode();

        if ($status < 400 || $status > 599) {
            $status = 500;
        }

        $errorReporting = CmsFactory::getApplication()->get('error_reporting');

        if ($errorReporting === "development" || $errorReporting === "maximum") {
            $status .= ' ' . str_replace("\n", ' ', $this->_error->getMessage());
        }

        CmsFactory::getApplication()->setHeader('status', $status);

        // Set variables
        $this->debug = $params['debug'] ?? false;
        $this->error = $this->_error;

        $params['file'] = 'error.php';

        return parent::render($cache, $params);
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
        if (!isset($this->_error)) {
            return;
        }

        // The back trace
        $backtrace = $this->_error->getTrace();

        // Add the position of the actual file
        array_unshift($backtrace, ['file' => $this->_error->getFile(), 'line' => $this->_error->getLine(), 'function' => '']);

        return LayoutHelper::render('joomla.error.backtrace', ['backtrace' => $backtrace]);
    }
}
