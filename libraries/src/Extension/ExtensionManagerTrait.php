<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Extension;

use Joomla\CMS\Dispatcher\ModuleDispatcherFactory;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Helper\HelperFactory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\Exception\ContainerNotFoundException;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for classes which can load extensions
 *
 * @since  4.0.0
 */
trait ExtensionManagerTrait
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
    public function bootComponent($component): ComponentInterface
    {
        // Normalize the component name
        $component = strtolower(str_replace('com_', '', $component));

        // Path to look for services
        $path = JPATH_ADMINISTRATOR . '/components/com_' . $component;

        return $this->loadExtension(ComponentInterface::class, $component, $path);
    }

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
    public function bootModule($module, $applicationName): ModuleInterface
    {
        // Normalize the module name
        $module = strtolower(str_replace('mod_', '', $module));

        // Path to look for services
        $path = JPATH_SITE . '/modules/mod_' . $module;

        if ($applicationName === 'administrator') {
            $path = JPATH_ADMINISTRATOR . '/modules/mod_' . $module;
        }

        return $this->loadExtension(ModuleInterface::class, $module, $path);
    }

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
    public function bootPlugin($plugin, $type): PluginInterface
    {
        // Normalize the plugin name
        $plugin = strtolower(str_replace('plg_', '', $plugin));

        // Path to look for services
        $path = JPATH_SITE . '/plugins/' . $type . '/' . $plugin;

        return $this->loadExtension(PluginInterface::class, $plugin . ':' . $type, $path);
    }

    /**
     * Loads the extension.
     *
     * @param   string  $type           The extension type
     * @param   string  $extensionName  The extension name
     * @param   string  $extensionPath  The path of the extension
     *
     * @return  ComponentInterface|ModuleInterface|PluginInterface
     *
     * @since   4.0.0
     */
    private function loadExtension($type, $extensionName, $extensionPath)
    {
        // Check if the extension is already loaded
        if (!empty(ExtensionHelper::$extensions[$type][$extensionName])) {
            return ExtensionHelper::$extensions[$type][$extensionName];
        }

        // The container to get the services from
        $container = $this->getContainer()->createChild();

        $container->get(DispatcherInterface::class)->dispatch(
            'onBeforeExtensionBoot',
            AbstractEvent::create(
                'onBeforeExtensionBoot',
                [
                    'subject'       => $this,
                    'type'          => $type,
                    'extensionName' => $extensionName,
                    'container'     => $container
                ]
            )
        );

        // The path of the loader file
        $path = $extensionPath . '/services/provider.php';

        if (is_file($path)) {
            // Load the file
            $provider = require_once $path;

            // Check if the extension supports the service provider interface
            if ($provider instanceof ServiceProviderInterface) {
                $provider->register($container);
            }
        }

        // Fallback to legacy
        if (!$container->has($type)) {
            switch ($type) {
                case ComponentInterface::class:
                    $container->set($type, new LegacyComponent('com_' . $extensionName));
                    break;
                case ModuleInterface::class:
                    $container->set($type, new Module(new ModuleDispatcherFactory(''), new HelperFactory('')));
                    break;
                case PluginInterface::class:
                    list($pluginName, $pluginType) = explode(':', $extensionName);
                    $container->set($type, $this->loadPluginFromFilesystem($pluginName, $pluginType));
            }
        }

        $container->get(DispatcherInterface::class)->dispatch(
            'onAfterExtensionBoot',
            AbstractEvent::create(
                'onAfterExtensionBoot',
                [
                    'subject'       => $this,
                    'type'          => $type,
                    'extensionName' => $extensionName,
                    'container'     => $container
                ]
            )
        );

        $extension = $container->get($type);

        if ($extension instanceof BootableExtensionInterface) {
            $extension->boot($container);
        }

        // Cache the extension
        ExtensionHelper::$extensions[$type][$extensionName] = $extension;

        return $extension;
    }

    /**
     * Creates a CMS plugin from the filesystem.
     *
     * @param   string  $plugin  The plugin
     * @param   string  $type    The type
     *
     * @return  CMSPlugin
     *
     * @since   4.0.0
     */
    private function loadPluginFromFilesystem(string $plugin, string $type)
    {
        // The dispatcher
        $dispatcher = $this->getContainer()->get(DispatcherInterface::class);

        // Clear the names
        $plugin = preg_replace('/[^A-Z0-9_\.-]/i', '', $plugin);
        $type   = preg_replace('/[^A-Z0-9_\.-]/i', '', $type);

        // The path of the plugin
        $path = JPATH_PLUGINS . '/' . $type . '/' . $plugin . '/' . $plugin . '.php';

        // Return an empty class when the file doesn't exist
        if (!is_file($path)) {
            return new DummyPlugin($dispatcher);
        }

        // Include the file of the plugin
        require_once $path;

        // Compile the classname
        $className = 'Plg' . str_replace('-', '', $type) . $plugin;

        if ($type === 'editors-xtd') {
            // This type doesn't follow the convention
            $className = 'PlgEditorsXtd' . $plugin;

            if (!class_exists($className)) {
                $className = 'PlgButton' . $plugin;
            }
        }

        // Return an empty class when the class doesn't exist
        if (!class_exists($className)) {
            return new DummyPlugin($dispatcher);
        }

        // Instantiate the plugin
        return new $className($dispatcher, (array) PluginHelper::getPlugin($type, $plugin));
    }

    /**
     * Get the DI container.
     *
     * @return  Container
     *
     * @since   4.0.0
     * @throws  ContainerNotFoundException May be thrown if the container has not been set.
     */
    abstract protected function getContainer();
}
