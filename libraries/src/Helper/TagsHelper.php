<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\CoreContent;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\UCM\UCMContent;
use Joomla\CMS\UCM\UCMType;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Tags helper class, provides methods to perform various tasks relevant
 * tagging of content.
 *
 * @since  3.1
 */
class TagsHelper extends CMSHelper
{
    /**
     * Helper object for storing and deleting tag information.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $tagsChanged = false;

    /**
     * Whether up replace all tags or just add tags
     *
     * @var    boolean
     * @since  3.1
     */
    protected $replaceTags = false;

    /**
     * Alias for querying mapping and content type table.
     *
     * @var    string
     * @since  3.1
     */
    public $typeAlias;

    /**
     * Array of item tags.
     *
     * @var    array
     * @since  3.1
     */
    public $itemTags;

    /**
     * The tags as comma separated string or array.
     *
     * @var    mixed
     * @since  4.3.0
     */
    public $tags;

    /**
     * The new tags as comma separated string or array.
     *
     * @var    mixed
     * @since  4.3.0
     */
    public $newTags;

    /**
     * The old tags as comma separated string or array.
     *
     * @var    mixed
     * @since  4.3.0
     */
    public $oldTags;

    /**
     * Method to add tag rows to mapping table.
     *
     * @param   integer         $ucmId  ID of the #__ucm_content item being tagged
     * @param   TableInterface  $table  Table object being tagged
     * @param   array           $tags   Array of tags to be applied.
     *
     * @return  boolean  true on success, otherwise false.
     *
     * @since   3.1
     */
    public function addTagMapping($ucmId, TableInterface $table, $tags = [])
    {
        $db     = $table->getDbo();
        $key    = $table->getKeyName();
        $item   = $table->$key;
        $ucm    = new UCMType($this->typeAlias, $db);
        $typeId = $ucm->getTypeId();

        // Insert the new tag maps
        if (strpos('#', implode(',', $tags)) === false) {
            $tags = self::createTagsFromField($tags);
        }

        // Prevent saving duplicate tags
        $tags = array_values(array_unique($tags));

        if (!$tags) {
            return true;
        }

        $query = $db->getQuery(true);
        $query->insert('#__contentitem_tag_map');
        $query->columns(
            [
                $db->quoteName('core_content_id'),
                $db->quoteName('content_item_id'),
                $db->quoteName('tag_id'),
                $db->quoteName('type_id'),
                $db->quoteName('type_alias'),
                $db->quoteName('tag_date'),
            ]
        );

        foreach ($tags as $tag) {
            $query->values(
                implode(
                    ',',
                    array_merge(
                        $query->bindArray([(int) $ucmId, (int) $item, (int) $tag, (int) $typeId]),
                        $query->bindArray([$this->typeAlias], ParameterType::STRING),
                        [$query->currentTimestamp()]
                    )
                )
            );
        }

        $db->setQuery($query);

        return (bool) $db->execute();
    }

    /**
     * Function that converts tags paths into paths of names
     *
     * @param   array  $tags  Array of tags
     *
     * @return  array
     *
     * @since   3.1
     */
    public static function convertPathsToNames($tags)
    {
        // We will replace path aliases with tag names
        if ($tags) {
            // Create an array with all the aliases of the results
            $aliases = [];

            foreach ($tags as $tag) {
                if (!empty($tag->path)) {
                    if ($pathParts = explode('/', $tag->path)) {
                        $aliases = array_merge($aliases, $pathParts);
                    }
                }
            }

            // Get the aliases titles in one single query and map the results
            if ($aliases) {
                // Remove duplicates
                $aliases = array_values(array_unique($aliases));

                $db = Factory::getDbo();

                $query = $db->getQuery(true)
                    ->select(
                        [
                            $db->quoteName('alias'),
                            $db->quoteName('title'),
                        ]
                    )
                    ->from($db->quoteName('#__tags'))
                    ->whereIn($db->quoteName('alias'), $aliases, ParameterType::STRING);
                $db->setQuery($query);

                try {
                    $aliasesMapper = $db->loadAssocList('alias');
                } catch (\RuntimeException $e) {
                    return false;
                }

                // Rebuild the items path
                if ($aliasesMapper) {
                    foreach ($tags as $tag) {
                        $namesPath = [];

                        if (!empty($tag->path)) {
                            if ($pathParts = explode('/', $tag->path)) {
                                foreach ($pathParts as $alias) {
                                    if (isset($aliasesMapper[$alias])) {
                                        $namesPath[] = $aliasesMapper[$alias]['title'];
                                    } else {
                                        $namesPath[] = $alias;
                                    }
                                }

                                $tag->text = implode('/', $namesPath);
                            }
                        }
                    }
                }
            }
        }

        return $tags;
    }

