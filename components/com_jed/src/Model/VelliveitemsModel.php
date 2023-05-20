<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Site\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\QueryInterface;

use function defined;

/**
 * VEL Live Items Model Class.
 *
 * @since 4.0.0
 */
class VelliveitemsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see        JController
     * @since      4.0.0
     * @throws Exception
     */
    public function __construct($config = [])
    {

        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'title', 'a.title',
                'publication_date_sort', 'publication_date_sort',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to get an array of data items
     *
     * @return  mixed An array of data on success, false on failure.
     *
     * @since 4.0.0
     */
    public function getItems(): mixed
    {
        $items = parent::getItems();
        foreach ($items as $item) {
            // Always create a slug for sef URL's
            //  $item->slug = (isset($item->alias) && isset($item->id)) ? $item->id.':'.$item->alias : $item->id;
            $item->title              = JedHelper::reformatTitle($item->title);
            $item->public_description = JedHelper::reformatTitle($item->public_description);
            // Always create a slug for sef URL's
            $item->slug = (isset($item->alias) && isset($item->id)) ? $item->id . ':' . $item->alias : $item->id;
        }

        return $items;
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return QueryInterface
     *
     * @since 4.0.0
     */
    protected function getListQuery(): QueryInterface
    {

        $db    =  Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);


        $query->select("id,`title` AS `title`, `alias` AS `alias`, `public_description` as `public_description`, `state` AS `published`, IF((`modified` > `created`),DATE_FORMAT(`modified`,'%d %M %Y'),DATE_FORMAT(`created`,'%d %M %Y')) AS `publication_date`,IF((`modified` > `created`),`modified`,`created`) AS `publication_date_sort`");

        $query->from($db->qn('#__jed_vel_vulnerable_item', 'a'));

        $query->where('a.status = 1 AND a.state=1'); //Status 0 = reported, 1 = live, 2 = patched
        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
                $query->where('( a.title LIKE ' . $search . ' )');
            }
        }
        $query->order($db->escape($this->getState('list.ordering', 'publication_date_sort')) . ' ' .
            $db->escape($this->getState('list.direction', 'DESC')));


        return $query;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   Elements order
     * @param   string  $direction  Order direction
     *
     * @return void
     *
     * @since 4.0.0
     * @throws Exception
     *
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $app = Factory::getApplication();

        $list = $app->getUserState($this->context . '.list');

        $ordering  = $list['filter_order'] ?? null;
        $direction = $list['filter_order_Dir'] ?? null;
        if (empty($ordering)) {
            $ordering = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', $app->get('filter_order'));
            if (!in_array($ordering, $this->filter_fields)) {
                $ordering = "publication_date_sort";
            }
            $this->setState('list.ordering', $ordering);
        }
        if (empty($direction)) {
            $direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', $app->get('filter_order_Dir'));
            if (!in_array(strtoupper($direction), ['ASC', 'DESC', ''])) {
                $direction = "DESC";
            }
            $this->setState('list.direction', $direction);
        }

        $list['limit']     = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'uint');
        $list['start']     = $app->input->getInt('start', 0);
        $list['ordering']  = $ordering;
        $list['direction'] = $direction;

        $app->setUserState($this->context . '.list', $list);
        $app->input->set('list', null);


        // List state information.

        parent::populateState($ordering, $direction);

        $context = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $context);


        // Split context into component and optional section
        $parts = FieldsHelper::extract($context);

        if ($parts) {
            $this->setState('filter.component', $parts[0]);
            $this->setState('filter.section', $parts[1]);
        }
    }
}
