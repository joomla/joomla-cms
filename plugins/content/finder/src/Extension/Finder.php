<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\Finder\Extension;

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
     * Smart Search after save content method.
     * Content is passed by reference, but after the save, so no changes will be saved.
     * Method is called right after the content is saved.
     *
     * @param   string  $context  The context of the content passed to the plugin (added in 1.6)
     * @param   object  $article  A JTableContent object
     * @param   bool    $isNew    If the content has just been created
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentAfterSave($context, $article, $isNew): void
    {
        PluginHelper::importPlugin('finder');

        // Trigger the onFinderAfterSave event.
        $this->getApplication()->triggerEvent('onFinderAfterSave', [$context, $article, $isNew]);
    }

    /**
     * Smart Search before save content method.
     * Content is passed by reference. Method is called before the content is saved.
     *
     * @param   string  $context  The context of the content passed to the plugin (added in 1.6).
     * @param   object  $article  A JTableContent object.
     * @param   bool    $isNew    If the content is just about to be created.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentBeforeSave($context, $article, $isNew)
    {
        PluginHelper::importPlugin('finder');

        // Trigger the onFinderBeforeSave event.
        $this->getApplication()->triggerEvent('onFinderBeforeSave', [$context, $article, $isNew]);
    }

    /**
     * Smart Search after delete content method.
     * Content is passed by reference, but after the deletion.
     *
     * @param   string  $context  The context of the content passed to the plugin (added in 1.6).
     * @param   object  $article  A JTableContent object.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onContentAfterDelete($context, $article): void
    {
        PluginHelper::importPlugin('finder');

        // Trigger the onFinderAfterDelete event.
        $this->getApplication()->triggerEvent('onFinderAfterDelete', [$context, $article]);
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
        PluginHelper::importPlugin('finder');

        // Trigger the onFinderChangeState event.
        $this->getApplication()->triggerEvent('onFinderChangeState', [$context, $pks, $value]);
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
        PluginHelper::importPlugin('finder');

        // Trigger the onFinderCategoryChangeState event.
        $this->getApplication()->triggerEvent('onFinderCategoryChangeState', [$extension, $pks, $value]);
    }
}
