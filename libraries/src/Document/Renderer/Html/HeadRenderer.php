<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Renderer\Html;

use Joomla\CMS\Document\DocumentRenderer;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML document renderer for the document `<head>` element
 *
 * @since  3.5
 */
class HeadRenderer extends DocumentRenderer
{
    /**
     * Renders the document head and returns the results as a string
     *
     * @param   string  $head     (unused)
     * @param   array   $params   Associative array of values
     * @param   string  $content  The script
     *
     * @return  string  The output of the script
     *
     * @since   3.5
     */
    public function render($head, $params = [], $content = null)
    {
        $buffer  = '';
        $buffer .= $this->_doc->loadRenderer('metas')->render($head, $params, $content);
        $buffer .= $this->_doc->loadRenderer('styles')->render($head, $params, $content);
        $buffer .= $this->_doc->loadRenderer('scripts')->render($head, $params, $content);

        return $buffer;
    }
}
