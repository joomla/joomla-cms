<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Editor;

use Joomla\CMS\Editor\Button\ButtonRegistryInterface;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Abstract editor provider
 *
 * @since   __DEPLOY_VERSION__
 */
abstract class AbstractEditorProvider implements EditorProviderInterface, DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * Load the editor buttons.
     *
     * @param   mixed   $buttons  Array with button names to be excluded. Empty array or boolean true to display all buttons.
     * @param   array   $options  Associative array with additional parameters
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getButtons($buttons, array $options = []): array
    {
        if ($buttons === false) {
            return [];
        }

        $loadAll = false;

        if ($buttons === true || $buttons === []) {
            $buttons = [];
            $loadAll = true;
        }

        if (!\is_array($buttons)) {
            throw new \UnexpectedValueException('The Buttons variable should be an array of names of enabled buttons or boolean.');
        }

        $result   = [];
        $editorId = $options['editorId'] ?? '';
        $asset    = $options['asset'] ?? 0;
        $author   = $options['author'] ?? 0;

        $buttonsRegistry = new class() implements ButtonRegistryInterface {
            protected $registry = [];
            public function add($item): self {
                $this->registry[$item->get('buttonName')] = $item;
                return $this;
            }
            public function getAll(): array
            {
                return array_values($this->registry);
            }
        };
        $event = new EditorButtonsSetupEvent('onEditorButtonsSetup', [
            'subject'    => $buttonsRegistry,
            'editorId'   => $editorId,
            'editorType1' => $this->getName(),
            'asset'      => $asset,
            'author'     => $author,
        ]);
        $this->getDispatcher()->dispatch($event->getName(), $event);

        dump($event, $buttonsRegistry, $options);

        // Load legacy buttons for backward compatibility
        $plugins = PluginHelper::getPlugin('editors-xtd');

        foreach ($plugins as $plugin) {
            if (!$loadAll && \in_array($plugin->name, $buttons)) {
                continue;
            }

            $plugin = Factory::getApplication()->bootPlugin($plugin->name, 'editors-xtd');

            if (!$plugin || $plugin instanceof SubscriberInterface) {
                continue;
            }

            // Try to authenticate
            if (!method_exists($plugin, 'onDisplay')) {
                continue;
            }

            $button = $plugin->onDisplay($editorId, $asset, $author);

            if (empty($button)) {
                continue;
            }

            if (\is_array($button)) {
                $result = array_merge($result, $button);
                continue;
            }

            $button->editor = $editorId;

            $result[$button->name] = $button;
        }

        return array_values($result);
    }

    /**
     * Displays the editor buttons.
     *
     * @param   mixed   $buttons  Array with button names to be excluded. Empty array or boolean true to display all buttons.
     * @param   array   $options  Associative array with additional parameters
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function displayButtons($buttons, array $options = [])
    {
        $list = $this->getButtons($buttons, $options);

        return $list ? LayoutHelper::render('joomla.editors.buttons', $list) : '';
    }
}
