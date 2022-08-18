<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Plugin;

use Joomla\CMS\Application\PluginFreezingApplicationInterface;
use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Factory;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherInterface;

/**
 * Plugin helper class
 *
 * @since  1.5
 */
abstract class PluginHelper
{
    /**
     * Plugins we are allowed to load even when we are in a frozen state.
     *
     * @since  __DEPLOY_VERSION__
     */
    private const ALLOWED_PLUGINS_WHEN_FROZEN = [
        'authentication' => ['cookie', 'joomla', 'ldap'],
        'extension'      => ['joomla', 'namespacemap'],
        'installer'      => [],
        'system'         => [
            'debug', 'httpheaders', 'languagecode', 'languagefilter', 'log', 'logout',
            'remember'
        ],
        'user'           => ['joomla']
    ];

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
            $temp = explode(':', $layout);
            $template = $temp[0] === '_' ? $templateObj->template : $temp[0];
            $layout = $temp[1];
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
        $result = [];
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
    public static function importPlugin(
        $type,
        $plugin = null,
        $autocreate = true,
        DispatcherInterface $dispatcher = null
    ) {
        static $loaded = [];

        $app = Factory::getApplication();

        /**
         * Handle the frozen state of plugin loading.
         *
         * When plugin loading is frozen any attempt to import plugins or an entire plugin group
         * will be denied. There are however exceptions.
         *
         * When trying to import an entire group which exists as a key in the
         * self::ALLOWED_PLUGINS_WHEN_FROZEN array we will instead load the plugins explicitly
         * mentioned in the array value. If that value is empty we will allow loading any of these
         * plugins.
         *
         * When trying to import a specific plugin we will allow it only if its group is a key in
         * the self::ALLOWED_PLUGINS_WHEN_FROZEN array and the plugin's name exists in the value
         * of that array (or the value is empty).
         */
        if ($app instanceof PluginFreezingApplicationInterface && $app->isPluginLoadingFrozen()) {
            // If the plugin type is not in self::ALLOWED_PLUGINS_WHEN_FROZEN deny loading.
            if (!in_array($type, array_keys(self::ALLOWED_PLUGINS_WHEN_FROZEN))) {
                return false;
            }

            // We are allowed to load SOME plugins. Which ones?
            $allowedPlugins = self::ALLOWED_PLUGINS_WHEN_FROZEN[$type];

            /**
             * Special case: authentication plugins
             *
             * If the built-in plg_authentication_joomla plugin is published we keep the hard-coded
             * list of core plugins as the only allowed authentication plugins. At worst, the user
             * can authenticate with their Joomla username and password.
             *
             * If that plugin is not enabled, the site owner has enabled a custom, third party
             * authentication plugin. In this case we HOPE AND PRAY that it's compatible with the
             * new Joomla version. For us to be able to load it, all we can do is empty the
             * $allowedPlugins array which makes the code below load any plugin in the
             * authentication group.
             *
             * Rule of thumb: always publish the Joomla authentication plugin
             * (plg_authentication_joomla) before upgrading your site to avoid any nasty bugs in
             * third party code which lead to broken updates.
             */
            if ($type === 'authentication') {
                $hasJoomlaAuth = array_reduce(
                    static::load(),
                    function (bool $carry, $p) use ($type): bool {
                        return $carry
                            || (
                                is_object($p) && (
                                    ($p->type ?? '') === $type) && (($p->name ?? '') === 'joomla'
                                )
                            );
                    },
                    false
                );
                $allowedPlugins = $hasJoomlaAuth ? $allowedPlugins : [];
            }

            if ($plugin === null) {
                /**
                 * We are told to load all plugins of a type which has SOME allowed plugins under
                 * the frozen state. No problem! We will only load the specifically allowed plugins.
                 * If, however, we were given an empty list we will just allow loading ANY plugin
                 * of that type.
                 */
                if (!empty($allowedPlugins)) {
                    foreach ($allowedPlugins as $pluginName) {
                        self::importPlugin($type, $pluginName, $autocreate, $dispatcher);
                    }

                    return true;
                }
            } elseif (!empty($allowedPlugins) && !in_array($plugin, $allowedPlugins)) {
                /**
                 * We were asked to load a plugin which is not explicitly allowed under the frozen
                 * state. Sorry, I will not do that.
                 */
                return false;
            }
        }

        // Check for the default args, if so we can optimise cheaply
        $defaults = false;

        if ($plugin === null && $autocreate === true && $dispatcher === null) {
            $defaults = true;
        }

        // Ensure we have a dispatcher now so we can correctly track the loaded plugins
        $dispatcher = $dispatcher ?: $app->getDispatcher();

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
            $db = Factory::getDbo();
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
