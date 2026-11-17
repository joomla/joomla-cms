<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\Finder\Extension;

use Joomla\CMS\Event\Finder as FinderEvent;
use Joomla\CMS\Event\Model;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Smart Search Content Plugin
 *
 * @since  2.5
 */
final class Finder extends CMSPlugin implements SubscriberInterface, DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * Flag to check whether finder plugins already imported.
     *
     * @var bool
     *
     * @since  5.0.0
     */
    protected $pluginsImported = false;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentBeforeSave'   => 'onContentBeforeSave',
            'onContentAfterSave'    => 'onContentAfterSave',
            'onContentAfterDelete'  => 'onContentAfterDelete',
            'onContentChangeState'  => 'onContentChangeState',
            'onCategoryChangeState' => 'onCategoryChangeState',
        ];
    }

    /**
     * Smart Search after save content method.
     * Content is passed by reference, but after the save, so no changes will be saved.
     * Method is called right after the content is saved.
     *
     * @param   Model\AfterSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentAfterSave(Model\AfterSaveEvent $event): void
    {
        $this->importFinderPlugins();

        // Trigger the onFinderAfterSave event.
        $this->getDispatcher()->dispatch('onFinderAfterSave', new FinderEvent\AfterSaveEvent('onFinderAfterSave', [
            'context' => $event->getContext(),
            'subject' => $event->getItem(),
            'isNew'   => $event->getIsNew(),
        ]));
    }

    /**
     * Smart Search before save content method.
     * Content is passed by reference. Method is called before the content is saved.
     *
     * @param   Model\BeforeSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentBeforeSave(Model\BeforeSaveEvent $event)
    {
        $this->importFinderPlugins();

        // Trigger the onFinderBeforeSave event.
        $this->getDispatcher()->dispatch('onFinderBeforeSave', new FinderEvent\BeforeSaveEvent('onFinderBeforeSave', [
            'context' => $event->getContext(),
            'subject' => $event->getItem(),
            'isNew'   => $event->getIsNew(),
        ]));
    }

    /**
     * Smart Search after delete content method.
     * Content is passed by reference, but after the deletion.
     *
     * @param   Model\AfterDeleteEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentAfterDelete(Model\AfterDeleteEvent $event): void
    {
        $this->importFinderPlugins();

        // Trigger the onFinderAfterDelete event.
        $this->getDispatcher()->dispatch('onFinderAfterDelete', new FinderEvent\AfterDeleteEvent('onFinderAfterDelete', [
            'context' => $event->getContext(),
            'subject' => $event->getItem(),
        ]));
    }

    /**
     * Smart Search content state change method.
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   Model\AfterChangeStateEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentChangeState(Model\AfterChangeStateEvent $event)
    {
        $this->importFinderPlugins();

        // Trigger the onFinderChangeState event.
        $this->getDispatcher()->dispatch('onFinderChangeState', new FinderEvent\AfterChangeStateEvent('onFinderChangeState', [
            'context' => $event->getContext(),
            'subject' => $event->getPks(),
            'value'   => $event->getValue(),
        ]));
    }

    /**
     * Smart Search change category state content method.
     * Method is called when the state of the category to which the
     * content item belongs is changed.
     *
     * @param   Model\AfterCategoryChangeStateEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onCategoryChangeState(Model\AfterCategoryChangeStateEvent $event)
    {
        $this->importFinderPlugins();

        // Trigger the onFinderCategoryChangeState event.
        $this->getDispatcher()->dispatch('onFinderCategoryChangeState', new FinderEvent\AfterCategoryChangeStateEvent('onFinderCategoryChangeState', [
            'context' => $event->getExtension(),
            'subject' => $event->getPks(),
            'value'   => $event->getValue(),
        ]));
    }

    /**
     * A helper method to import finder plugins.
     *
     * @return void
     *
     * @since  5.0.0
     */
    protected function importFinderPlugins()
    {
        if ($this->pluginsImported) {
            return;
        }

        $this->pluginsImported = true;

        PluginHelper::importPlugin('finder', null, true, $this->getDispatcher());
    }
}
