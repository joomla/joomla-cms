<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\Model;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Categories\CategoryNode;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Category;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Single item model for a contact
 *
 * @package     Joomla.Site
 * @subpackage  com_contact
 * @since       1.5
 */
class CategoryModel extends ListModel
{
    /**
     * Category item data
     *
     * @var    CategoryNode
     */
    protected $_item;

    /**
     * Category left and right of this one
     *
     * @var    CategoryNode[]|null
     */
    protected $_siblings;

    /**
     * Array of child-categories
     *
     * @var    CategoryNode[]|null
     */
    protected $_children;

    /**
     * Parent category of the current one
     *
     * @var    CategoryNode|null
     */
    protected $_parent;

    /**
     * The category that applies.
     *
     * @var    object
     */
    protected $_category;

    /**
     * The list of other contact categories.
     *
     * @var    array
     */
    protected $_categories;

    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @since   1.6
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'name', 'a.name',
                'con_position', 'a.con_position',
                'suburb', 'a.suburb',
                'state', 'a.state',
                'country', 'a.country',
                'ordering', 'a.ordering',
                'sortname',
                'sortname1', 'a.sortname1',
                'sortname2', 'a.sortname2',
                'sortname3', 'a.sortname3',
                'featuredordering', 'a.featured',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to get a list of items.
     *
     * @return  mixed  An array of objects on success, false on failure.
     */
    public function getItems()
    {
        // Invoke the parent getItems method to get the main list
        $items = parent::getItems();

        if ($items === false) {
            return false;
        }

        $taggedItems = [];

        // Convert the params field into an object, saving original in _params
        foreach ($items as $item) {
            if (!isset($this->_params)) {
                $item->params = new Registry($item->params);
            }

            // Some contexts may not use tags data at all, so we allow callers to disable loading tag data
            if ($this->getState('load_tags', true)) {
                $item->tags             = new TagsHelper();
                $taggedItems[$item->id] = $item;
            }
        }

        // Load tags of all items.
        if ($taggedItems) {
            $tagsHelper = new TagsHelper();
            $itemIds    = array_keys($taggedItems);

            foreach ($tagsHelper->getMultipleItemTags('com_contact.contact', $itemIds) as $id => $tags) {
                $taggedItems[$id]->tags->itemTags = $tags;
            }
        }

        return $items;
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return  QueryInterface    An SQL query
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        $user   = $this->getCurrentUser();
        $groups = $user->getAuthorisedViewLevels();

        // Create a new query object.
        $db = $this->getDatabase();

        /** @var \Joomla\Database\DatabaseQuery $query */
        $query = $db->getQuery(true);

        $query->select($this->getState('list.select', 'a.*'))
            ->select($this->getSlugColumn($query, 'a.id', 'a.alias') . ' AS slug')
            ->select($this->getSlugColumn($query, 'c.id', 'c.alias') . ' AS catslug')
        /**
         * @todo: we actually should be doing it but it's wrong this way
         *  . ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
         *  . ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END AS catslug ');
         */
            ->from($db->quoteName('#__contact_details', 'a'))
            ->leftJoin($db->quoteName('#__categories', 'c') . ' ON c.id = a.catid')
            ->whereIn($db->quoteName('a.access'), $groups);

        // Filter by category.
        $categoryId           = (int) $this->getState('category.id');
        $includeSubcategories = (int) $this->getState('filter.max_category_levels', 1) !== 0;

        if ($includeSubcategories) {
            $levels = (int) $this->getState('filter.max_category_levels', 1);

            // Create a subquery for the subcategory list
            $subQuery = $db->getQuery(true)
                ->select($db->quoteName('sub.id'))
                ->from($db->quoteName('#__categories', 'sub'))
                ->join(
                    'INNER',
                    $db->quoteName('#__categories', 'this'),
                    $db->quoteName('sub.lft') . ' > ' . $db->quoteName('this.lft')
                    . ' AND ' . $db->quoteName('sub.rgt') . ' < ' . $db->quoteName('this.rgt')
                )
                ->where($db->quoteName('this.id') . ' = :subCategoryId');

            $query->bind(':subCategoryId', $categoryId, ParameterType::INTEGER);

            if ($levels >= 0) {
                $subQuery->where($db->quoteName('sub.level') . ' <= ' . $db->quoteName('this.level') . ' + :levels');
                $query->bind(':levels', $levels, ParameterType::INTEGER);
            }

            // Add the subquery to the main query
            $query->where(
                '(' . $db->quoteName('a.catid') . ' = :categoryId OR ' . $db->quoteName('a.catid') . ' IN (' . $subQuery . '))'
            );
            $query->bind(':categoryId', $categoryId, ParameterType::INTEGER);
        } else {
            $query->where($db->quoteName('a.catid') . ' = :acatid')
                ->whereIn($db->quoteName('c.access'), $groups);
            $query->bind(':acatid', $categoryId, ParameterType::INTEGER);
        }

        // Join over the users for the author and modified_by names.
        $query->select("CASE WHEN a.created_by_alias > ' ' THEN a.created_by_alias ELSE ua.name END AS author")
            ->select('ua.email AS author_email')
            ->leftJoin($db->quoteName('#__users', 'ua') . ' ON ua.id = a.created_by')
            ->leftJoin($db->quoteName('#__users', 'uam') . ' ON uam.id = a.modified_by');

        // Filter by state
        $state = $this->getState('filter.published');

        if (is_numeric($state)) {
            $query->where($db->quoteName('a.published') . ' = :published');
            $query->bind(':published', $state, ParameterType::INTEGER);
        } else {
            $query->whereIn($db->quoteName('c.published'), [0,1,2]);
        }

        // Filter by start and end dates.
        $nowDate = Factory::getDate()->toSql();

        if ($this->getState('filter.publish_date')) {
            $query->where('(' . $db->quoteName('a.publish_up')
                . ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' <= :publish_up)')
                ->where('(' . $db->quoteName('a.publish_down')
                    . ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' >= :publish_down)')
                ->bind(':publish_up', $nowDate)
                ->bind(':publish_down', $nowDate);
        }

        // Filter by search in title
        $search = $this->getState('list.filter');

        if (!empty($search)) {
            $search = '%' . trim($search) . '%';
            $query->where($db->quoteName('a.name') . ' LIKE :name ');
            $query->bind(':name', $search);
        }

        // Filter on the language.
        if ($this->getState('filter.language')) {
            $query->whereIn($db->quoteName('a.language'), [Factory::getApplication()->getLanguage()->getTag(), '*'], ParameterType::STRING);
        }

        // Set sortname ordering if selected
        if ($this->getState('list.ordering') === 'sortname') {
            $query->order($db->escape('a.sortname1') . ' ' . $db->escape($this->getState('list.direction', 'ASC')))
                ->order($db->escape('a.sortname2') . ' ' . $db->escape($this->getState('list.direction', 'ASC')))
                ->order($db->escape('a.sortname3') . ' ' . $db->escape($this->getState('list.direction', 'ASC')));
        } elseif ($this->getState('list.ordering') === 'featuredordering') {
            $query->order($db->escape('a.featured') . ' DESC')
                ->order($db->escape('a.ordering') . ' ASC');
        } else {
            $query->order($db->escape($this->getState('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));
        }

        return $query;
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
    protected function populateState($ordering = null, $direction = null)
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $params = $app->getParams();
        $this->setState('params', $params);

        // List state information
        $format = $input->getWord('format');

        if ($format === 'feed') {
            $limit = $app->get('feed_limit');
        } else {
            $limit = $app->getUserStateFromRequest(
                'com_contact.category.list.limit',
                'limit',
                $params->get('contacts_display_num', $app->get('list_limit')),
                'uint'
            );
        }

        $this->setState('list.limit', $limit);

        $limitstart = $input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $limitstart);

        // Optional filter text
        $itemid = $input->get('Itemid', 0, 'int');
        $search = $app->getUserStateFromRequest('com_contact.category.list.' . $itemid . '.filter-search', 'filter-search', '', 'string');
        $this->setState('list.filter', $search);

        $orderCol = $input->get('filter_order', $params->get('initial_sort', 'ordering'));

        if (!\in_array($orderCol, $this->filter_fields)) {
            $orderCol = 'ordering';
        }

        $this->setState('list.ordering', $orderCol);

        $listOrder = $input->get('filter_order_Dir', 'ASC');

        if (!\in_array(strtoupper($listOrder), ['ASC', 'DESC', ''])) {
            $listOrder = 'ASC';
        }

        $this->setState('list.direction', $listOrder);

        $id = $input->get('id', 0, 'int');
        $this->setState('category.id', $id);
        $this->setState('filter.max_category_levels', $params->get('maxLevel', 1));

        $user = $this->getCurrentUser();

        if ((!$user->authorise('core.edit.state', 'com_contact')) && (!$user->authorise('core.edit', 'com_contact'))) {
            // Limit to published for people who can't edit or edit.state.
            $this->setState('filter.published', 1);

            // Filter by start and end dates.
            $this->setState('filter.publish_date', true);
        }

        $this->setState('filter.language', Multilanguage::isEnabled());
    }

    /**
     * Method to get category data for the current category
     *
     * @return  object  The category object
     *
     * @since   1.5
     */
    public function getCategory()
    {
        if (!\is_object($this->_item)) {
            $app    = Factory::getApplication();
            $menu   = $app->getMenu();
            $active = $menu->getActive();

            if ($active) {
                $params = $active->getParams();
            } else {
                $params = new Registry();
            }

            $options               = [];
            $options['countItems'] = $params->get('show_cat_items', 1) || $params->get('show_empty_categories', 0);
            $categories            = Categories::getInstance('Contact', $options);
            $this->_item           = $categories->get($this->getState('category.id', 'root'));

            if (\is_object($this->_item)) {
                $this->_children = $this->_item->getChildren();
                $this->_parent   = false;

                if ($this->_item->getParent()) {
                    $this->_parent = $this->_item->getParent();
                }

                $this->_rightsibling = $this->_item->getSibling();
                $this->_leftsibling  = $this->_item->getSibling(false);
            } else {
                $this->_children = false;
                $this->_parent   = false;
            }
        }

        return $this->_item;
    }

    /**
     * Get the parent category.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    public function getParent()
    {
        if (!\is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_parent;
    }

    /**
     * Get the sibling (adjacent) categories.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    public function &getLeftSibling()
    {
        if (!\is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_leftsibling;
    }

    /**
     * Get the sibling (adjacent) categories.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    public function &getRightSibling()
    {
        if (!\is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_rightsibling;
    }

    /**
     * Get the child categories.
     *
     * @return  mixed  An array of categories or false if an error occurs.
     */
    public function &getChildren()
    {
        if (!\is_object($this->_item)) {
            $this->getCategory();
        }

        return $this->_children;
    }

    /**
     * Generate column expression for slug or catslug.
     *
     * @param   \Joomla\Database\DatabaseQuery  $query  Current query instance.
     * @param   string                          $id     Column id name.
     * @param   string                          $alias  Column alias name.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    private function getSlugColumn($query, $id, $alias)
    {
        return 'CASE WHEN '
            . $query->charLength($alias, '!=', '0')
            . ' THEN '
            . $query->concatenate([$query->castAsChar($id), $alias], ':')
            . ' ELSE '
            . $query->castAsChar($id) . ' END';
    }

    /**
     * Increment the hit counter for the category.
     *
     * @param   integer  $pk  Optional primary key of the category to increment.
     *
     * @return  boolean  True if successful; false otherwise and internal error set.
     *
     * @since   3.2
     */
    public function hit($pk = 0)
    {
        $input    = Factory::getApplication()->getInput();
        $hitcount = $input->getInt('hitcount', 1);

        if ($hitcount) {
            $pk = (!empty($pk)) ? $pk : (int) $this->getState('category.id');

            $table = new Category($this->getDatabase());
            $table->hit($pk);
        }

        return true;
    }
}
