<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Site\Model;

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
 * Newsfeeds Component Category Model
 *
 * @since  1.5
 */
class CategoryModel extends ListModel
{
    /**
     * Category items data
     *
     * @var array
     */
    protected $_item;

    /**
     * Array of newsfeeds in the category
     *
     * @var    \stdClass[]
     */
    protected $_articles;

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
     * The list of other newsfeed categories.
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
     * @see    \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.2
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'name', 'a.name',
                'numarticles', 'a.numarticles',
                'link', 'a.link',
                'ordering', 'a.ordering',
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

            foreach ($tagsHelper->getMultipleItemTags('com_newsfeeds.newsfeed', $itemIds) as $id => $tags) {
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

        $query = $db->getQuery(true);

        // Select required fields from the categories.
        $query->select($this->getState('list.select', $db->quoteName('a') . '.*'))
            ->from($db->quoteName('#__newsfeeds', 'a'))
            ->whereIn($db->quoteName('a.access'), $groups);

        // Filter by category.
        if ($categoryId = (int) $this->getState('category.id')) {
            $query->where($db->quoteName('a.catid') . ' = :categoryId')
                ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
                ->whereIn($db->quoteName('c.access'), $groups)
                ->bind(':categoryId', $categoryId, ParameterType::INTEGER);
        }

        // Filter by state
        $state = $this->getState('filter.published');

        if (is_numeric($state)) {
            $state = (int) $state;
            $query->where($db->quoteName('a.published') . ' = :state')
                ->bind(':state', $state, ParameterType::INTEGER);
        } else {
            $query->where($db->quoteName('a.published') . ' IN (0,1,2)');
        }

        // Filter by start and end dates.
        if ($this->getState('filter.publish_date')) {
            $nowDate = Factory::getDate()->toSql();

            $query->extendWhere(
                'AND',
                [
                    $db->quoteName('a.publish_up') . ' IS NULL',
                    $db->quoteName('a.publish_up') . ' <= :nowDate1',
                ],
                'OR'
            )
                ->extendWhere(
                    'AND',
                    [
                        $db->quoteName('a.publish_down') . ' IS NULL',
                        $db->quoteName('a.publish_down') . ' >= :nowDate2',
                    ],
                    'OR'
                )
                ->bind([':nowDate1', ':nowDate2'], $nowDate);
        }

        // Filter by search in title
        if ($search = $this->getState('list.filter')) {
            $search = '%' . $search . '%';
            $query->where($db->quoteName('a.name') . ' LIKE :search')
                ->bind(':search', $search);
        }

        // Filter by language
        if ($this->getState('filter.language')) {
            $query->whereIn($db->quoteName('a.language'), [Factory::getApplication()->getLanguage()->getTag(), '*'], ParameterType::STRING);
        }

        // Add the list ordering clause.
        $query->order($db->escape($this->getState('list.ordering', 'a.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

        return $query;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field
     * @param   string  $direction  An optional direction [asc|desc]
     *
     * @return void
     *
     * @since   1.6
     *
     * @throws \Exception
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();

        $params = $app->getParams();
        $this->setState('params', $params);

        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'uint');
        $this->setState('list.limit', $limit);

        $limitstart = $input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $limitstart);

        // Optional filter text
        $this->setState('list.filter', $input->getString('filter-search'));

        $orderCol = $input->get('filter_order', 'ordering');

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

        $user = $this->getCurrentUser();

        if ((!$user->authorise('core.edit.state', 'com_newsfeeds')) && (!$user->authorise('core.edit', 'com_newsfeeds'))) {
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
     * @return  object
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
            $categories            = Categories::getInstance('Newsfeeds', $options);
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
     * Increment the hit counter for the category.
     *
     * @param   int  $pk  Optional primary key of the category to increment.
     *
     * @return  boolean True if successful; false otherwise and internal error set.
     */
    public function hit($pk = 0)
    {
        $input    = Factory::getApplication()->getInput();
        $hitcount = $input->getInt('hitcount', 1);

        if ($hitcount) {
            $pk    = (!empty($pk)) ? $pk : (int) $this->getState('category.id');
            $table = new Category($this->getDatabase());
            $table->hit($pk);
        }

        return true;
    }
}
