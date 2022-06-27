<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar;

/**
 * Interface for creating toolbar objects
 *
 * @since  4.0.0
 */
interface ToolbarFactoryInterface
{
    /**
     * Creates a new toolbar button.
     *
     * @param   Toolbar  $toolbar  The Toolbar instance to attach to the button
     * @param   string   $type     Button Type
     *
     * @return  ToolbarButton
     *
     * @since   4.0.0
     * @throws  \InvalidArgumentException
     */
    public function createButton(Toolbar $toolbar, string $type): ToolbarButton;

    /**
     * Creates a new Toolbar object.
     *
     * @param   string  $name  The toolbar name.
     *
     * @return  Toolbar
     *
     * @since   4.0.0
     */
    public function createToolbar(string $name = 'toolbar'): Toolbar;
}
