<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor\Button;

use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Buttons Registry class
 * @since   __DEPLOY_VERSION__
 */
final class ButtonsRegistry implements ButtonsRegistryInterface, DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * List of registered elements
     *
     * @var    array
     * @since   __DEPLOY_VERSION__
     */
    protected $registry = [];

    /**
     * Internal flag of initialisation
     *
     * @var    boolean
     * @since   __DEPLOY_VERSION__
     */
    private $initialised = false;

    /**
     * Register element in registry, add new or override existing.
     *
     * @param   ButtonInterface $instance  A button instance.
     *
     * @return  static
     * @since    __DEPLOY_VERSION__
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
     * @since    __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
     */
    public function initRegistry(array $options = []): ButtonsRegistryInterface
    {
        if (!$this->initialised) {
            $this->initialised = true;

            $options['subject']    = $this;
            $options['editorType'] = $options['editorType'] ?? '';

            $event      = new EditorButtonsSetupEvent('onEditorButtonsSetup', $options);
            $dispatcher = $this->getDispatcher();

            PluginHelper::importPlugin('editors-xtd', null, true, $dispatcher);
            $dispatcher->dispatch($event->getName(), $event);
        }

        return $this;
    }
}