    /**
     * Create any new tags by looking for #new# in the strings
     *
     * @param   array  $tags  Tags text array from the field
     *
     * @return  mixed   If successful, metadata with new tag titles replaced by tag ids. Otherwise false.
     *
     * @since   3.1
     */
    public function createTagsFromField($tags)
    {
        if (empty($tags) || $tags[0] == '') {
            return;
        } else {
            // We will use the tags table to store them
            $tagTable  = Factory::getApplication()->bootComponent('com_tags')->getMVCFactory()->createTable('Tag', 'Administrator');
            $newTags   = [];
            $canCreate = Factory::getUser()->authorise('core.create', 'com_tags');

            foreach ($tags as $key => $tag) {
                // User is not allowed to create tags, so don't create.
                if (!$canCreate && strpos($tag, '#new#') !== false) {
                    continue;
                }

                // Remove the #new# prefix that identifies new tags
                $tagText = str_replace('#new#', '', $tag);

                if ($tagText === $tag) {
                    $newTags[] = (int) $tag;
                } else {
                    // Clear old data if exist
                    $tagTable->reset();

                    // Try to load the selected tag
                    if ($tagTable->load(['title' => $tagText])) {
                        $newTags[] = (int) $tagTable->id;
                    } else {
                        // Prepare tag data
                        $tagTable->id          = 0;
                        $tagTable->title       = $tagText;
                        $tagTable->published   = 1;
                        $tagTable->description = '';

                        // $tagTable->language = property_exists ($item, 'language') ? $item->language : '*';
                        $tagTable->language = '*';
                        $tagTable->access   = 1;

                        // Make this item a child of the root tag
                        $tagTable->setLocation($tagTable->getRootId(), 'last-child');

                        // Try to store tag
                        if ($tagTable->check()) {
                            // Assign the alias as path (autogenerated tags have always level 1)
                            $tagTable->path = $tagTable->alias;

                            if ($tagTable->store()) {
                                $newTags[] = (int) $tagTable->id;
                            }
                        }
                    }
                }
            }

            // At this point $tags is an array of all tag ids
            $this->tags = $newTags;
            $result     = $newTags;
        }

        return $result;
    }

    /**
     * Method to delete the tag mappings and #__ucm_content record for for an item
     *
     * @param   TableInterface  $table          Table object of content table where delete occurred
     * @param   integer|array   $contentItemId  ID of the content item. Or an array of key/value pairs with array key
     *                                          being a primary key name and value being the content item ID. Note
     *                                          multiple primary keys are not supported
     *
     * @return  boolean  true on success, false on failure
     *
     * @since   3.1
     * @throws  \InvalidArgumentException
     */
    public function deleteTagData(TableInterface $table, $contentItemId)
    {
        $key = $table->getKeyName();

        if (!\is_array($contentItemId)) {
            $contentItemId = [$key => $contentItemId];
        }

        // If we have multiple items for the content item primary key we currently don't support this so
        // throw an InvalidArgumentException for now
        if (\count($contentItemId) != 1) {
            throw new \InvalidArgumentException('Multiple primary keys are not supported as a content item id');
        }

        $result = $this->unTagItem($contentItemId[$key], $table);

        /** @var  CoreContent $ucmContentTable */
        $ucmContentTable = Table::getInstance('Corecontent');

        return $result && $ucmContentTable->deleteByContentId($contentItemId[$key], $this->typeAlias);
    }

