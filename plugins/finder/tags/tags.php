<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.Tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Component\Tags\Site\Helper\RouteHelper;
use Joomla\Database\DatabaseQuery;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Finder adapter for Joomla Tag.
 *
 * @since  3.1
 */
class PlgFinderTags extends Adapter
{
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
     * Method to remove the link information for items that have been deleted.
     *
     * @param   string  $context  The context of the action being performed.
     * @param   Table   $table    A Table object containing the record to be deleted
     *
     * @return  void
     *
     * @since   3.1
     * @throws  Exception on database error.
     */
    public function onFinderAfterDelete($context, $table): void
    {
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
     * @param   string   $context  The context of the content passed to the plugin.
     * @param   Table    $row      A Table object
     * @param   boolean  $isNew    If the content has just been created
     *
     * @return  void
     *
     * @since   3.1
     * @throws  Exception on database error.
     */
    public function onFinderAfterSave($context, $row, $isNew): void
    {
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
     * @param   string   $context  The context of the content passed to the plugin.
     * @param   Table    $row      A Table object
     * @param   boolean  $isNew    If the content is just about to be created
     *
     * @return  boolean  True on success.
     *
     * @since   3.1
     * @throws  Exception on database error.
     */
    public function onFinderBeforeSave($context, $row, $isNew)
    {
        // We only want to handle news feeds here
        if ($context === 'com_tags.tag') {
            // Query the database for the old access level if the item isn't new
            if (!$isNew) {
                $this->checkItemAccess($row);
            }
        }

        return true;
    }

    /**
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
     * @since   3.1
     */
    public function onFinderChangeState($context, $pks, $value)
    {
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
     * @throws  Exception on database error.
     */
    protected function index(Result $item)
    {
        // Check if the extension is enabled
        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        $item->setLanguage();

        // Initialize the item parameters.
        $registry = new Registry($item->params);
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

        // Add the type taxonomy data.
        $item->addTaxonomy('Type', 'Tag');

        // Add the author taxonomy data.
        if (!empty($item->author) || !empty($item->created_by_alias)) {
            $item->addTaxonomy('Author', !empty($item->created_by_alias) ? $item->created_by_alias : $item->author);
        }

        // Add the language taxonomy data.
        $item->addTaxonomy('Language', $item->language);

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
     * @param   mixed  $query  A DatabaseQuery object or null.
     *
     * @return  DatabaseQuery  A database object.
     *
     * @since   3.1
     */
    protected function getListQuery($query = null)
    {
        $db = $this->db;

        // Check if we can use the supplied SQL query.
        $query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true)
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
     * @return  DatabaseQuery  A database object.
     *
     * @since   3.1
     */
    protected function getStateQuery()
    {
        $query = $this->db->getQuery(true);
        $query->select($this->db->quoteName('a.id'))
            ->select($this->db->quoteName('a.' . $this->state_field, 'state') . ', ' . $this->db->quoteName('a.access'))
            ->select('NULL AS cat_state, NULL AS cat_access')
            ->from($this->db->quoteName($this->table, 'a'));

        return $query;
    }

    /**
     * Method to get the query clause for getting items to update by time.
     *
     * @param   string  $time  The modified timestamp.
     *
     * @return  DatabaseQuery  A database object.
     *
     * @since   3.1
     */
    protected function getUpdateQueryByTime($time)
    {
        // Build an SQL query based on the modified time.
        $query = $this->db->getQuery(true)
            ->where('a.date >= ' . $this->db->quote($time));

        return $query;
    }
}
