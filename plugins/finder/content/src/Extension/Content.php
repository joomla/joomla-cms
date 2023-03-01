<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Finder.content
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Finder\Content\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseQuery;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Smart Search adapter for com_content.
 *
 * @since  2.5
 */
final class Content extends Adapter
{
    use DatabaseAwareTrait;

    /**
     * The plugin identifier.
     *
     * @var    string
     * @since  2.5
     */
    protected $context = 'Content';

    /**
     * The extension name.
     *
     * @var    string
     * @since  2.5
     */
    protected $extension = 'com_content';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  2.5
     */
    protected $layout = 'article';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  2.5
     */
    protected $type_title = 'Article';

    /**
     * The table name.
     *
     * @var    string
     * @since  2.5
     */
    protected $table = '#__content';

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    protected function setup()
    {
        return true;
    }

    /**
     * Method to update the item link information when the item category is
     * changed. This is fired when the item category is published or unpublished
     * from the list view.
     *
     * @param   string   $extension  The extension whose category has been updated.
     * @param   array    $pks        A list of primary key ids of the content that has changed state.
     * @param   integer  $value      The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderCategoryChangeState($extension, $pks, $value)
    {
        // Make sure we're handling com_content categories.
        if ($extension === 'com_content') {
            $this->categoryStateChange($pks, $value);
        }
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   string  $context  The context of the action being performed.
     * @param   Table   $table    A Table object containing the record to be deleted
     *
     * @return  void
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    public function onFinderAfterDelete($context, $table): void
    {
        if ($context === 'com_content.article') {
            $id = $table->id;
        } elseif ($context === 'com_finder.index') {
            $id = $table->link_id;
        } else {
            return;
        }

        // Remove item from the index.
        $this->remove($id);
    }

    /**
     * Smart Search after save content method.
     * Reindexes the link information for an article that has been saved.
     * It also makes adjustments if the access level of an item or the
     * category to which it belongs has changed.
     *
     * @param   string   $context  The context of the content passed to the plugin.
     * @param   Table    $row      A Table object.
     * @param   boolean  $isNew    True if the content has just been created.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    public function onFinderAfterSave($context, $row, $isNew): void
    {
        // We only want to handle articles here.
        if ($context === 'com_content.article' || $context === 'com_content.form') {
            // Check if the access levels are different.
            if (!$isNew && $this->old_access != $row->access) {
                // Process the change.
                $this->itemAccessChange($row);
            }

            // Reindex the item.
            $this->reindex($row->id);
        }

        // Check for access changes in the category.
        if ($context === 'com_categories.category') {
            // Check if the access levels are different.
            if (!$isNew && $this->old_cataccess != $row->access) {
                $this->categoryAccessChange($row);
            }
        }
    }

    /**
     * Smart Search before content save method.
     * This event is fired before the data is actually saved.
     *
     * @param   string   $context  The context of the content passed to the plugin.
     * @param   Table    $row      A Table object.
     * @param   boolean  $isNew    If the content is just about to be created.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     * @throws  Exception on database error.
     */
    public function onFinderBeforeSave($context, $row, $isNew)
    {
        // We only want to handle articles here.
        if ($context === 'com_content.article' || $context === 'com_content.form') {
            // Query the database for the old access level if the item isn't new.
            if (!$isNew) {
                $this->checkItemAccess($row);
            }
        }

        // Check for access levels from the category.
        if ($context === 'com_categories.category') {
            // Query the database for the old access level if the item isn't new.
            if (!$isNew) {
                $this->checkCategoryAccess($row);
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
     * @param   array    $pks      An array of primary key ids of the content that has changed state.
     * @param   integer  $value    The value of the state that the content has been changed to.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderChangeState($context, $pks, $value)
    {
        // We only want to handle articles here.
        if ($context === 'com_content.article' || $context === 'com_content.form') {
            $this->itemStateChange($pks, $value);
        }

        // Handle when the plugin is disabled.
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
     * @since   2.5
     * @throws  Exception on database error.
     */
    protected function index(Result $item)
    {
        $item->setLanguage();

        // Check if the extension is enabled.
        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        $item->context = 'com_content.article';

        // Initialise the item parameters.
        $registry     = new Registry($item->params);
        $item->params = clone ComponentHelper::getParams('com_content', true);
        $item->params->merge($registry);

        $item->metadata = new Registry($item->metadata);

        // Trigger the onContentPrepare event.
        $item->summary = Helper::prepareContent($item->summary, $item->params, $item);
        $item->body    = Helper::prepareContent($item->body, $item->params, $item);

        // Create a URL as identifier to recognise items again.
        $item->url = $this->getUrl($item->id, $this->extension, $this->layout);

        // Build the necessary route and path information.
        $item->route = RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language);

        // Get the menu title if it exists.
        $title = $this->getItemMenuTitle($item->url);

        // Adjust the title if necessary.
        if (!empty($title) && $this->params->get('use_menu_title', true)) {
            $item->title = $title;
        }

        $images = $item->images ? json_decode($item->images) : false;

        // Add the image.
        if ($images && !empty($images->image_intro)) {
            $item->imageUrl = $images->image_intro;
            $item->imageAlt = $images->image_intro_alt ?? '';
        }

        // Add the meta author.
        $item->metaauthor = $item->metadata->get('author');

        // Add the metadata processing instructions.
        $item->addInstruction(Indexer::META_CONTEXT, 'metakey');
        $item->addInstruction(Indexer::META_CONTEXT, 'metadesc');
        $item->addInstruction(Indexer::META_CONTEXT, 'metaauthor');
        $item->addInstruction(Indexer::META_CONTEXT, 'author');
        $item->addInstruction(Indexer::META_CONTEXT, 'created_by_alias');

        // Translate the state. Articles should only be published if the category is published.
        $item->state = $this->translateState($item->state, $item->cat_state);

        // Add the type taxonomy data.
        $item->addTaxonomy('Type', 'Article');

        // Add the author taxonomy data.
        if (!empty($item->author) || !empty($item->created_by_alias)) {
            $item->addTaxonomy('Author', !empty($item->created_by_alias) ? $item->created_by_alias : $item->author, $item->state);
        }

        // Add the category taxonomy data.
        $categories = $this->getApplication()->bootComponent('com_content')->getCategory(['published' => false, 'access' => false]);
        $category   = $categories->get($item->catid);

        // Category does not exist, stop here
        if (!$category) {
            return;
        }

        $item->addNestedTaxonomy('Category', $category, $this->translateState($category->published), $category->access, $category->language);

        // Add the language taxonomy data.
        $item->addTaxonomy('Language', $item->language);

        // Get content extras.
        Helper::getContentExtras($item);

        // Index the item.
        $this->indexer->index($item);
    }

    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  A DatabaseQuery object or null.
     *
     * @return  DatabaseQuery  A database object.
     *
     * @since   2.5
     */
    protected function getListQuery($query = null)
    {
        $db = $this->getDatabase();

        // Check if we can use the supplied SQL query.
        $query = $query instanceof DatabaseQuery ? $query : $db->getQuery(true)
            ->select('a.id, a.title, a.alias, a.introtext AS summary, a.fulltext AS body')
            ->select('a.images')
            ->select('a.state, a.catid, a.created AS start_date, a.created_by')
            ->select('a.created_by_alias, a.modified, a.modified_by, a.attribs AS params')
            ->select('a.metakey, a.metadesc, a.metadata, a.language, a.access, a.version, a.ordering')
            ->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date')
            ->select('c.title AS category, c.published AS cat_state, c.access AS cat_access');

        // Handle the alias CASE WHEN portion of the query
        $case_when_item_alias = ' CASE WHEN ';
        $case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
        $case_when_item_alias .= ' THEN ';
        $a_id = $query->castAsChar('a.id');
        $case_when_item_alias .= $query->concatenate([$a_id, 'a.alias'], ':');
        $case_when_item_alias .= ' ELSE ';
        $case_when_item_alias .= $a_id . ' END as slug';
        $query->select($case_when_item_alias);

        $case_when_category_alias = ' CASE WHEN ';
        $case_when_category_alias .= $query->charLength('c.alias', '!=', '0');
        $case_when_category_alias .= ' THEN ';
        $c_id = $query->castAsChar('c.id');
        $case_when_category_alias .= $query->concatenate([$c_id, 'c.alias'], ':');
        $case_when_category_alias .= ' ELSE ';
        $case_when_category_alias .= $c_id . ' END as catslug';
        $query->select($case_when_category_alias)

            ->select('u.name AS author')
            ->from('#__content AS a')
            ->join('LEFT', '#__categories AS c ON c.id = a.catid')
            ->join('LEFT', '#__users AS u ON u.id = a.created_by');

        return $query;
    }
}
