<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor\Button;

use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Buttons Registry class
 * @since   5.0.0
 */
final class ButtonsRegistry implements ButtonsRegistryInterface, DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * List of registered elements
     *
     * @var    array
     * @since   5.0.0
     */
    protected $registry = [];

    /**
     * Internal flag of initialisation
     *
     * @var    boolean
     * @since   5.0.0
     */
    private $initialised = false;

    /**
     * Register element in registry, add new or override existing.
     *
     * @param   ButtonInterface $instance  A button instance.
     *
     * @return  static
     * @since    5.0.0
     */
    public function add(ButtonInterface $instance): ButtonsRegistryInterface
    {
        $this->registry[$instance->getButtonName()] = $instance;

        return $this;
    }

    /**
     * Return list of all registered elements.
     *
     * @return ButtonInterface[]
     * @since    5.0.0
     */
    public function getAll(): array
    {
        return array_values($this->registry);
    }

    /**
     * Trigger event to allow to register the elements through plugins.
     *
     * @param array $options  Extra data with editor information.
     *
     * @return  ButtonsRegistryInterface
     * @since   5.0.0
     */
    public function initRegistry(array $options = []): ButtonsRegistryInterface
    {
        if ($this->initialised) {
            return $this;
        }

        $this->initialised = true;

        $options['subject']         = $this;
        $options['editorType']      = $options['editorType'] ?? '';
        $options['disabledButtons'] = $options['disabledButtons'] ?? [];

        $event      = new EditorButtonsSetupEvent('onEditorButtonsSetup', $options);
        $dispatcher = $this->getDispatcher();

        PluginHelper::importPlugin('editors-xtd', null, true, $dispatcher);
        $dispatcher->dispatch($event->getName(), $event);

        // Load legacy buttons for backward compatibility
        $plugins  = PluginHelper::getPlugin('editors-xtd');
        $editorId = $options['editorId'] ?? '';
        $asset    = (int) ($options['asset'] ?? 0);
        $author   = (int) ($options['author'] ?? 0);

        foreach ($plugins as $plugin) {
            $pluginInst = Factory::getApplication()->bootPlugin($plugin->name, 'editors-xtd');

            if ($pluginInst instanceof SubscriberInterface) {
                continue;
            }

            if (!method_exists($pluginInst, 'onDisplay')) {
                continue;
            }

            $legacyButton = $pluginInst->onDisplay($editorId, $asset, $author);

            if (empty($legacyButton)) {
                continue;
            }

            @trigger_error('6.0 Button "' . $plugin->name . '" instance should be set up onEditorButtonsSetup event.', \E_USER_DEPRECATED);

            // Transform Legacy buttons to Button object
            if ($legacyButton instanceof CMSObject || $legacyButton instanceof Registry) {
                $legacyButton = [$legacyButton];
            }

            if (\is_array($legacyButton)) {
                foreach ($legacyButton as $i => $item) {
                    // Extract button properties
                    if ($item instanceof CMSObject) {
                        $props = $item->getProperties();
                    } elseif ($item instanceof Registry) {
                        $props = $item->toArray();
                    } else {
                        continue;
                    }

                    $options = !empty($props['options']) ? $props['options'] : [];
                    // Some very old buttons use string for options, but this does not work since Joomla 3, so we reset it here
                    $options = \is_array($options) ? $options : [];
                    unset($props['options']);

                    $button = new Button($plugin->name . $i, $props, $options);
                    $this->add($button);
                }
            }
        }

        return $this;
    }
}
