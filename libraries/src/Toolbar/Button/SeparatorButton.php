<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

use Joomla\CMS\Toolbar\ToolbarButton;

/**
 * Renders a button separator
 *
 * @since  3.0
 */
class SeparatorButton extends ToolbarButton
{
    /**
     * Property layout.
     *
     * @var  string
     *
     * @since  4.0.0
     */
    protected $layout = 'joomla.toolbar.separator';

    /**
     * Empty implementation (not required for separator)
     *
     * @return  void
     *
     * @since   3.0
     *
     * @deprecated  5.0 Use render() instead.
     */
    public function fetchButton()
    {
    }
}
