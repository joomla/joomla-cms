<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\Component\Tags\Site\Helper\RouteHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Tags Component Tag Model
 *
 * @since  3.1
 */
class TagModel extends ListModel
{
    /**
     * The tags that apply.
     *
     * @var    object
     * @since  3.1
     */
    protected $tag = null;

    /**
     * The list of items associated with the tags.
     *
     * @var    array
     * @since  3.1
     */
    protected $items = null;

    /**
     * Constructor.
     *
     * @param   array                $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface  $factory  The factory.
     *
     * @since   1.6
     */
    public function __construct($config = array(), MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'core_content_id', 'c.core_content_id',
                'core_title', 'c.core_title',
                'core_type_alias', 'c.core_type_alias',
                'core_checked_out_user_id', 'c.core_checked_out_user_id',
                'core_checked_out_time', 'c.core_checked_out_time',
                'core_catid', 'c.core_catid',
                'core_state', 'c.core_state',
                'core_access', 'c.core_access',
                'core_created_user_id', 'c.core_created_user_id',
                'core_created_time', 'c.core_created_time',
                'core_modified_time', 'c.core_modified_time',
                'core_ordering', 'c.core_ordering',
                'core_featured', 'c.core_featured',
                'core_language', 'c.core_language',
                'core_hits', 'c.core_hits',
                'core_publish_up', 'c.core_publish_up',
                'core_publish_down', 'c.core_publish_down',
                'core_images', 'c.core_images',
                'core_urls', 'c.core_urls',
                'match_count',
            );
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to get a list of items for a list of tags.
     *
     * @return  mixed  An array of objects on success, false on failure.
     *
     * @since   3.1
     */
    public function getItems()
    {
        // Invoke the parent getItems method to get the main list
        $items = parent::getItems();

        if (!empty($items)) {
            foreach ($items as $item) {
                $item->link = RouteHelper::getItemRoute(
                    $item->content_item_id,
                    $item->core_alias,
                    $item->core_catid,
                    $item->core_language,
                    $item->type_alias,
                    $item->router
                );

                // Get display date
                switch ($this->state->params->get('tag_list_show_date')) {
                    case 'modified':
                        $item->displayDate = $item->core_modified_time;
                        break;

                    case 'created':
                        $item->displayDate = $item->core_created_time;
                        break;

                    default:
                        $item->displayDate = ($item->core_publish_up == 0) ? $item->core_created_time : $item->core_publish_up;
                        break;
                }
            }
        }

        return $items;
    }

