<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

/**
 * Featured contact model class.
 *
 * @since  1.6.0
 */
class FeaturedModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since   1.6
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'name', 'a.name',
                'con_position', 'a.con_position',
                'suburb', 'a.suburb',
                'state', 'a.state',
                'country', 'a.country',
                'ordering', 'a.ordering',
            );
        }

        parent::__construct($config);
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

        // Convert the params field into an object, saving original in _params
        for ($i = 0, $n = count($items); $i < $n; $i++) {
            $item = &$items[$i];

            if (!isset($this->_params)) {
                $item->params = new Registry($item->params);
            }
        }

        return $items;
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return  string    An SQL query
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        $user = Factory::getUser();
        $groups = $user->getAuthorisedViewLevels();

        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select required fields from the categories.
        $query->select($this->getState('list.select', 'a.*'))
            ->from($db->quoteName('#__contact_details', 'a'))
            ->where($db->quoteName('a.featured') . ' = 1')
            ->whereIn($db->quoteName('a.access'), $groups)
            ->innerJoin($db->quoteName('#__categories', 'c') . ' ON c.id = a.catid')
            ->whereIn($db->quoteName('c.access'), $groups);

        // Filter by category.
        if ($categoryId = $this->getState('category.id')) {
            $query->where($db->quoteName('a.catid') . ' = :catid');
            $query->bind(':catid', $categoryId, ParameterType::INTEGER);
        }

        $query->select('c.published as cat_published, c.published AS parents_published')
            ->where('c.published = 1');

        // Filter by state
        $state = $this->getState('filter.published');

        if (is_numeric($state)) {
            $query->where($db->quoteName('a.published') . ' = :published');
            $query->bind(':published', $state, ParameterType::INTEGER);

            // Filter by start and end dates.
            $nowDate = Factory::getDate()->toSql();

            $query->where('(' . $db->quoteName('a.publish_up') .
                ' IS NULL OR ' . $db->quoteName('a.publish_up') . ' <= :publish_up)')
                ->where('(' . $db->quoteName('a.publish_down') .
                    ' IS NULL OR ' . $db->quoteName('a.publish_down') . ' >= :publish_down)')
                ->bind(':publish_up', $nowDate)
                ->bind(':publish_down', $nowDate);
        }

        // Filter by search in title
        $search = $this->getState('list.filter');

        // Filter by search in title
        if (!empty($search)) {
            $search = '%' . trim($search) . '%';
            $query->where($db->quoteName('a.name') . ' LIKE :name ');
            $query->bind(':name', $search);
        }

        // Filter by language
        if ($this->getState('filter.language')) {
            $query->whereIn($db->quoteName('a.language'), [Factory::getLanguage()->getTag(), '*'], ParameterType::STRING);
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
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();
        $params = ComponentHelper::getParams('com_contact');

        // List state information
        $limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->get('list_limit'), 'uint');
        $this->setState('list.limit', $limit);

        $limitstart = $app->input->get('limitstart', 0, 'uint');
        $this->setState('list.start', $limitstart);

        // Optional filter text
        $this->setState('list.filter', $app->input->getString('filter-search'));

        $orderCol = $app->input->get('filter_order', 'ordering');

        if (!in_array($orderCol, $this->filter_fields)) {
            $orderCol = 'ordering';
        }

        $this->setState('list.ordering', $orderCol);

        $listOrder = $app->input->get('filter_order_Dir', 'ASC');

        if (!in_array(strtoupper($listOrder), array('ASC', 'DESC', ''))) {
            $listOrder = 'ASC';
        }

        $this->setState('list.direction', $listOrder);

        $user = Factory::getUser();

        if ((!$user->authorise('core.edit.state', 'com_contact')) && (!$user->authorise('core.edit', 'com_contact'))) {
            // Limit to published for people who can't edit or edit.state.
            $this->setState('filter.published', 1);

            // Filter by start and end dates.
            $this->setState('filter.publish_date', true);
        }

        $this->setState('filter.language', Multilanguage::isEnabled());

        // Load the parameters.
        $this->setState('params', $params);
    }
}
