<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document;

use Joomla\CMS\Uri\Uri;

/**
 * Abstract class for a renderer
 *
 * @since  1.7.0
 */
abstract class DocumentRenderer implements RendererInterface
{
    /**
     * Reference to the Document object that instantiated the renderer
     *
     * @var    Document
     * @since  1.7.0
     */
    protected $_doc = null;

    /**
     * Renderer mime type
     *
     * @var    string
     * @since  1.7.0
     */
    protected $_mime = 'text/html';

    /**
     * Class constructor
     *
     * @param   Document  $doc  A reference to the Document object that instantiated the renderer
     *
     * @since   1.7.0
     */
    public function __construct(Document $doc)
    {
        $this->_doc = $doc;
    }

    /**
     * Return the content type of the renderer
     *
     * @return  string  The contentType
     *
     * @since   1.7.0
     */
    public function getContentType()
    {
        return $this->_mime;
    }

    /**
     * Convert links in a text from relative to absolute
     *
     * @param   string  $text  The text processed
     *
     * @return  string   Text with converted links
     *
     * @since   1.7.0
     */
    protected function _relToAbs($text)
    {
        $base = Uri::base();
        $text = preg_replace("/(href|src)=\"(?!http|ftp|https|mailto|data|\/\/)([^\"]*)\"/", "$1=\"$base\$2\"", $text);

        return $text;
    }
}
