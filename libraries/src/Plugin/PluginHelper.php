<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Plugin;

use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Factory;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin helper class
 *
 * @since  1.5
 */
abstract class PluginHelper
{
    /**
     * A persistent cache of the loaded plugins.
     *
     * @var    array|null
     *
     * @since  1.7
     */
    protected static $plugins = null;

    /**
     * Get the path to a layout from a Plugin
     *
     * @param   string  $type    Plugin type
     * @param   string  $name    Plugin name
     * @param   string  $layout  Layout name
     *
     * @return  string  Layout path
     *
     * @since   3.0
     */
    public static function getLayoutPath($type, $name, $layout = 'default')
    {
        $templateObj   = Factory::getApplication()->getTemplate(true);
        $defaultLayout = $layout;
        $template      = $templateObj->template;

        if (strpos($layout, ':') !== false) {
            // Get the template and file name from the string
            $temp          = explode(':', $layout);
            $template      = $temp[0] === '_' ? $templateObj->template : $temp[0];
            $layout        = $temp[1];
            $defaultLayout = $temp[1] ?: 'default';
        }

        // Build the template and base path for the layout
        $tPath = JPATH_THEMES . '/' . $template . '/html/plg_' . $type . '_' . $name . '/' . $layout . '.php';
        $iPath = JPATH_THEMES . '/' . $templateObj->parent . '/html/plg_' . $type . '_' . $name . '/' . $layout . '.php';
        $bPath = JPATH_PLUGINS . '/' . $type . '/' . $name . '/tmpl/' . $defaultLayout . '.php';
        $dPath = JPATH_PLUGINS . '/' . $type . '/' . $name . '/tmpl/default.php';

        // If the template has a layout override use it
        if (is_file($tPath)) {
            return $tPath;
        }

        if (!empty($templateObj->parent) && is_file($iPath)) {
            return $iPath;
        }

        if (is_file($bPath)) {
            return $bPath;
        }

        return $dPath;
    }

    /**
     * Get the plugin data of a specific type if no specific plugin is specified
     * otherwise only the specific plugin data is returned.
     *
     * @param   string  $type    The plugin type, relates to the subdirectory in the plugins directory.
     * @param   string  $plugin  The plugin name.
     *
     * @return  mixed  An array of plugin data objects, or a plugin data object.
     *
     * @since   1.5
     */
    public static function getPlugin($type, $plugin = null)
    {
        $result  = [];
        $plugins = static::load();

        // Find the correct plugin(s) to return.
        if (!$plugin) {
            foreach ($plugins as $p) {
                // Is this the right plugin?
                if ($p->type === $type) {
                    $result[] = $p;
                }
            }
        } else {
            foreach ($plugins as $p) {
                // Is this plugin in the right group?
                if ($p->type === $type && $p->name === $plugin) {
                    $result = $p;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Checks if a plugin is enabled.
     *
     * @param   string  $type    The plugin type, relates to the subdirectory in the plugins directory.
     * @param   string  $plugin  The plugin name.
     *
     * @return  boolean
     *
     * @since   1.5
     */
    public static function isEnabled($type, $plugin = null)
    {
        $result = static::getPlugin($type, $plugin);

        return !empty($result);
    }

    /**
     * Loads all the plugin files for a particular type if no specific plugin is specified
     * otherwise only the specific plugin is loaded.
     *
     * @param   string               $type        The plugin type, relates to the subdirectory in the plugins directory.
     * @param   string               $plugin      The plugin name.
     * @param   boolean              $autocreate  Autocreate the plugin.
     * @param   DispatcherInterface  $dispatcher  Optionally allows the plugin to use a different dispatcher.
     *
     * @return  boolean  True on success.
     *
     * @since   1.5
     */
    public static function importPlugin($type, $plugin = null, $autocreate = true, DispatcherInterface $dispatcher = null)
    {
        static $loaded = [];

        // Check for the default args, if so we can optimise cheaply
        $defaults = false;

        if ($plugin === null && $autocreate === true && $dispatcher === null) {
            $defaults = true;
        }

        // Ensure we have a dispatcher now so we can correctly track the loaded plugins
        $dispatcher = $dispatcher ?: Factory::getApplication()->getDispatcher();

        // Get the dispatcher's hash to allow plugins to be registered to unique dispatchers
        $dispatcherHash = spl_object_hash($dispatcher);

        if (!isset($loaded[$dispatcherHash])) {
            $loaded[$dispatcherHash] = [];
        }

        if (!$defaults || !isset($loaded[$dispatcherHash][$type])) {
            $results = null;

            // Load the plugins from the database.
            $plugins = static::load();

            // Get the specified plugin(s).
            for ($i = 0, $t = \count($plugins); $i < $t; $i++) {
                if ($plugins[$i]->type === $type && ($plugin === null || $plugins[$i]->name === $plugin)) {
                    static::import($plugins[$i], $autocreate, $dispatcher);
                    $results = true;
                }
            }

            // Bail out early if we're not using default args
            if (!$defaults) {
                return $results;
            }

            $loaded[$dispatcherHash][$type] = $results;
        }

        return $loaded[$dispatcherHash][$type];
    }

    /**
     * Loads the plugin file.
     *
     * @param   object               $plugin      The plugin.
     * @param   boolean              $autocreate  True to autocreate.
     * @param   DispatcherInterface  $dispatcher  Optionally allows the plugin to use a different dispatcher.
     *
     * @return  void
     *
     * @since   3.2
     */
    protected static function import($plugin, $autocreate = true, DispatcherInterface $dispatcher = null)
    {
        static $plugins = [];

        // Get the dispatcher's hash to allow paths to be tracked against unique dispatchers
        $hash = spl_object_hash($dispatcher) . $plugin->type . $plugin->name;

        if (\array_key_exists($hash, $plugins)) {
            return;
        }

        $plugins[$hash] = true;

        $plugin = Factory::getApplication()->bootPlugin($plugin->name, $plugin->type);

        if ($dispatcher && $plugin instanceof DispatcherAwareInterface) {
            $plugin->setDispatcher($dispatcher);
        }

        if (!$autocreate) {
            return;
        }

        $plugin->registerListeners();
    }

    /**
     * Loads the published plugins.
     *
     * @return  array  An array of published plugins
     *
     * @since   3.2
     */
    protected static function load()
    {
        if (static::$plugins !== null) {
            return static::$plugins;
        }

        $levels = Factory::getUser()->getAuthorisedViewLevels();

        /** @var \Joomla\CMS\Cache\Controller\CallbackController $cache */
        $cache = Factory::getCache('com_plugins', 'callback');

        $loader = function () use ($levels) {
            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select(
                    $db->quoteName(
                        [
                            'folder',
                            'element',
                            'params',
                            'extension_id',
                        ],
                        [
                            'type',
                            'name',
                            'params',
                            'id',
                        ]
                    )
                )
                ->from($db->quoteName('#__extensions'))
                ->where(
                    [
                        $db->quoteName('enabled') . ' = 1',
                        $db->quoteName('type') . ' = ' . $db->quote('plugin'),
                        $db->quoteName('state') . ' IN (0,1)',
                    ]
                )
                ->whereIn($db->quoteName('access'), $levels)
                ->order($db->quoteName('ordering'));
            $db->setQuery($query);

            return $db->loadObjectList();
        };

        try {
            static::$plugins = $cache->get($loader, [], md5(implode(',', $levels)), false);
        } catch (CacheExceptionInterface $cacheException) {
            static::$plugins = $loader();
        }

        return static::$plugins;
    }
}