    /**
     * Method to get a list of tags for an item, optionally with the tag data.
     *
     * @param   string   $contentType  Content type alias. Dot separated.
     * @param   integer  $id           Id of the item to retrieve tags for.
     * @param   boolean  $getTagData   If true, data from the tags table will be included, defaults to true.
     *
     * @return  array    Array of of tag objects
     *
     * @since   3.1
     */
    public function getItemTags($contentType, $id, $getTagData = true)
    {
        // Cast as integer until method is typehinted.
        $id = (int) $id;

        // Initialize some variables.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('m.tag_id'))
            ->from($db->quoteName('#__contentitem_tag_map', 'm'))
            ->where(
                [
                    $db->quoteName('m.type_alias') . ' = :contentType',
                    $db->quoteName('m.content_item_id') . ' = :id',
                    $db->quoteName('t.published') . ' = 1',
                ]
            )
            ->bind(':contentType', $contentType)
            ->bind(':id', $id, ParameterType::INTEGER);

        $user   = Factory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        $query->whereIn($db->quoteName('t.access'), $groups);

        // Optionally filter on language
        $language = ComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

        if ($language !== 'all') {
            if ($language === 'current_language') {
                $language = $this->getCurrentLanguage();
            }

            $query->whereIn($db->quoteName('language'), [$language, '*'], ParameterType::STRING);
        }

        if ($getTagData) {
            $query->select($db->quoteName('t') . '.*');
        }

        $query->join('INNER', $db->quoteName('#__tags', 't'), $db->quoteName('m.tag_id') . ' = ' . $db->quoteName('t.id'));

        $db->setQuery($query);
        $this->itemTags = $db->loadObjectList();

        return $this->itemTags;
    }

    /**
     * Method to get a list of tags for multiple items, optionally with the tag data.
     *
     * @param   string   $contentType  Content type alias. Dot separated.
     * @param   array    $ids          Id of the item to retrieve tags for.
     * @param   boolean  $getTagData   If true, data from the tags table will be included, defaults to true.
     *
     * @return  array    Array of of tag objects grouped by Id.
     *
     * @since   4.2.0
     */
    public function getMultipleItemTags($contentType, array $ids, $getTagData = true)
    {
        $data = [];

        $ids = array_map('intval', $ids);

        /** @var DatabaseDriver $db */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true)
            ->select($db->quoteName(['m.tag_id', 'm.content_item_id']))
            ->from($db->quoteName('#__contentitem_tag_map', 'm'))
            ->where(
                [
                    $db->quoteName('m.type_alias') . ' = :contentType',
                    $db->quoteName('t.published') . ' = 1',
                ]
            )
            ->whereIn($db->quoteName('m.content_item_id'), $ids)
            ->bind(':contentType', $contentType);

        $query->join('INNER', $db->quoteName('#__tags', 't'), $db->quoteName('m.tag_id') . ' = ' . $db->quoteName('t.id'));

        $groups = Factory::getUser()->getAuthorisedViewLevels();

        $query->whereIn($db->quoteName('t.access'), $groups);

        // Optionally filter on language
        $language = ComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

        if ($language !== 'all') {
            if ($language === 'current_language') {
                $language = $this->getCurrentLanguage();
            }

            $query->whereIn($db->quoteName('language'), [$language, '*'], ParameterType::STRING);
        }

        if ($getTagData) {
            $query->select($db->quoteName('t') . '.*');
        }

        $db->setQuery($query);

        $rows = $db->loadObjectList();

        // Group data by item Id.
        foreach ($rows as $row) {
            $data[$row->content_item_id][] = $row;
            unset($row->content_item_id);
        }

