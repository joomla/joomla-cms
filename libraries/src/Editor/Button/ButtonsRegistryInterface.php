<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor\Button;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Buttons Registry class
 * @since   __DEPLOY_VERSION__
 */
interface ButtonsRegistryInterface
{
    /**
     * Register element in registry, add new or override existing.
     *
     * @param   ButtonInterface $instance
     *
     * @return  static
     * @since    __DEPLOY_VERSION__
     */
    public function add(ButtonInterface $instance): ButtonsRegistryInterface;

    /**
     * Return list of all registered elements
     *
     * @return ButtonInterface[]
     * @since    __DEPLOY_VERSION__
     */
    public function getAll(): array;

    /**
     * Initialise the registry, eg: auto-register elements.
     *
     * @param array $options  Extra data with editor information.
     *
     * @return  ButtonsRegistryInterface
     * @since   __DEPLOY_VERSION__
     */
    public function initRegistry(array $options = []): ButtonsRegistryInterface;
}
