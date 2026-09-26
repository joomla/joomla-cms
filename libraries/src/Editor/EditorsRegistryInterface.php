<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor;

use Joomla\CMS\Editor\Exception\EditorNotFoundException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface for Editor Registry classes
 *
 * @since   5.0.0
 */
interface EditorsRegistryInterface
{
    /**
     * Check whether the element exists in the registry.
     *
     * @param   string  $name  Element name
     *
     * @return  bool
     * @since    5.0.0
     */
    public function has(string $name): bool;

    /**
     * Return element by name.
     *
     * @param   string  $name  Element name
     *
     * @return  EditorProviderInterface
     * @throws  EditorNotFoundException
     * @since    5.0.0
     */
    public function get(string $name): EditorProviderInterface;

    /**
     * Register element in registry, add new or override existing.
     *
     * @param   EditorProviderInterface $instance
     *
     * @return  EditorsRegistryInterface
     * @since    5.0.0
     */
    public function add(EditorProviderInterface $instance): EditorsRegistryInterface;

    /**
     * Return list of all registered elements
     *
     * @return EditorProviderInterface[]
     * @since    5.0.0
     */
    public function getAll(): array;

    /**
     * Initial set up of the registry elements through plugins etc.
     *
     * @return  EditorsRegistryInterface
     * @since   5.0.0
     */
    public function initRegistry(): EditorsRegistryInterface;
}