        return $data;
    }

    /**
     * Method to get a list of tags for a given item.
     * Normally used for displaying a list of tags within a layout
     *
     * @param   mixed   $ids     The id or array of ids (primary key) of the item to be tagged.
     * @param   string  $prefix  Dot separated string with the option and view to be used for a url.
     *
     * @return  string   Comma separated list of tag Ids.
     *
     * @since   3.1
     */
    public function getTagIds($ids, $prefix)
    {
        if (empty($ids)) {
            return;
        }

        /**
         * Ids possible formats:
         * ---------------------
         *  $id = 1;
         *  $id = array(1,2);
         *  $id = array('1,3,4,19');
         *  $id = '1,3';
         */
        $ids = (array) $ids;
        $ids = implode(',', $ids);
        $ids = explode(',', $ids);
        $ids = ArrayHelper::toInteger($ids);

        $db = Factory::getDbo();

        // Load the tags.
        $query = $db->getQuery(true)
            ->select($db->quoteName('t.id'))
            ->from($db->quoteName('#__tags', 't'))
            ->join('INNER', $db->quoteName('#__contentitem_tag_map', 'm'), $db->quoteName('m.tag_id') . ' = ' . $db->quoteName('t.id'))
            ->where($db->quoteName('m.type_alias') . ' = :prefix')
            ->whereIn($db->quoteName('m.content_item_id'), $ids)
            ->bind(':prefix', $prefix);

        $db->setQuery($query);

        // Add the tags to the content data.
        $tagsList   = $db->loadColumn();
        $this->tags = implode(',', $tagsList);

        return $this->tags;
    }

    /**
     * Method to get a query to retrieve a detailed list of items for a tag.
     *
     * @param   mixed    $tagId            Tag or array of tags to be matched
     * @param   mixed    $typesr           Null, type or array of type aliases for content types to be included in the results
     * @param   boolean  $includeChildren  True to include the results from child tags
     * @param   string   $orderByOption    Column to order the results by
     * @param   string   $orderDir         Direction to sort the results in
     * @param   boolean  $anyOrAll         True to include items matching at least one tag, false to include
     *                                     items all tags in the array.
     * @param   string   $languageFilter   Optional filter on language. Options are 'all', 'current' or any string.
     * @param   string   $stateFilter      Optional filtering on publication state, defaults to published or unpublished.
     *
     * @return  \Joomla\Database\DatabaseQuery  Query to retrieve a list of tags
     *
     * @since   3.1
     */
    public function getTagItemsQuery(
        $tagId,
        $typesr = null,
        $includeChildren = false,
        $orderByOption = 'c.core_title',
        $orderDir = 'ASC',
        $anyOrAll = true,
        $languageFilter = 'all',
        $stateFilter = '0,1'
    ) {
        // Create a new query object.
        $db       = Factory::getDbo();
        $query    = $db->getQuery(true);
        $user     = Factory::getUser();
        $nullDate = $db->getNullDate();
        $nowDate  = Factory::getDate()->toSql();

        // Force ids to array and sanitize
        $tagIds = (array) $tagId;
        $tagIds = implode(',', $tagIds);
        $tagIds = explode(',', $tagIds);
        $tagIds = ArrayHelper::toInteger($tagIds);

        $ntagsr = \count($tagIds);

        // If we want to include children we have to adjust the list of tags.
        // We do not search child tags when the match all option is selected.
        if ($includeChildren) {
            $tagTreeArray = [];

            foreach ($tagIds as $tag) {
                $this->getTagTreeArray($tag, $tagTreeArray);
            }

            $tagIds = array_values(array_unique(array_merge($tagIds, $tagTreeArray)));
        }

        // Sanitize filter states
        $stateFilters = explode(',', $stateFilter);
        $stateFilters = ArrayHelper::toInteger($stateFilters);

        // M is the mapping table. C is the core_content table. Ct is the content_types table.
        $query->select(
            [
                $db->quoteName('m.type_alias'),
                $db->quoteName('m.content_item_id'),
                $db->quoteName('m.core_content_id'),
                'COUNT(' . $db->quoteName('m.tag_id') . ') AS ' . $db->quoteName('match_count'),
                'MAX(' . $db->quoteName('m.tag_date') . ') AS ' . $db->quoteName('tag_date'),
                'MAX(' . $db->quoteName('c.core_title') . ') AS ' . $db->quoteName('core_title'),
                'MAX(' . $db->quoteName('c.core_params') . ') AS ' . $db->quoteName('core_params'),
                'MAX(' . $db->quoteName('c.core_alias') . ') AS ' . $db->quoteName('core_alias'),
                'MAX(' . $db->quoteName('c.core_body') . ') AS ' . $db->quoteName('core_body'),
                'MAX(' . $db->quoteName('c.core_state') . ') AS ' . $db->quoteName('core_state'),
                'MAX(' . $db->quoteName('c.core_access') . ') AS ' . $db->quoteName('core_access'),
                'MAX(' . $db->quoteName('c.core_metadata') . ') AS ' . $db->quoteName('core_metadata'),
                'MAX(' . $db->quoteName('c.core_created_user_id') . ') AS ' . $db->quoteName('core_created_user_id'),
                'MAX(' . $db->quoteName('c.core_created_by_alias') . ') AS' . $db->quoteName('core_created_by_alias'),
                'MAX(' . $db->quoteName('c.core_created_time') . ') AS ' . $db->quoteName('core_created_time'),
                'MAX(' . $db->quoteName('c.core_images') . ') AS ' . $db->quoteName('core_images'),
                'CASE WHEN ' . $db->quoteName('c.core_modified_time') . ' = :nullDate THEN ' . $db->quoteName('c.core_created_time')
                . ' ELSE ' . $db->quoteName('c.core_modified_time') . ' END AS ' . $db->quoteName('core_modified_time'),
                'MAX(' . $db->quoteName('c.core_language') . ') AS ' . $db->quoteName('core_language'),
                'MAX(' . $db->quoteName('c.core_catid') . ') AS ' . $db->quoteName('core_catid'),
                'MAX(' . $db->quoteName('c.core_publish_up') . ') AS ' . $db->quoteName('core_publish_up'),
                'MAX(' . $db->quoteName('c.core_publish_down') . ') AS ' . $db->quoteName('core_publish_down'),
                'MAX(' . $db->quoteName('ct.type_title') . ') AS ' . $db->quoteName('content_type_title'),
                'MAX(' . $db->quoteName('ct.router') . ') AS ' . $db->quoteName('router'),
                'CASE WHEN ' . $db->quoteName('c.core_created_by_alias') . ' > ' . $db->quote(' ')
                . ' THEN ' . $db->quoteName('c.core_created_by_alias') . ' ELSE ' . $db->quoteName('ua.name') . ' END AS ' . $db->quoteName('author'),
                $db->quoteName('ua.email', 'author_email'),
            ]
        )
            ->bind(':nullDate', $nullDate)
            ->from($db->quoteName('#__contentitem_tag_map', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__ucm_content', 'c'),
                $db->quoteName('m.type_alias') . ' = ' . $db->quoteName('c.core_type_alias')
                . ' AND ' . $db->quoteName('m.core_content_id') . ' = ' . $db->quoteName('c.core_content_id')
            )
            ->join('INNER', $db->quoteName('#__content_types', 'ct'), $db->quoteName('ct.type_alias') . ' = ' . $db->quoteName('m.type_alias'));

        // Join over categories to get only tags from published categories
        $query->join('LEFT', $db->quoteName('#__categories', 'tc'), $db->quoteName('tc.id') . ' = ' . $db->quoteName('c.core_catid'));

        // Join over the users for the author and email
        $query->join('LEFT', $db->quoteName('#__users', 'ua'), $db->quoteName('ua.id') . ' = ' . $db->quoteName('c.core_created_user_id'))
            ->whereIn($db->quoteName('c.core_state'), $stateFilters)
            ->whereIn($db->quoteName('m.tag_id'), $tagIds)
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('c.core_catid') . ' = 0',
                    $db->quoteName('tc.published') . ' = 1',
                ],
                'OR'
            );

        // Get the type data, limited to types in the request if there are any specified.
        $typesarray  = self::getTypes('assocList', $typesr, false);
        $typeAliases = \array_column($typesarray, 'type_alias');
        $query->whereIn($db->quoteName('m.type_alias'), $typeAliases, ParameterType::STRING);

        $groups   = array_values(array_unique($user->getAuthorisedViewLevels()));
        $groups[] = 0;
        $query->whereIn($db->quoteName('c.core_access'), $groups);

        if (!\in_array(0, $stateFilters, true)) {
            $query->extendWhere(
                'AND',
                [
                    $db->quoteName('c.core_publish_up') . ' = :nullDate1',
                    $db->quoteName('c.core_publish_up') . ' IS NULL',
                    $db->quoteName('c.core_publish_up') . ' <= :nowDate1',
                ],
                'OR'
            )
                ->extendWhere(
                    'AND',
                    [
                        $db->quoteName('c.core_publish_down') . ' = :nullDate2',
                        $db->quoteName('c.core_publish_down') . ' IS NULL',
                        $db->quoteName('c.core_publish_down') . ' >= :nowDate2',
                    ],
                    'OR'
                )
                ->bind([':nullDate1', ':nullDate2'], $nullDate)
                ->bind([':nowDate1', ':nowDate2'], $nowDate);
        }

        // Optionally filter on language
        if ($languageFilter !== 'all') {
            if ($languageFilter === 'current_language') {
                $languageFilter = $this->getCurrentLanguage();
            }

            $query->whereIn($db->quoteName('c.core_language'), [$languageFilter, '*'], ParameterType::STRING);
        }

        $query->group(
            [
                $db->quoteName('m.type_alias'),
                $db->quoteName('m.content_item_id'),
                $db->quoteName('m.core_content_id'),
                $db->quoteName('core_modified_time'),
                $db->quoteName('core_created_time'),
                $db->quoteName('core_created_by_alias'),
                $db->quoteName('author'),
                $db->quoteName('author_email'),
            ]
        );

        // Use HAVING if matching all tags and we are matching more than one tag.
        if ($ntagsr > 1 && $anyOrAll != 1 && $includeChildren != 1) {
            // The number of results should equal the number of tags requested.
            $query->having('COUNT(' . $db->quoteName('m.tag_id') . ') = :ntagsr')
                ->bind(':ntagsr', $ntagsr, ParameterType::INTEGER);
        }

        // Set up the order by using the option chosen
        if ($orderByOption === 'match_count') {
            $orderBy = 'COUNT(' . $db->quoteName('m.tag_id') . ')';
        } else {
            $orderBy = 'MAX(' . $db->quoteName($orderByOption) . ')';
        }

        $query->order($orderBy . ' ' . $orderDir);

        return $query;
    }

    /**
     * Function that converts tag ids to their tag names
     *
     * @param   array  $tagIds  Array of integer tag ids.
     *
     * @return  array  An array of tag names.
     *
     * @since   3.1
     */
    public function getTagNames($tagIds)
    {
        $tagNames = [];

        if (\is_array($tagIds) && \count($tagIds) > 0) {
            $tagIds = ArrayHelper::toInteger($tagIds);

            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('title'))
                ->from($db->quoteName('#__tags'))
                ->whereIn($db->quoteName('id'), $tagIds)
                ->order($db->quoteName('title'));

            $db->setQuery($query);
            $tagNames = $db->loadColumn();
        }

        return $tagNames;
    }

    /**
     * Method to get an array of tag ids for the current tag and its children
     *
     * @param   integer  $id             An optional ID
     * @param   array    &$tagTreeArray  Array containing the tag tree
     *
     * @return  mixed
     *
     * @since   3.1
     */
    public function getTagTreeArray($id, &$tagTreeArray = [])
    {
        // Get a level row instance.
        $table = Factory::getApplication()->bootComponent('com_tags')->getMVCFactory()->createTable('Tag', 'Administrator');

        if ($table->isLeaf($id)) {
            $tagTreeArray[] = $id;

            return $tagTreeArray;
        }

        $tagTree = $table->getTree($id);

        // Attempt to load the tree
        if ($tagTree) {
            foreach ($tagTree as $tag) {
                $tagTreeArray[] = $tag->id;
            }

            return $tagTreeArray;
        }
    }

    /**
     * Method to get a list of types with associated data.
     *
     * @param   string   $arrayType    Optionally specify that the returned list consist of objects, associative arrays, or arrays.
     *                                 Options are: rowList, assocList, and objectList
     * @param   array    $selectTypes  Optional array of type ids or aliases to limit the results to. Often from a request.
     * @param   boolean  $useAlias     If true, the alias is used to match, if false the type_id is used.
     *
     * @return  array   Array of of types
     *
     * @since   3.1
     */
    public static function getTypes($arrayType = 'objectList', $selectTypes = null, $useAlias = true)
    {
        // Initialize some variables.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*');

        if (!empty($selectTypes)) {
            $selectTypes = (array) $selectTypes;

            if ($useAlias) {
                $query->whereIn($db->quoteName('type_alias'), $selectTypes, ParameterType::STRING);
            } else {
                $selectTypes = ArrayHelper::toInteger($selectTypes);

                $query->whereIn($db->quoteName('type_id'), $selectTypes);
            }
        }

        $query->from($db->quoteName('#__content_types'));

        $db->setQuery($query);

        switch ($arrayType) {
            case 'assocList':
                $types = $db->loadAssocList();
                break;

            case 'rowList':
                $types = $db->loadRowList();
                break;

            case 'objectList':
            default:
                $types = $db->loadObjectList();
                break;
        }

        return $types;
    }

    /**
     * Function that handles saving tags used in a table class after a store()
     *
     * @param   TableInterface  $table    Table being processed
     * @param   array           $newTags  Array of new tags
     * @param   boolean         $replace  Flag indicating if all existing tags should be replaced
     *
     * @return  boolean
     *
     * @since   3.1
     */
    public function postStoreProcess(TableInterface $table, $newTags = [], $replace = true)
    {
        if (!empty($table->newTags) && empty($newTags)) {
            $newTags = $table->newTags;
        }

        // If existing row, check to see if tags have changed.
        $newTable = clone $table;
        $newTable->reset();

        $result = true;

        // Process ucm_content and ucm_base if either tags have changed or we have some tags.
        if ($this->tagsChanged || (!empty($newTags) && $newTags[0] != '')) {
            if (!$newTags && $replace == true) {
                // Delete all tags data
                $key    = $table->getKeyName();
                $result = $this->deleteTagData($table, $table->$key);
            } else {
                // Process the tags
                $data            = $this->getRowData($table);
                $ucmContentTable = Table::getInstance('Corecontent');

                $ucm     = new UCMContent($table, $this->typeAlias);
                $ucmData = $data ? $ucm->mapData($data) : $ucm->ucmData;

                $primaryId = $ucm->getPrimaryKey($ucmData['common']['core_type_id'], $ucmData['common']['core_content_item_id']);
                $result    = $ucmContentTable->load($primaryId);
                $result    = $result && $ucmContentTable->bind($ucmData['common']);
                $result    = $result && $ucmContentTable->check();
                $result    = $result && $ucmContentTable->store();
                $ucmId     = $ucmContentTable->core_content_id;

                // Store the tag data if the article data was saved and run related methods.
                $result = $result && $this->tagItem($ucmId, $table, $newTags, $replace);
            }
        }

        return $result;
    }

    /**
     * Function that preProcesses data from a table prior to a store() to ensure proper tag handling
     *
     * @param   TableInterface  $table    Table being processed
     * @param   array           $newTags  Array of new tags
     *
     * @return  null
     *
     * @since   3.1
     */
    public function preStoreProcess(TableInterface $table, $newTags = [])
    {
        if ($newTags != []) {
            $this->newTags = $newTags;
        }

        // If existing row, check to see if tags have changed.
        $oldTable = clone $table;
        $oldTable->reset();
        $key       = $oldTable->getKeyName();
        $typeAlias = $this->typeAlias;

        if ($oldTable->$key && $oldTable->load()) {
            $this->oldTags = $this->getTagIds($oldTable->$key, $typeAlias);
        }

        // New items with no tags bypass this step.
        if ((!empty($newTags) && \is_string($newTags) || (isset($newTags[0]) && $newTags[0] != '')) || isset($this->oldTags)) {
            if (\is_array($newTags)) {
                $newTags = implode(',', $newTags);
            }

            // We need to process tags if the tags have changed or if we have a new row
            $this->tagsChanged = (empty($this->oldTags) && !empty($newTags)) || (!empty($this->oldTags) && $this->oldTags != $newTags) || !$table->$key;
        }
    }

    /**
     * Function to search tags
     *
     * @param   array  $filters  Filter to apply to the search
     *
     * @return  array
     *
     * @since   3.1
     */
    public static function searchTags($filters = [])
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('a.id', 'value'),
                    $db->quoteName('a.path', 'text'),
                    $db->quoteName('a.path'),
                ]
            )
            ->from($db->quoteName('#__tags', 'a'))
            ->join(
                'LEFT',
                $db->quoteName('#__tags', 'b'),
                $db->quoteName('a.lft') . ' > ' . $db->quoteName('b.lft') . ' AND ' . $db->quoteName('a.rgt') . ' < ' . $db->quoteName('b.rgt')
            );

        // Do not return root
        $query->where($db->quoteName('a.alias') . ' <> ' . $db->quote('root'));

        // Filter language
        if (!empty($filters['flanguage'])) {
            $query->whereIn($db->quoteName('a.language'), [$filters['flanguage'], '*'], ParameterType::STRING);
        }

        // Search in title or path
        if (!empty($filters['like'])) {
            $search = '%' . $filters['like'] . '%';
            $query->extendWhere(
                'AND',
                [
                    $db->quoteName('a.title') . ' LIKE :search1',
                    $db->quoteName('a.path') . ' LIKE :search2',
                ],
                'OR'
            )
                ->bind([':search1', ':search2'], $search);
        }

        // Filter title
        if (!empty($filters['title'])) {
            $query->where($db->quoteName('a.title') . ' = :title')
                ->bind(':title', $filters['title']);
        }

        // Filter on the published state
        if (isset($filters['published']) && is_numeric($filters['published'])) {
            $published = (int) $filters['published'];
            $query->where($db->quoteName('a.published') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
        }

        // Filter on the access level
        if (isset($filters['access']) && \is_array($filters['access']) && \count($filters['access'])) {
            $groups = ArrayHelper::toInteger($filters['access']);
            $query->whereIn($db->quoteName('a.access'), $groups);
        }

        // Filter by parent_id
        if (isset($filters['parent_id']) && is_numeric($filters['parent_id'])) {
            $tagTable = Factory::getApplication()->bootComponent('com_tags')->getMVCFactory()->createTable('Tag', 'Administrator');

            if ($children = $tagTable->getTree($filters['parent_id'])) {
                $childrenIds = \array_column($children, 'id');

                $query->whereIn($db->quoteName('a.id'), $childrenIds);
            }
        }

        $query->group(
            [
                $db->quoteName('a.id'),
                $db->quoteName('a.title'),
                $db->quoteName('a.level'),
                $db->quoteName('a.lft'),
                $db->quoteName('a.rgt'),
                $db->quoteName('a.parent_id'),
                $db->quoteName('a.published'),
                $db->quoteName('a.path'),
            ]
        )
            ->order($db->quoteName('a.lft') . ' ASC');

        // Get the options.
        $db->setQuery($query);

        try {
            $results = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            return [];
        }

        // We will replace path aliases with tag names
        return self::convertPathsToNames($results);
    }

    /**
     * Method to delete all instances of a tag from the mapping table. Generally used when a tag is deleted.
     *
     * @param   integer  $tagId  The tag_id (primary key) for the deleted tag.
     *
     * @return  void
     *
     * @since   3.1
     */
    public function tagDeleteInstances($tagId)
    {
        // Cast as integer until method is typehinted.
        $tag_id = (int) $tagId;

        // Delete the old tag maps.
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__contentitem_tag_map'))
            ->where($db->quoteName('tag_id') . ' = :id')
            ->bind(':id', $tagId, ParameterType::INTEGER);
        $db->setQuery($query);
        $db->execute();
    }

    /**
     * Method to add or update tags associated with an item.
     *
     * @param   integer         $ucmId    Id of the #__ucm_content item being tagged
     * @param   TableInterface  $table    Table object being tagged
     * @param   array           $tags     Array of tags to be applied.
     * @param   boolean         $replace  Flag indicating if all existing tags should be replaced
     *
     * @return  boolean  true on success, otherwise false.
     *
     * @since   3.1
     */
    public function tagItem($ucmId, TableInterface $table, $tags = [], $replace = true)
    {
        $key     = $table->get('_tbl_key');
        $oldTags = $this->getTagIds((int) $table->$key, $this->typeAlias);
        $oldTags = explode(',', $oldTags);
        $result  = $this->unTagItem($ucmId, $table);

        if ($replace) {
            $newTags = $tags;
        } else {
            if ($tags == []) {
                $newTags = $table->newTags;
            } else {
                $newTags = $tags;
            }

            if ($oldTags[0] != '') {
                $newTags = array_unique(array_merge($newTags, $oldTags));
            }
        }

        if (\is_array($newTags) && \count($newTags) > 0 && $newTags[0] != '') {
            $result = $result && $this->addTagMapping($ucmId, $table, $newTags);
        }

        return $result;
    }

    /**
     * Method to untag an item
     *
     * @param   integer         $contentId  ID of the content item being untagged
     * @param   TableInterface  $table      Table object being untagged
     * @param   array           $tags       Array of tags to be untagged. Use an empty array to untag all existing tags.
     *
     * @return  boolean  true on success, otherwise false.
     *
     * @since   3.1
     */
    public function unTagItem($contentId, TableInterface $table, $tags = [])
    {
        $key   = $table->getKeyName();
        $id    = (int) $table->$key;
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__contentitem_tag_map'))
            ->where(
                [
                    $db->quoteName('type_alias') . ' = :type',
                    $db->quoteName('content_item_id') . ' = :id',
                ]
            )
            ->bind(':type', $this->typeAlias)
            ->bind(':id', $id, ParameterType::INTEGER);

        if (\is_array($tags) && \count($tags) > 0) {
            $tags = ArrayHelper::toInteger($tags);

            $query->whereIn($db->quoteName('tag_id'), $tags);
        }

        $db->setQuery($query);

        return (bool) $db->execute();
    }
}
