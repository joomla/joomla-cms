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
 * HTML document renderer for the component output
 *
 * @since  3.5
 */
class ComponentRenderer extends DocumentRenderer
{
    /**
     * Renders a component script and returns the results as a string
     *
     * @param   string  $component  The name of the component to render
     * @param   array   $params     Associative array of values
     * @param   string  $content    Content script
     *
     * @return  string  The output of the script
     *
     * @since   3.5
     */
    public function render($component = null, $params = [], $content = null)
    {
        return $content;
    }
}
