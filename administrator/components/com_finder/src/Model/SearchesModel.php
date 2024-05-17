<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods supporting a list of search terms.
 *
 * @since  4.0.0
 */
class SearchesModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   4.0.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'searchterm', 'a.searchterm',
                'hits', 'a.hits',
            ];
        }

        parent::__construct($config, $factory);
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
     * @since   4.0.0
     */
    protected function populateState($ordering = 'a.hits', $direction = 'asc')
    {
        // Special state for toggle results button.
        $this->setState('show_results', $this->getUserStateFromRequest($this->context . '.show_results', 'show_results', 1, 'int'));

        // Load the parameters.
        $params = ComponentHelper::getParams('com_finder');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);
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
     * @since   4.0.0
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('show_results');
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  QueryInterface
     *
     * @since   4.0.0
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.*'
            )
        );
        $query->from($db->quoteName('#__finder_logging', 'a'));

        // Filter by search in title
        if ($search = $this->getState('filter.search')) {
            $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
            $query->where($db->quoteName('a.searchterm') . ' LIKE ' . $search);
        }

        // Add the list ordering clause.
        $query->order($db->escape($this->getState('list.ordering', 'a.hits')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

        return $query;
    }

    /**
     * Override the parent getItems to inject optional data.
     *
     * @return  mixed  An array of objects on success, false on failure.
     *
     * @since   4.0.0
     */
    public function getItems()
    {
        $items = parent::getItems();

        foreach ($items as $item) {
            if (\is_resource($item->query)) {
                $item->query = unserialize(stream_get_contents($item->query));
            } else {
                $item->query = unserialize($item->query);
            }
        }

        return $items;
    }

    /**
     * Method to reset the search log table.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function reset()
    {
        $db = $this->getDatabase();

        try {
            $db->truncateTable('#__finder_logging');
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return true;
    }
}
