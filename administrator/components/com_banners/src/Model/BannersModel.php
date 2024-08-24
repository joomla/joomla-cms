<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods supporting a list of banner records.
 *
 * @since  1.6
 */
class BannersModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since   1.6
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'cid', 'a.cid', 'client_name',
                'name', 'a.name',
                'alias', 'a.alias',
                'state', 'a.state',
                'ordering', 'a.ordering',
                'language', 'a.language',
                'catid', 'a.catid', 'category_title',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'created', 'a.created',
                'impmade', 'a.impmade',
                'imptotal', 'a.imptotal',
                'clicks', 'a.clicks',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down',
                'sticky', 'a.sticky',
                'client_id',
                'category_id',
                'published',
                'level', 'c.level',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to get the maximum ordering value for each category.
     *
     * @return  array
     *
     * @since   1.6
     */
    public function &getCategoryOrders()
    {
        if (!isset($this->cache['categoryorders'])) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select(
                    [
                        'MAX(' . $db->quoteName('ordering') . ') AS ' . $db->quoteName('max'),
                        $db->quoteName('catid'),
                    ]
                )
                ->from($db->quoteName('#__banners'))
                ->group($db->quoteName('catid'));
            $db->setQuery($query);
            $this->cache['categoryorders'] = $db->loadAssocList('catid', 0);
        }

        return $this->cache['categoryorders'];
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
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.name'),
                    $db->quoteName('a.alias'),
                    $db->quoteName('a.checked_out'),
                    $db->quoteName('a.checked_out_time'),
                    $db->quoteName('a.catid'),
                    $db->quoteName('a.clicks'),
                    $db->quoteName('a.metakey'),
                    $db->quoteName('a.sticky'),
                    $db->quoteName('a.impmade'),
                    $db->quoteName('a.imptotal'),
                    $db->quoteName('a.state'),
                    $db->quoteName('a.ordering'),
                    $db->quoteName('a.purchase_type'),
                    $db->quoteName('a.language'),
                    $db->quoteName('a.publish_up'),
                    $db->quoteName('a.publish_down'),
                ]
            )
        )
            ->select(
                [
                    $db->quoteName('l.title', 'language_title'),
                    $db->quoteName('l.image', 'language_image'),
                    $db->quoteName('uc.name', 'editor'),
                    $db->quoteName('c.title', 'category_title'),
                    $db->quoteName('cl.name', 'client_name'),
                    $db->quoteName('cl.purchase_type', 'client_purchase_type'),
                ]
            )
            ->from($db->quoteName('#__banners', 'a'))
            ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'))
            ->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'))
            ->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('a.catid'))
            ->join('LEFT', $db->quoteName('#__banner_clients', 'cl'), $db->quoteName('cl.id') . ' = ' . $db->quoteName('a.cid'));

        // Filter by published state
        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $published = (int) $published;
            $query->where($db->quoteName('a.state') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->where($db->quoteName('a.state') . ' IN (0, 1)');
        }

        // Filter by category.
        $categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId)) {
            $categoryId = (int) $categoryId;
            $query->where($db->quoteName('a.catid') . ' = :categoryId')
                ->bind(':categoryId', $categoryId, ParameterType::INTEGER);
        }

        // Filter by client.
        $clientId = $this->getState('filter.client_id');

        if (is_numeric($clientId)) {
            $clientId = (int) $clientId;
            $query->where($db->quoteName('a.cid') . ' = :clientId')
                ->bind(':clientId', $clientId, ParameterType::INTEGER);
        }

        // Filter by search in title
        if ($search = $this->getState('filter.search')) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :search')
                    ->bind(':search', $search, ParameterType::INTEGER);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where('(' . $db->quoteName('a.name') . ' LIKE :search1 OR ' . $db->quoteName('a.alias') . ' LIKE :search2)')
                    ->bind([':search1', ':search2'], $search);
            }
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' = :language')
                ->bind(':language', $language);
        }

        // Filter on the level.
        if ($level = (int) $this->getState('filter.level')) {
            $query->where($db->quoteName('c.level') . ' <= :level')
                ->bind(':level', $level, ParameterType::INTEGER);
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.name');
        $orderDirn = $this->state->get('list.direction', 'ASC');

        if ($orderCol === 'a.ordering' || $orderCol === 'category_title') {
            $ordering = [
                $db->quoteName('c.title') . ' ' . $db->escape($orderDirn),
                $db->quoteName('a.ordering') . ' ' . $db->escape($orderDirn),
            ];
        } else {
            if ($orderCol === 'client_name') {
                $orderCol = 'cl.name';
            }

            $ordering = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
        }

        $query->order($ordering);

        return $query;
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
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.category_id');
        $id .= ':' . $this->getState('filter.client_id');
        $id .= ':' . $this->getState('filter.language');
        $id .= ':' . $this->getState('filter.level');

        return parent::getStoreId($id);
    }

    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param   string  $type    The table type to instantiate
     * @param   string  $prefix  A prefix for the table class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @since   1.6
     */
    public function getTable($type = 'Banner', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
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
    protected function populateState($ordering = 'a.name', $direction = 'asc')
    {
        // Load the parameters.
        $this->setState('params', ComponentHelper::getParams('com_banners'));

        // List state information.
        parent::populateState($ordering, $direction);
    }
}
