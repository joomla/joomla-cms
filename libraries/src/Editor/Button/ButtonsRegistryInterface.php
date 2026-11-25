<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor\Button;

\defined('_JEXEC') or die;

/**
 * Buttons Registry class
 * @since   5.0.0
 */
interface ButtonsRegistryInterface
{
    /**
     * Register element in registry, add new or override existing.
     *
     * @param   ButtonInterface $instance
     *
     * @return  static
     * @since    5.0.0
     */
    public function add(ButtonInterface $instance): ButtonsRegistryInterface;

    /**
     * Return list of all registered elements
     *
     * @return ButtonInterface[]
     * @since    5.0.0
     */
    public function getAll(): array;

    /**
     * Initialise the registry, eg: auto-register elements.
     *
     * @param array $options  Extra data with editor information.
     *
     * @return  ButtonsRegistryInterface
     * @since   5.0.0
     */
    public function initRegistry(array $options = []): ButtonsRegistryInterface;
}
