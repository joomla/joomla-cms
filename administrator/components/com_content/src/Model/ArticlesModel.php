<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods supporting a list of article records.
 *
 * @since  1.6
 */
class ArticlesModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since   1.6
     * @see     \Joomla\CMS\MVC\Controller\BaseController
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'alias', 'a.alias',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'catid', 'a.catid', 'category_title',
                'state', 'a.state',
                'access', 'a.access', 'access_level',
                'created', 'a.created',
                'modified', 'a.modified',
                'created_by', 'a.created_by',
                'created_by_alias', 'a.created_by_alias',
                'ordering', 'a.ordering',
                'featured', 'a.featured',
                'featured_up', 'fp.featured_up',
                'featured_down', 'fp.featured_down',
                'language', 'a.language',
                'hits', 'a.hits',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down',
                'published', 'a.published',
                'author_id',
                'category_id',
                'level',
                'tag',
                'rating_count', 'rating',
                'stage', 'wa.stage_id',
                'ws.title',
            ];

            if (Associations::isEnabled()) {
                $config['filter_fields'][] = 'association';
            }
        }

        parent::__construct($config);
    }

    /**
     * Get the filter form
     *
     * @param   array    $data      data
     * @param   boolean  $loadData  load current data
     *
     * @return  \Joomla\CMS\Form\Form|null  The Form object or null if the form can't be found
     *
     * @since   3.2
     */
    public function getFilterForm($data = [], $loadData = true)
    {
        $form = parent::getFilterForm($data, $loadData);

        $params = ComponentHelper::getParams('com_content');

        if (!$params->get('workflow_enabled')) {
            $form->removeField('stage', 'filter');
        } else {
            $ordering = $form->getField('fullordering', 'list');

            $ordering->addOption('JSTAGE_ASC', ['value' => 'ws.title ASC']);
            $ordering->addOption('JSTAGE_DESC', ['value' => 'ws.title DESC']);
        }

        return $form;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'a.id', $direction = 'desc')
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $forcedLanguage = $input->get('forcedLanguage', '', 'cmd');

        // Adjust the context to support modal layouts.
        if ($layout = $input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.' . $forcedLanguage;
        }

        // List state information.
        parent::populateState($ordering, $direction);

        // Force a language
        if (!empty($forcedLanguage)) {
            $this->setState('filter.language', $forcedLanguage);
            $this->setState('filter.forcedLanguage', $forcedLanguage);
        }
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . serialize($this->getState('filter.access'));
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . serialize($this->getState('filter.category_id'));
        $id .= ':' . serialize($this->getState('filter.author_id'));
        $id .= ':' . $this->getState('filter.language');
        $id .= ':' . serialize($this->getState('filter.tag'));

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  QueryInterface
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $user  = $this->getCurrentUser();

        $params = ComponentHelper::getParams('com_content');

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.asset_id'),
                    $db->quoteName('a.title'),
                    $db->quoteName('a.alias'),
                    $db->quoteName('a.checked_out'),
                    $db->quoteName('a.checked_out_time'),
                    $db->quoteName('a.catid'),
                    $db->quoteName('a.state'),
                    $db->quoteName('a.access'),
                    $db->quoteName('a.created'),
                    $db->quoteName('a.created_by'),
                    $db->quoteName('a.created_by_alias'),
                    $db->quoteName('a.modified'),
                    $db->quoteName('a.ordering'),
                    $db->quoteName('a.featured'),
                    $db->quoteName('a.language'),
                    $db->quoteName('a.hits'),
                    $db->quoteName('a.publish_up'),
                    $db->quoteName('a.publish_down'),
                    $db->quoteName('a.introtext'),
                    $db->quoteName('a.fulltext'),
                    $db->quoteName('a.note'),
                    $db->quoteName('a.images'),
                    $db->quoteName('a.metakey'),
                    $db->quoteName('a.metadesc'),
                    $db->quoteName('a.metadata'),
                    $db->quoteName('a.version'),
                ]
            )
        )
            ->select(
                [
                    $db->quoteName('fp.featured_up'),
                    $db->quoteName('fp.featured_down'),
                    $db->quoteName('l.title', 'language_title'),
                    $db->quoteName('l.image', 'language_image'),
                    $db->quoteName('uc.name', 'editor'),
                    $db->quoteName('ag.title', 'access_level'),
                    $db->quoteName('c.title', 'category_title'),
                    $db->quoteName('c.created_user_id', 'category_uid'),
                    $db->quoteName('c.level', 'category_level'),
                    $db->quoteName('c.published', 'category_published'),
                    $db->quoteName('parent.title', 'parent_category_title'),
                    $db->quoteName('parent.id', 'parent_category_id'),
                    $db->quoteName('parent.created_user_id', 'parent_category_uid'),
                    $db->quoteName('parent.level', 'parent_category_level'),
                    $db->quoteName('ua.name', 'author_name'),
                    $db->quoteName('wa.stage_id', 'stage_id'),
                    $db->quoteName('ws.title', 'stage_title'),
                    $db->quoteName('ws.workflow_id', 'workflow_id'),
                    $db->quoteName('w.title', 'workflow_title'),
                ]
            )
            ->from($db->quoteName('#__content', 'a'))
            ->where($db->quoteName('wa.extension') . ' = ' . $db->quote('com_content.article'))
            ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'))
            ->join('LEFT', $db->quoteName('#__content_frontpage', 'fp'), $db->quoteName('fp.content_id') . ' = ' . $db->quoteName('a.id'))
            ->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'))
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'))
            ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
            ->join('LEFT', $db->quoteName('#__categories', 'parent'), $db->quoteName('parent.id') . ' = ' . $db->quoteName('c.parent_id'))
            ->join('LEFT', $db->quoteName('#__users', 'ua'), $db->quoteName('ua.id') . ' = ' . $db->quoteName('a.created_by'))
            ->join('INNER', $db->quoteName('#__workflow_associations', 'wa'), $db->quoteName('wa.item_id') . ' = ' . $db->quoteName('a.id'))
            ->join('INNER', $db->quoteName('#__workflow_stages', 'ws'), $db->quoteName('ws.id') . ' = ' . $db->quoteName('wa.stage_id'))
            ->join('INNER', $db->quoteName('#__workflows', 'w'), $db->quoteName('w.id') . ' = ' . $db->quoteName('ws.workflow_id'));

        if (PluginHelper::isEnabled('content', 'vote')) {
            $query->select(
                [
                    'COALESCE(NULLIF(ROUND(' . $db->quoteName('v.rating_sum') . ' / ' . $db->quoteName('v.rating_count') . ', 0), 0), 0)'
                        . ' AS ' . $db->quoteName('rating'),
                    'COALESCE(NULLIF(' . $db->quoteName('v.rating_count') . ', 0), 0) AS ' . $db->quoteName('rating_count'),
                ]
            )
                ->join('LEFT', $db->quoteName('#__content_rating', 'v'), $db->quoteName('a.id') . ' = ' . $db->quoteName('v.content_id'));
        }

        // Join over the associations.
        if (Associations::isEnabled()) {
            $subQuery = $db->getQuery(true)
                ->select('COUNT(' . $db->quoteName('asso1.id') . ') > 1')
                ->from($db->quoteName('#__associations', 'asso1'))
                ->join('INNER', $db->quoteName('#__associations', 'asso2'), $db->quoteName('asso1.key') . ' = ' . $db->quoteName('asso2.key'))
                ->where(
                    [
                        $db->quoteName('asso1.id') . ' = ' . $db->quoteName('a.id'),
                        $db->quoteName('asso1.context') . ' = ' . $db->quote('com_content.item'),
                    ]
                );

            $query->select('(' . $subQuery . ') AS ' . $db->quoteName('association'));
        }

        // Filter by access level.
        $access = $this->getState('filter.access');

        if (is_numeric($access)) {
            $access = (int) $access;
            $query->where($db->quoteName('a.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        } elseif (\is_array($access)) {
            $access = ArrayHelper::toInteger($access);
            $query->whereIn($db->quoteName('a.access'), $access);
        }

        // Filter by featured.
        $featured = (string) $this->getState('filter.featured');

        if (\in_array($featured, ['0','1'])) {
            $featured = (int) $featured;
            $query->where($db->quoteName('a.featured') . ' = :featured')
                ->bind(':featured', $featured, ParameterType::INTEGER);
        }

        // Filter by access level on categories.
        if (!$user->authorise('core.admin')) {
            $groups = $user->getAuthorisedViewLevels();
            $query->whereIn($db->quoteName('a.access'), $groups);
            $query->whereIn($db->quoteName('c.access'), $groups);
        }

        // Filter by published state
        $workflowStage = (string) $this->getState('filter.stage');

        if ($params->get('workflow_enabled') && is_numeric($workflowStage)) {
            $workflowStage = (int) $workflowStage;
            $query->where($db->quoteName('wa.stage_id') . ' = :stage')
                ->bind(':stage', $workflowStage, ParameterType::INTEGER);
        }

        $published = (string) $this->getState('filter.published');

        if ($published !== '*') {
            if (is_numeric($published)) {
                $state = (int) $published;
                $query->where($db->quoteName('a.state') . ' = :state')
                    ->bind(':state', $state, ParameterType::INTEGER);
            } elseif (!is_numeric($workflowStage)) {
                $query->whereIn(
                    $db->quoteName('a.state'),
                    [
                        ContentComponent::CONDITION_PUBLISHED,
                        ContentComponent::CONDITION_UNPUBLISHED,
                    ]
                );
            }
        }

        // Filter by categories and by level
        $categoryId = $this->getState('filter.category_id', []);
        $level      = (int) $this->getState('filter.level');

        if (!\is_array($categoryId)) {
            $categoryId = $categoryId ? [$categoryId] : [];
        }

        // Case: Using both categories filter and by level filter
        if (\count($categoryId)) {
            $categoryId       = ArrayHelper::toInteger($categoryId);
            $categoryTable    = Table::getInstance('Category', '\\Joomla\\CMS\\Table\\');
            $subCatItemsWhere = [];

            foreach ($categoryId as $key => $filter_catid) {
                $categoryTable->load($filter_catid);

                // Because values to $query->bind() are passed by reference, using $query->bindArray() here instead to prevent overwriting.
                $valuesToBind = [$categoryTable->lft, $categoryTable->rgt];

                if ($level) {
                    $valuesToBind[] = $level + $categoryTable->level - 1;
                }

                // Bind values and get parameter names.
                $bounded = $query->bindArray($valuesToBind);

                $categoryWhere = $db->quoteName('c.lft') . ' >= ' . $bounded[0] . ' AND ' . $db->quoteName('c.rgt') . ' <= ' . $bounded[1];

                if ($level) {
                    $categoryWhere .= ' AND ' . $db->quoteName('c.level') . ' <= ' . $bounded[2];
                }

                $subCatItemsWhere[] = '(' . $categoryWhere . ')';
            }

            $query->where('(' . implode(' OR ', $subCatItemsWhere) . ')');
        } elseif ($level = (int) $level) {
            // Case: Using only the by level filter
            $query->where($db->quoteName('c.level') . ' <= :level')
                ->bind(':level', $level, ParameterType::INTEGER);
        }

        // Filter by author
        $authorId = $this->getState('filter.author_id');

        if (is_numeric($authorId)) {
            $authorId = (int) $authorId;
            $type     = $this->getState('filter.author_id.include', true) ? ' = ' : ' <> ';
            $query->where($db->quoteName('a.created_by') . $type . ':authorId')
                ->bind(':authorId', $authorId, ParameterType::INTEGER);
        } elseif (\is_array($authorId)) {
            // Check to see if by_me is in the array
            if (\in_array('by_me', $authorId)) {
                // Replace by_me with the current user id in the array
                $authorId['by_me'] = $user->id;
            }

            $authorId = ArrayHelper::toInteger($authorId);
            $query->whereIn($db->quoteName('a.created_by'), $authorId);
        }

        // Filter by search in title.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :search')
                    ->bind(':search', $search, ParameterType::INTEGER);
            } elseif (stripos($search, 'author:') === 0) {
                $search = '%' . substr($search, 7) . '%';
                $query->where('(' . $db->quoteName('ua.name') . ' LIKE :search1 OR ' . $db->quoteName('ua.username') . ' LIKE :search2)')
                    ->bind([':search1', ':search2'], $search);
            } elseif (stripos($search, 'content:') === 0) {
                $search = '%' . substr($search, 8) . '%';
                $query->where('(' . $db->quoteName('a.introtext') . ' LIKE :search1 OR ' . $db->quoteName('a.fulltext') . ' LIKE :search2)')
                    ->bind([':search1', ':search2'], $search);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where(
                    '(' . $db->quoteName('a.title') . ' LIKE :search1 OR ' . $db->quoteName('a.alias') . ' LIKE :search2'
                        . ' OR ' . $db->quoteName('a.note') . ' LIKE :search3)'
                )
                    ->bind([':search1', ':search2', ':search3'], $search);
            }
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' = :language')
                ->bind(':language', $language);
        }

        // Filter by a single or group of tags.
        $tag = $this->getState('filter.tag');

        // Run simplified query when filtering by one tag.
        if (\is_array($tag) && \count($tag) === 1) {
            $tag = $tag[0];
        }

        if ($tag && \is_array($tag)) {
            $tag = ArrayHelper::toInteger($tag);

            $subQuery = $db->getQuery(true)
                ->select('DISTINCT ' . $db->quoteName('content_item_id'))
                ->from($db->quoteName('#__contentitem_tag_map'))
                ->where(
                    [
                        $db->quoteName('tag_id') . ' IN (' . implode(',', $query->bindArray($tag)) . ')',
                        $db->quoteName('type_alias') . ' = ' . $db->quote('com_content.article'),
                    ]
                );

            $query->join(
                'INNER',
                '(' . $subQuery . ') AS ' . $db->quoteName('tagmap'),
                $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
            );
        } elseif ($tag = (int) $tag) {
            $query->join(
                'INNER',
                $db->quoteName('#__contentitem_tag_map', 'tagmap'),
                $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
            )
                ->where(
                    [
                        $db->quoteName('tagmap.tag_id') . ' = :tag',
                        $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_content.article'),
                    ]
                )
                ->bind(':tag', $tag, ParameterType::INTEGER);
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.id');
        $orderDirn = $this->state->get('list.direction', 'DESC');

        if ($orderCol === 'a.ordering' || $orderCol === 'category_title') {
            $ordering = [
                $db->quoteName('c.title') . ' ' . $db->escape($orderDirn),
                $db->quoteName('a.ordering') . ' ' . $db->escape($orderDirn),
            ];
        } else {
            $ordering = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
        }

        $query->order($ordering);

        return $query;
    }

    /**
     * Method to get all transitions at once for all articles
     *
     * @return  array|boolean
     *
     * @since   4.0.0
     */
    public function getTransitions()
    {
        // Get a storage key.
        $store = $this->getStoreId('getTransitions');

        // Try to load the data from internal storage.
        if (isset($this->cache[$store])) {
            return $this->cache[$store];
        }

        $db   = $this->getDatabase();
        $user = $this->getCurrentUser();

        $items = $this->getItems();

        if ($items === false) {
            return false;
        }

        $stage_ids = ArrayHelper::getColumn($items, 'stage_id');
        $stage_ids = ArrayHelper::toInteger($stage_ids);
        $stage_ids = array_values(array_unique(array_filter($stage_ids)));

        $workflow_ids = ArrayHelper::getColumn($items, 'workflow_id');
        $workflow_ids = ArrayHelper::toInteger($workflow_ids);
        $workflow_ids = array_values(array_unique(array_filter($workflow_ids)));

        $this->cache[$store] = [];

        try {
            if (\count($stage_ids) || \count($workflow_ids)) {
                Factory::getLanguage()->load('com_workflow', JPATH_ADMINISTRATOR);

                $query = $db->getQuery(true);

                $query  ->select(
                    [
                        $db->quoteName('t.id', 'value'),
                        $db->quoteName('t.title', 'text'),
                        $db->quoteName('t.from_stage_id'),
                        $db->quoteName('t.to_stage_id'),
                        $db->quoteName('s.id', 'stage_id'),
                        $db->quoteName('s.title', 'stage_title'),
                        $db->quoteName('t.workflow_id'),
                    ]
                )
                    ->from($db->quoteName('#__workflow_transitions', 't'))
                    ->innerJoin(
                        $db->quoteName('#__workflow_stages', 's'),
                        $db->quoteName('t.to_stage_id') . ' = ' . $db->quoteName('s.id')
                    )
                    ->where(
                        [
                            $db->quoteName('t.published') . ' = 1',
                            $db->quoteName('s.published') . ' = 1',
                        ]
                    )
                    ->order($db->quoteName('t.ordering'));

                $where = [];

                if (\count($stage_ids)) {
                    $where[] = $db->quoteName('t.from_stage_id') . ' IN (' . implode(',', $query->bindArray($stage_ids)) . ')';
                }

                if (\count($workflow_ids)) {
                    $where[] = '(' . $db->quoteName('t.from_stage_id') . ' = -1 AND ' . $db->quoteName('t.workflow_id') . ' IN (' . implode(',', $query->bindArray($workflow_ids)) . '))';
                }

                $query->where('((' . implode(') OR (', $where) . '))');

                $transitions = $db->setQuery($query)->loadAssocList();

                foreach ($transitions as $key => $transition) {
                    if (!$user->authorise('core.execute.transition', 'com_content.transition.' . (int) $transition['value'])) {
                        unset($transitions[$key]);
                    }

                    $transitions[$key]['text'] = Text::_($transition['text']);
                }

                $this->cache[$store] = $transitions;
            }
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return $this->cache[$store];
    }

    /**
     * Method to get a list of articles.
     * Overridden to add item type alias.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   4.0.0
     */
    public function getItems()
    {
        $items = parent::getItems();

        foreach ($items as $item) {
            $item->typeAlias = 'com_content.article';

            if (isset($item->metadata)) {
                $registry       = new Registry($item->metadata);
                $item->metadata = $registry->toArray();
            }
        }

        return $items;
    }
}
