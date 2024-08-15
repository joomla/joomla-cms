<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * RawDocument class, provides an easy interface to parse and display raw output
 *
 * @since  1.7.0
 */
class RawDocument extends Document
{
    /**
     * Class constructor
     *
     * @param   array  $options  Associative array of options
     *
     * @since   1.7.0
     */
    public function __construct($options = [])
    {
        parent::__construct($options);

        // Set mime type
        $this->_mime = 'text/html';

        // Set document type
        $this->_type = 'raw';
    }

    /**
     * Render the document.
     *
     * @param   boolean  $cache   If true, cache the output
     * @param   array    $params  Associative array of attributes
     *
     * @return  string  The rendered data
     *
     * @since   1.7.0
     */
    public function render($cache = false, $params = [])
    {
        parent::render($cache, $params);

        return $this->getBuffer();
    }
}
