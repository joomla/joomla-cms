<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor\Button;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Editor button interface
 *
 * @since   5.0.0
 */
interface ButtonInterface
{
    /**
     * Return Button name, CMD string.
     *
     * @return string
     * @since   5.0.0
     */
    public function getButtonName(): string;

    /**
     * Return Button property or null.
     *
     * @param string $name Property name
     *
     * @return mixed
     * @since   5.0.0
     */
    public function get(string $name);

    /**
     * Set Button property.
     *
     * @param string $name  Property name
     * @param mixed  $value Property value
     *
     * @return ButtonInterface
     * @since   5.0.0
     */
    public function set(string $name, $value): ButtonInterface;

    /**
     * Return Button options.
     *
     * @return array
     * @since   5.0.0
     */
    public function getOptions(): array;

    /**
     * Set Button options.
     *
     * @param array  $options The button options.
     *
     * @return ButtonInterface
     * @since   5.0.0
     */
    public function setOptions(array $options): ButtonInterface;
}