    /**
     * Method to build an SQL query to load the list data of all items with a given tag.
     *
     * @return  string  An SQL query
     *
     * @since   3.1
     */
    protected function getListQuery()
    {
        $tagId  = $this->getState('tag.id') ? : '';

        $typesr = $this->getState('tag.typesr');
        $orderByOption = $this->getState('list.ordering', 'c.core_title');
        $includeChildren = $this->state->params->get('include_children', 0);
        $orderDir = $this->getState('list.direction', 'ASC');
        $matchAll = $this->getState('params')->get('return_any_or_all', 1);
        $language = $this->getState('tag.language');
        $stateFilter = $this->getState('tag.state');

        // Optionally filter on language
        if (empty($language)) {
            $language = ComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');
        }

        $query = (new TagsHelper())->getTagItemsQuery($tagId, $typesr, $includeChildren, $orderByOption, $orderDir, $matchAll, $language, $stateFilter);

        if ($this->state->get('list.filter')) {
            $db = $this->getDatabase();
            $query->where($db->quoteName('c.core_title') . ' LIKE ' . $db->quote('%' . $this->state->get('list.filter') . '%'));
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
     * @since   3.1
     */
    protected function populateState($ordering = 'c.core_title', $direction = 'ASC')
    {
        $app = Factory::getApplication();

        // Load the parameters.
        $params = $app->isClient('administrator') ? ComponentHelper::getParams('com_tags') : $app->getParams();

        $this->setState('params', $params);

        // Load state from the request.
        $ids = (array) $app->input->get('id', array(), 'string');

        if (count($ids) == 1) {
            $ids = explode(',', $ids[0]);
        }

        $ids = ArrayHelper::toInteger($ids);

        // Remove zero values resulting from bad input
        $ids = array_filter($ids);

        $pkString = implode(',', $ids);

        $this->setState('tag.id', $pkString);

        // Get the selected list of types from the request. If none are specified all are used.
        $typesr = $app->input->get('types', array(), 'array');

        if ($typesr) {
            // Implode is needed because the array can contain a string with a coma separated list of ids
            $typesr = implode(',', $typesr);

            // Sanitise
            $typesr = explode(',', $typesr);
            $typesr = ArrayHelper::toInteger($typesr);

            $this->setState('tag.typesr', $typesr);
        }

        $language = $app->input->getString('tag_list_language_filter');
        $this->setState('tag.language', $language);

        // List state information
        $format = $app->input->getWord('format');

        if ($format === 'feed') {
            $limit = $app->get('feed_limit');
        } else {
            $limit = $params->get('display_num', $app->get('list_limit', 20));
            $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $limit, 'uint');
        }

        $this->setState('list.limit', $limit);

        $offset = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $offset);

        $itemid = $pkString . ':' . $app->input->get('Itemid', 0, 'int');
        $orderCol = $app->getUserStateFromRequest('com_tags.tag.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
        $orderCol = !$orderCol ? $this->state->params->get('tag_list_orderby', 'c.core_title') : $orderCol;

        if (!in_array($orderCol, $this->filter_fields)) {
            $orderCol = 'c.core_title';
        }

        $this->setState('list.ordering', $orderCol);

        $listOrder = $app->getUserStateFromRequest('com_tags.tag.list.' . $itemid . '.filter_order_direction', 'filter_order_Dir', '', 'string');
        $listOrder = !$listOrder ? $this->state->params->get('tag_list_orderby_direction', 'ASC') : $listOrder;

        if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
            $listOrder = 'ASC';
        }

        $this->setState('list.direction', $listOrder);

        $this->setState('tag.state', 1);

        // Optional filter text
        $filterSearch = $app->getUserStateFromRequest('com_tags.tag.list.' . $itemid . '.filter_search', 'filter-search', '', 'string');
        $this->setState('list.filter', $filterSearch);
    }

    /**
     * Method to get tag data for the current tag or tags
     *
     * @param   integer  $pk  An optional ID
     *
     * @return  array
     *
     * @since   3.1
     */
    public function getItem($pk = null)
    {
        if (!isset($this->item)) {
            $this->item = [];

            if (empty($pk)) {
                $pk = $this->getState('tag.id');
            }

            // Get a level row instance.
            /** @var \Joomla\Component\Tags\Administrator\Table\TagTable $table */
            $table = $this->getTable();

            $idsArray = explode(',', $pk);

            // Attempt to load the rows into an array.
            foreach ($idsArray as $id) {
                try {
                    $table->load($id);

                    // Check published state.
                    if ($published = $this->getState('tag.state')) {
                        if ($table->published != $published) {
                            continue;
                        }
                    }

                    if (!in_array($table->access, Factory::getUser()->getAuthorisedViewLevels())) {
                        continue;
                    }

                    // Convert the Table to a clean CMSObject.
                    $properties = $table->getProperties(1);
                    $this->item[] = ArrayHelper::toObject($properties, CMSObject::class);
                } catch (\RuntimeException $e) {
                    $this->setError($e->getMessage());

                    return false;
                }
            }
        }

        if (!$this->item) {
            throw new \Exception(Text::_('COM_TAGS_TAG_NOT_FOUND'), 404);
        }

        return $this->item;
    }

    /**
     * Increment the hit counter.
     *
     * @param   integer  $pk  Optional primary key of the article to increment.
     *
     * @return  boolean  True if successful; false otherwise and internal error set.
     *
     * @since   3.2
     */
    public function hit($pk = 0)
    {
        $input    = Factory::getApplication()->input;
        $hitcount = $input->getInt('hitcount', 1);

        if ($hitcount) {
            $pk    = (!empty($pk)) ? $pk : (int) $this->getState('tag.id');

            /** @var \Joomla\Component\Tags\Administrator\Table\TagTable $table */
            $table = $this->getTable();
            $table->hit($pk);

            // Load the table data for later
            $table->load($pk);

            if (!$table->hasPrimaryKey()) {
                throw new \Exception(Text::_('COM_TAGS_TAG_NOT_FOUND'), 404);
            }
        }

        return true;
    }
}
