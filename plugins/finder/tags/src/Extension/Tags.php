<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Finder\Tags\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Finder as FinderEvent;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Component\Tags\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\QueryInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Finder adapter for Joomla Tag.
 *
 * @since  3.1
 */
final class Tags extends Adapter implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * The plugin identifier.
     *
     * @var    string
     * @since  3.1
     */
    protected $context = 'Tags';

    /**
     * The extension name.
     *
     * @var    string
     * @since  3.1
     */
    protected $extension = 'com_tags';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  3.1
     */
    protected $layout = 'tag';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  3.1
     */
    protected $type_title = 'Tag';

    /**
     * The table name.
     *
     * @var    string
     * @since  3.1
     */
    protected $table = '#__tags';

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * The field the published state is stored in.
     *
     * @var    string
     * @since  3.1
     */
    protected $state_field = 'published';

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.2.0
     */
    public static function getSubscribedEvents(): array
    {
        return array_merge(parent::getSubscribedEvents(), [
            'onFinderAfterDelete' => 'onFinderAfterDelete',
            'onFinderAfterSave'   => 'onFinderAfterSave',
            'onFinderBeforeSave'  => 'onFinderBeforeSave',
            'onFinderChangeState' => 'onFinderChangeState',
        ]);
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   FinderEvent\AfterDeleteEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   3.1
     * @throws  \Exception on database error.
     */
    public function onFinderAfterDelete(FinderEvent\AfterDeleteEvent $event): void
    {
        $context = $event->getContext();
        $table   = $event->getItem();

        if ($context === 'com_tags.tag') {
            $id = $table->id;
        } elseif ($context === 'com_finder.index') {
            $id = $table->link_id;
        } else {
            return;
        }

        // Remove the items.
        $this->remove($id);
    }

    /**
     * Method to determine if the access level of an item changed.
     *
     * @param   FinderEvent\AfterSaveEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   3.1
     * @throws  \Exception on database error.
     */
    public function onFinderAfterSave(FinderEvent\AfterSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        // We only want to handle tags here.
        if ($context === 'com_tags.tag') {
            // Check if the access levels are different
            if (!$isNew && $this->old_access != $row->access) {
                // Process the change.
                $this->itemAccessChange($row);
            }

            // Reindex the item
            $this->reindex($row->id);
        }
    }

    /**
     * Method to reindex the link information for an item that has been saved.
     * This event is fired before the data is actually saved so we are going
     * to queue the item to be indexed later.
     *
     * @param   FinderEvent\BeforeSaveEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   3.1
     * @throws  \Exception on database error.
     */
    public function onFinderBeforeSave(FinderEvent\BeforeSaveEvent $event): void
    {
        $context = $event->getContext();
        $row     = $event->getItem();
        $isNew   = $event->getIsNew();

        // We only want to handle news feeds here
        if ($context === 'com_tags.tag') {
            // Query the database for the old access level if the item isn't new
            if (!$isNew) {
                $this->checkItemAccess($row);
            }
        }
    }

    /**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   FinderEvent\AfterChangeStateEvent   $event  The event instance.
     *
     * @return  void
     *
     * @since   3.1
     */
    public function onFinderChangeState(FinderEvent\AfterChangeStateEvent $event): void
    {
        $context = $event->getContext();
        $pks     = $event->getPks();
        $value   = $event->getValue();

        // We only want to handle tags here
        if ($context === 'com_tags.tag') {
            $this->itemStateChange($pks, $value);
        }

        // Handle when the plugin is disabled
        if ($context === 'com_plugins.plugin' && $value === 0) {
            $this->pluginDisable($pks);
        }
    }

    /**
     * Method to index an item. The item must be a Result object.
     *
     * @param   Result  $item  The item to index as a Result object.
     *
     * @return  void
     *
     * @since   3.1
     * @throws  \Exception on database error.
     */
    protected function index(Result $item)
    {
        // Check if the extension is enabled
        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        $item->setLanguage();

        // Initialize the item parameters.
        $registry     = new Registry($item->params);
        $item->params = clone ComponentHelper::getParams('com_tags', true);
        $item->params->merge($registry);

        $item->metadata = new Registry($item->metadata);

        // Create a URL as identifier to recognise items again.
        $item->url = $this->getUrl($item->id, $this->extension, $this->layout);

        // Build the necessary route and path information.
        $item->route = RouteHelper::getComponentTagRoute($item->slug, $item->language);

        // Get the menu title if it exists.
        $title = $this->getItemMenuTitle($item->url);

        // Adjust the title if necessary.
        if (!empty($title) && $this->params->get('use_menu_title', true)) {
            $item->title = $title;
        }

        // Add the meta author.
        $item->metaauthor = $item->metadata->get('author');

        // Handle the link to the metadata.
        $item->addInstruction(Indexer::META_CONTEXT, 'link');
        $item->addInstruction(Indexer::META_CONTEXT, 'metakey');
        $item->addInstruction(Indexer::META_CONTEXT, 'metadesc');
        $item->addInstruction(Indexer::META_CONTEXT, 'metaauthor');
        $item->addInstruction(Indexer::META_CONTEXT, 'author');
        $item->addInstruction(Indexer::META_CONTEXT, 'created_by_alias');

        // Get taxonomies to display
        $taxonomies = $this->params->get('taxonomies', ['type', 'author', 'language']);

        // Add the type taxonomy data.
        if (\in_array('type', $taxonomies)) {
            $item->addTaxonomy('Type', 'Tag');
        }

        // Add the author taxonomy data.
        if (\in_array('author', $taxonomies) && (!empty($item->author) || !empty($item->created_by_alias))) {
            $item->addTaxonomy('Author', !empty($item->created_by_alias) ? $item->created_by_alias : $item->author);
        }

        // Add the language taxonomy data.
        if (\in_array('language', $taxonomies)) {
            $item->addTaxonomy('Language', $item->language);
        }

        // Get content extras.
        Helper::getContentExtras($item);

        // Index the item.
        $this->indexer->index($item);
    }

    /**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     *
     * @since   3.1
     */
    protected function setup()
    {
        return true;
    }

    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  An object implementing QueryInterface or null.
     *
     * @return  QueryInterface  A database object.
     *
     * @since   3.1
     */
    protected function getListQuery($query = null)
    {
        $db = $this->getDatabase();

        // Check if we can use the supplied SQL query.
        $query = $query instanceof QueryInterface ? $query : $db->getQuery(true)
            ->select('a.id, a.title, a.alias, a.description AS summary')
            ->select('a.created_time AS start_date, a.created_user_id AS created_by')
            ->select('a.metakey, a.metadesc, a.metadata, a.language, a.access')
            ->select('a.modified_time AS modified, a.modified_user_id AS modified_by')
            ->select('a.published AS state, a.access, a.created_time AS start_date, a.params');

        // Handle the alias CASE WHEN portion of the query
        $case_when_item_alias = ' CASE WHEN ';
        $case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
        $case_when_item_alias .= ' THEN ';
        $a_id = $query->castAsChar('a.id');
        $case_when_item_alias .= $query->concatenate([$a_id, 'a.alias'], ':');
        $case_when_item_alias .= ' ELSE ';
        $case_when_item_alias .= $a_id . ' END as slug';
        $query->select($case_when_item_alias)
            ->from('#__tags AS a');

        // Join the #__users table
        $query->select('u.name AS author')
            ->join('LEFT', '#__users AS u ON u.id = a.created_user_id');

        // Exclude the ROOT item
        $query->where($db->quoteName('a.id') . ' > 1');

        return $query;
    }

    /**
     * Method to get a SQL query to load the published and access states for the given tag.
     *
     * @return  QueryInterface  A database object.
     *
     * @since   3.1
     */
    protected function getStateQuery()
    {
        $query = $this->getDatabase()->getQuery(true);
        $query->select($this->getDatabase()->quoteName('a.id'))
            ->select($this->getDatabase()->quoteName('a.' . $this->state_field, 'state') . ', ' . $this->getDatabase()->quoteName('a.access'))
            ->select('NULL AS cat_state, NULL AS cat_access')
            ->from($this->getDatabase()->quoteName($this->table, 'a'));

        return $query;
    }

    /**
     * Method to get the query clause for getting items to update by time.
     *
     * @param   string  $time  The modified timestamp.
     *
     * @return  QueryInterface  A database object.
     *
     * @since   3.1
     */
    protected function getUpdateQueryByTime($time)
    {
        // Build an SQL query based on the modified time.
        $query = $this->getDatabase()->getQuery(true)
            ->where('a.date >= ' . $this->getDatabase()->quote($time));

        return $query;
    }
}
