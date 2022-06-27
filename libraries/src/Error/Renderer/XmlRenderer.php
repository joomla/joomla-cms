<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Error\Renderer;

use Joomla\CMS\Error\AbstractRenderer;

/**
 * XML error page renderer
 *
 * @since  4.0.0
 */
class XmlRenderer extends AbstractRenderer
{
    /**
     * The format (type) of the error page
     *
     * @var    string
     * @since  4.0.0
     */
    protected $type = 'xml';

    /**
     * Render the error page for the given object
     *
     * @param   \Throwable  $error  The error object to be rendered
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function render(\Throwable $error): string
    {
        // Create our data object to be rendered
        $xw = new \XMLWriter();
        $xw->openMemory();
        $xw->setIndent(true);
        $xw->setIndentString("\t");
        $xw->startDocument('1.0', 'UTF-8');

        $xw->startElement('error');

        $xw->writeElement('code', $error->getCode());
        $xw->writeElement('message', $error->getMessage());

        // Include the stack trace if in debug mode
        if (JDEBUG) {
            $xw->writeElement('trace', $error->getTraceAsString());
        }

        // End error element
        $xw->endElement();

        // Push the data object into the document
        $this->getDocument()->setBuffer($xw->outputMemory(true));

        if (ob_get_contents()) {
            ob_end_clean();
        }

        return $this->getDocument()->render();
    }
}
