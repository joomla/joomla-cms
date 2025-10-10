<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Behaviour.taggable
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Behaviour\Taggable\Extension;

use Joomla\CMS\Event\Model\BeforeBatchEvent;
use Joomla\CMS\Event\Table\AfterLoadEvent;
use Joomla\CMS\Event\Table\AfterResetEvent;
use Joomla\CMS\Event\Table\AfterStoreEvent;
use Joomla\CMS\Event\Table\BeforeDeleteEvent;
use Joomla\CMS\Event\Table\BeforeStoreEvent;
use Joomla\CMS\Event\Table\ObjectCreateEvent;
use Joomla\CMS\Event\Table\SetNewTagsEvent;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Implements the Taggable behaviour which allows extensions to automatically support tags for their content items.
 *
 * This plugin supersedes JHelperObserverTags.
 *
 * @since  4.0.0
 */
final class Taggable extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTableObjectCreate' => 'onTableObjectCreate',
            'onTableBeforeStore'  => 'onTableBeforeStore',
            'onTableAfterStore'   => 'onTableAfterStore',
            'onTableBeforeDelete' => 'onTableBeforeDelete',
            'onTableSetNewTags'   => 'onTableSetNewTags',
            'onTableAfterReset'   => 'onTableAfterReset',
            'onTableAfterLoad'    => 'onTableAfterLoad',
            'onBeforeBatch'       => 'onBeforeBatch',
        ];
    }

    /**
     * Runs when a new table object is being created
     *
     * @param   ObjectCreateEvent  $event  The event to handle
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onTableObjectCreate(ObjectCreateEvent $event)
    {
        // Extract arguments
        /** @var TableInterface $table */
        $table = $event['subject'];

        // If the tags table doesn't implement the interface bail
        if (!($table instanceof TaggableTableInterface)) {
            return;
        }

        // If the table already has a tags helper we have nothing to do
        if (!\is_null($table->getTagsHelper())) {
            return;
        }

        $tagsHelper            = new TagsHelper();
        $tagsHelper->typeAlias = $table->typeAlias;
        $table->setTagsHelper($tagsHelper);

        // This is required because getTagIds overrides the tags property of the Tags Helper.
        $cloneHelper = clone $table->getTagsHelper();
        $tagIds      = $cloneHelper->getTagIds($table->getId(), $table->getTypeAlias());

        if (!empty($tagIds)) {
            $table->getTagsHelper()->tags = explode(',', $tagIds);
        }
    }

    /**
     * Pre-processor for $table->store($updateNulls)
     *
     * @param   BeforeStoreEvent  $event  The event to handle
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onTableBeforeStore(BeforeStoreEvent $event)
    {
        // Extract arguments
        /** @var TableInterface $table */
        $table = $event['subject'];

        // If the tags table doesn't implement the interface bail
        if (!($table instanceof TaggableTableInterface)) {
            return;
        }

        // If the table doesn't have a tags helper we can't proceed
        if (\is_null($table->getTagsHelper())) {
            return;
        }

        /** @var TagsHelper $tagsHelper */
        $tagsHelper            = $table->getTagsHelper();
        $tagsHelper->typeAlias = $table->getTypeAlias();

        $newTags = $table->newTags ?? [];

        if (empty($newTags)) {
            $tagsHelper->preStoreProcess($table);
        } else {
            $tagsHelper->preStoreProcess($table, (array) $newTags);
        }
    }

    /**
     * Post-processor for $table->store($updateNulls)
     *
     * @param   AfterStoreEvent  $event  The event to handle
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onTableAfterStore(AfterStoreEvent $event)
    {
        // Extract arguments
        /** @var TableInterface $table */
        $table  = $event['subject'];
        $result = $event['result'];

        if (!$result) {
            return;
        }

        if (!\is_object($table) || !($table instanceof TaggableTableInterface)) {
            return;
        }

        // If the table doesn't have a tags helper we can't proceed
        if (\is_null($table->getTagsHelper())) {
            return;
        }

        // Get the Tags helper and assign the parsed alias
        /** @var TagsHelper $tagsHelper */
        $tagsHelper            = $table->getTagsHelper();
        $tagsHelper->typeAlias = $table->getTypeAlias();

        $newTags = $table->newTags ?? [];

        if (empty($newTags)) {
            $result = $tagsHelper->postStore($table);
        } else {
            if (\is_string($newTags) && (str_contains($newTags, ','))) {
                $newTags = explode(',', $newTags);
            } elseif (!\is_array($newTags)) {
                $newTags = (array) $newTags;
            }

            $result = $tagsHelper->postStore($table, $newTags);
        }
    }

    /**
     * Pre-processor for $table->delete($pk)
     *
     * @param   BeforeDeleteEvent  $event  The event to handle
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onTableBeforeDelete(BeforeDeleteEvent $event)
    {
        // Extract arguments
        /** @var TableInterface $table */
        $table = $event['subject'];
        $pk    = $event['pk'];

        // If the tags table doesn't implement the interface bail
        if (!($table instanceof TaggableTableInterface)) {
            return;
        }

        // If the table doesn't have a tags helper we can't proceed
        if (\is_null($table->getTagsHelper())) {
            return;
        }

        // Get the Tags helper and assign the parsed alias
        $table->getTagsHelper()->typeAlias = $table->getTypeAlias();

        $table->getTagsHelper()->deleteTagData($table, $pk);
    }

    /**
     * Handles the tag setting in $table->batchTag($value, $pks, $contexts)
     *
     * @param   SetNewTagsEvent  $event  The event to handle
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onTableSetNewTags(SetNewTagsEvent $event)
    {
        // Extract arguments
        /** @var TableInterface $table */
        $table       = $event['subject'];
        $newTags     = $event['newTags'];
        $replaceTags = $event['replaceTags'];
        $removeTags  = $event['removeTags'];

        // If the tags table doesn't implement the interface bail
        if (!($table instanceof TaggableTableInterface)) {
            return;
        }

        // If the table doesn't have a tags helper we can't proceed
        if (\is_null($table->getTagsHelper())) {
            return;
        }

        // Get the Tags helper and assign the parsed alias
        /** @var TagsHelper $tagsHelper */
        $tagsHelper            = $table->getTagsHelper();
        $tagsHelper->typeAlias = $table->getTypeAlias();

        if (!$tagsHelper->postStore($table, $newTags, $replaceTags, $removeTags)) {
            throw new \RuntimeException($table->getError());
        }
    }

    /**
     * Runs when an existing table object is reset
     *
     * @param   AfterResetEvent  $event  The event to handle
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onTableAfterReset(AfterResetEvent $event)
    {
        // Extract arguments
        /** @var TableInterface $table */
        $table = $event['subject'];

        // If the tags table doesn't implement the interface bail
        if (!($table instanceof TaggableTableInterface)) {
            return;
        }

        // Parse the type alias
        $tagsHelper            = new TagsHelper();
        $tagsHelper->typeAlias = $table->getTypeAlias();
        $table->setTagsHelper($tagsHelper);
    }

    /**
     * Runs when an existing table object has been loaded
     *
     * @param   AfterLoadEvent  $event  The event to handle
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onTableAfterLoad(AfterLoadEvent $event)
    {
        // Extract arguments
        /** @var TableInterface $table */
        $table = $event['subject'];

        // If the tags table doesn't implement the interface bail
        if (!($table instanceof TaggableTableInterface)) {
            return;
        }

        // If the table doesn't have a tags helper we can't proceed
        if (\is_null($table->getTagsHelper())) {
            return;
        }

        // This is required because getTagIds overrides the tags property of the Tags Helper.
        $cloneHelper = clone $table->getTagsHelper();
        $tagIds      = $cloneHelper->getTagIds($table->getId(), $table->getTypeAlias());

        if (!empty($tagIds)) {
            $table->getTagsHelper()->tags = explode(',', $tagIds);
        }
    }

    /**
     * Runs when an existing table object has been loaded
     *
     * @param   BeforeBatchEvent  $event  The event to handle
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onBeforeBatch(BeforeBatchEvent $event)
    {
        /** @var TableInterface $sourceTable */
        $sourceTable = $event['src'];

        if (!($sourceTable instanceof TaggableTableInterface)) {
            return;
        }

        if ($event['type'] === 'copy') {
            $sourceTable->newTags = $sourceTable->getTagsHelper()->tags;
        } else {
            /**
             * All other batch actions we don't want the tags to be modified so clear the helper - that way no actions
             * will be performed on store
             */
            $sourceTable->clearTagsHelper();
        }
    }
}
