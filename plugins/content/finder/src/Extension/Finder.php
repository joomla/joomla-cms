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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Smart Search Content Plugin
 *
 * @since  2.5
 */
final class Finder extends CMSPlugin
{
    /**
     * Flag to check whether finder plugins already imported.
     *
     * @var bool
     *
     * @since  5.0.0
     */
    protected $pluginsImported = false;

    /**
     * Smart Search after save content method.
     * Content is passed by reference, but after the save, so no changes will be saved.
     * Method is called right after the content is saved.
     *
     * @param   string  $context  The context of the content passed to the plugin (added in 1.6)
     * @param   object  $article  A \Joomla\CMS\Table\Table\ object
     * @param   bool    $isNew    If the content has just been created
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentAfterSave($context, $article, $isNew): void
    {
        $this->importFinderPlugins();

        // Trigger the onFinderAfterSave event.
        $this->getDispatcher()->dispatch('onFinderAfterSave', new FinderEvent\AfterSaveEvent('onFinderAfterSave', [
            'context' => $context,
            'subject' => $article,
            'isNew'   => $isNew,
        ]));
    }

    /**
     * Smart Search before save content method.
     * Content is passed by reference. Method is called before the content is saved.
     *
     * @param   string  $context  The context of the content passed to the plugin (added in 1.6).
     * @param   object  $article  A \Joomla\CMS\Table\Table\ object.
     * @param   bool    $isNew    If the content is just about to be created.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentBeforeSave($context, $article, $isNew)
    {
        $this->importFinderPlugins();

        // Trigger the onFinderBeforeSave event.
        $this->getDispatcher()->dispatch('onFinderBeforeSave', new FinderEvent\BeforeSaveEvent('onFinderBeforeSave', [
            'context' => $context,
            'subject' => $article,
            'isNew'   => $isNew,
        ]));
    }

    /**
     * Smart Search after delete content method.
     * Content is passed by reference, but after the deletion.
     *
     * @param   string  $context  The context of the content passed to the plugin (added in 1.6).
     * @param   object  $article  A \Joomla\CMS\Table\Table object.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentAfterDelete($context, $article): void
    {
        $this->importFinderPlugins();

        // Trigger the onFinderAfterDelete event.
        $this->getDispatcher()->dispatch('onFinderAfterDelete', new FinderEvent\AfterDeleteEvent('onFinderAfterDelete', [
            'context' => $context,
            'subject' => $article,
        ]));
    }

    /**
     * Smart Search content state change method.
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   string   $context  The context for the content passed to the plugin.
     * @param   array    $pks      A list of primary key ids of the content that has changed state.
     * @param   integer  $value    The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentChangeState($context, $pks, $value)
    {
        $this->importFinderPlugins();

        // Trigger the onFinderChangeState event.
        $this->getDispatcher()->dispatch('onFinderChangeState', new FinderEvent\AfterChangeStateEvent('onFinderChangeState', [
            'context' => $context,
            'subject' => $pks,
            'value'   => $value,
        ]));
    }

    /**
     * Smart Search change category state content method.
     * Method is called when the state of the category to which the
     * content item belongs is changed.
     *
     * @param   string   $extension  The extension whose category has been updated.
     * @param   array    $pks        A list of primary key ids of the content that has changed state.
     * @param   integer  $value      The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onCategoryChangeState($extension, $pks, $value)
    {
        $this->importFinderPlugins();

        // Trigger the onFinderCategoryChangeState event.
        $this->getDispatcher()->dispatch('onFinderCategoryChangeState', new FinderEvent\AfterCategoryChangeStateEvent('onFinderCategoryChangeState', [
            'context' => $extension,
            'subject' => $pks,
            'value'   => $value,
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
