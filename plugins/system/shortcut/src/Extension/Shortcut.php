<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.shortcut
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Shortcut\Extension;

use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Shortcut plugin to add accessible keyboard shortcuts to the administrator templates.
 *
 * @since  4.2.0
 */
final class Shortcut extends CMSPlugin implements SubscriberInterface, DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  - The method name to call (priority defaults to 0)
     *  - An array composed of the method name to call and the priority
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *
     * @return  array
     *
     * @since   4.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeCompileHead' => 'initialize',
            'onLoadShortcuts'     => 'addShortcuts',
        ];
    }

    /**
     * Add the javascript for the shortcuts
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function initialize()
    {
        if (!$this->getApplication()->isClient('administrator')) {
            return;
        }

        // Load translations
        $this->loadLanguage();

        $context = $this->getApplication()->getInput()->get('option') . '.' . $this->getApplication()->getInput()->get('view');

        $shortcuts = [];

        $event = new GenericEvent(
            'onLoadShortcuts',
            [
                'context'   => $context,
                'shortcuts' => $shortcuts,
            ]
        );

        $this->getDispatcher()->dispatch('onLoadShortcuts', $event);

        $shortcuts = $event->getArgument('shortcuts');

        Text::script('PLG_SYSTEM_SHORTCUT_OVERVIEW_HINT');
        Text::script('PLG_SYSTEM_SHORTCUT_OVERVIEW_TITLE');
        Text::script('PLG_SYSTEM_SHORTCUT_OVERVIEW_DESC');
        Text::script('PLG_SYSTEM_SHORTCUT_THEN');
        Text::script('JCLOSE');

        $document = $this->getApplication()->getDocument();
        $wa       = $document->getWebAssetManager();
        $wa->registerAndUseScript(
            'plg_system_shortcut.shortcut',
            'plg_system_shortcut/shortcut.min.js',
            [],
            ['type' => 'module'],
            ['hotkeysjs', 'joomla.dialog']
        );

        $timeout = $this->params->get('timeout', 2000);

        $document->addScriptOptions('plg_system_shortcut.shortcuts', $shortcuts);
        $document->addScriptOptions('plg_system_shortcut.timeout', $timeout);
    }

    /**
     * Add default shortcuts to the document
     *
     * @param   Event  $event  The event
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function addShortcuts(Event $event)
    {
        $shortcuts = $event->getArgument('shortcuts', []);

        $shortcuts = array_merge(
            $shortcuts,
            [
                'applyKey'   => (object) ['selector' => 'joomla-toolbar-button .button-apply', 'shortcut' => 'A', 'title' => $this->getApplication()->getLanguage()->_('JAPPLY')],
                'saveKey'    => (object) ['selector' => 'joomla-toolbar-button .button-save', 'shortcut' => 'S', 'title' => $this->getApplication()->getLanguage()->_('JTOOLBAR_SAVE')],
                'cancelKey'  => (object) ['selector' => 'joomla-toolbar-button .button-cancel', 'shortcut' => 'Q', 'title' => $this->getApplication()->getLanguage()->_('JCANCEL')],
                'newKey'     => (object) ['selector' => 'joomla-toolbar-button .button-new', 'shortcut' => 'N', 'title' => $this->getApplication()->getLanguage()->_('JTOOLBAR_NEW')],
                'searchKey'  => (object) ['selector' => 'input[placeholder=' . $this->getApplication()->getLanguage()->_('JSEARCH_FILTER') . ']', 'shortcut' => 'F', 'title' => $this->getApplication()->getLanguage()->_('JSEARCH_FILTER')],
                'optionKey'  => (object) ['selector' => 'joomla-toolbar-button .button-options', 'shortcut' => 'O', 'title' => $this->getApplication()->getLanguage()->_('JOPTIONS')],
                'helpKey'    => (object) ['selector' => 'joomla-toolbar-button .button-help', 'shortcut' => 'H', 'title' => $this->getApplication()->getLanguage()->_('JHELP')],
                'toggleMenu' => (object) ['selector' => '#menu-collapse', 'shortcut' => 'M', 'title' => $this->getApplication()->getLanguage()->_('JTOGGLE_SIDEBAR_MENU')],
                'dashboard'  => (object) ['selector' => (string) new Uri(Route::_('index.php?')), 'shortcut' => 'D', 'title' => $this->getApplication()->getLanguage()->_('JHOMEDASHBOARD')],
            ]
        );

        $event->setArgument('shortcuts', $shortcuts);
    }
}
