<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor;

use Joomla\CMS\Editor\Button\ButtonInterface;

/**
 * Editor provider interface
 *
 * @since   5.0.0
 */
interface EditorProviderInterface
{
    /**
     * Return Editor name, CMD string.
     *
     * @return string
     * @since   5.0.0
     */
    public function getName(): string;

    /**
     * Gets the editor HTML markup
     *
     * @param   string  $name        Input name.
     * @param   string  $content     The content of the field.
     * @param   array   $attributes  Associative array of editor attributes.
     * @param   array   $params      Associative array of editor parameters.
     *
     * @return  string  The HTML markup of the editor
     *
     * @since   5.0.0
     */
    public function display(string $name, string $content = '', array $attributes = [], array $params = []): string;

    /**
     * Load the editor buttons.
     *
     * @param   mixed   $buttons  Array with button names to be excluded. Empty array or boolean true to display all buttons.
     * @param   array   $options  Associative array with additional parameters
     *
     * @return  ButtonInterface[]
     *
     * @since   5.0.0
     */
    public function getButtons($buttons, array $options = []): array;
}
