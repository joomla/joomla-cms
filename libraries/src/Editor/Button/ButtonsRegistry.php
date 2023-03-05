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
final class ButtonsRegistry implements ButtonsRegistryInterface
{
    /**
     * List of registered elements
     *
     * @var    array
     * @since   __DEPLOY_VERSION__
     */
    protected $registry = [];

    /**
     * Register element in registry, add new or override existing.
     *
     * @param   object $instance
     *
     * @return  static
     * @since    __DEPLOY_VERSION__
     */
    public function add($item): ButtonsRegistryInterface
    {
        $this->registry[$item->getButtonName()] = $item;

        return $this;
    }

    /**
     * Return list of all registered elements
     *
     * @return array
     * @since    __DEPLOY_VERSION__
     */
    public function getAll(): array
    {
        return array_values($this->registry);
    }
}
