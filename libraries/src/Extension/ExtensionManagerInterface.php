<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Loads extensions.
 *
 * @since  4.0.0
 */
interface ExtensionManagerInterface
{
    /**
     * Boots the component with the given name.
     *
     * @param   string  $component  The component to boot.
     *
     * @return  ComponentInterface
     *
     * @since   4.0.0
     */
    public function bootComponent($component): ComponentInterface;

    /**
     * Boots the module with the given name.
     *
     * @param   string  $module           The module to boot
     * @param   string  $applicationName  The application name
     *
     * @return  ModuleInterface
     *
     * @since   4.0.0
     */
    public function bootModule($module, $applicationName): ModuleInterface;

    /**
     * Boots the plugin with the given name and type.
     *
     * @param   string  $plugin  The plugin name
     * @param   string  $type    The type of the plugin
     *
     * @return  PluginInterface
     *
     * @since   4.0.0
     */
    public function bootPlugin($plugin, $type): PluginInterface;
}
