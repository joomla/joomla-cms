<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

use Joomla\CMS\Factory as CmsFactory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * JsonDocument class, provides an easy interface to parse and display JSON output
 *
 * @link   http://www.json.org/
 * @since  1.7.0
 */
class JsonDocument extends Document
{
    /**
     * Document name
     *
     * @var    string
     * @since  1.7.0
     */
    protected $_name = 'joomla';

    /**
     * Class constructor
     *
     * @param   array  $options  Associative array of options
     *
     * @since  1.7.0
     */
    public function __construct($options = [])
    {
        parent::__construct($options);

        // Set mime type
        $this->_mime = 'application/json';

        // Set document type
        $this->_type = 'json';
    }

    /**
     * Render the document.
     *
     * @param   boolean  $cache   If true, cache the output
     * @param   array    $params  Associative array of attributes
     *
     * @return  string  The rendered data
     *
     * @since  1.7.0
     */
    public function render($cache = false, $params = [])
    {
        /** @var \Joomla\CMS\Application\CMSApplication $app */
        $app = CmsFactory::getApplication();

        $app->allowCache($cache);

        parent::render($cache, $params);

        return $this->getBuffer();
    }

    /**
     * Returns the document name
     *
     * @return  string
     *
     * @since  1.7.0
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Sets the document name
     *
     * @param   string  $name  Document name
     *
     * @return  JsonDocument instance of $this to allow chaining
     *
     * @since   1.7.0
     */
    public function setName($name = 'joomla')
    {
        $this->_name = $name;

        return $this;
    }
}
