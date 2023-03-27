<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0.0
 */

namespace Joomla\Component\Workflow\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for workflows
 *
 * @since  4.0.0
 */
class WorkflowsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JController
     * @since  4.0.0
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'w.id',
                'title', 'w.title',
                'published', 'w.published',
                'created_by', 'w.created_by',
                'created', 'w.created',
                'ordering', 'w.ordering',
                'modified', 'w.modified',
                'description', 'w.description',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function populateState($ordering = 'w.ordering', $direction = 'asc')
    {
        $app       = Factory::getApplication();
        $extension = $app->getUserStateFromRequest($this->context . '.filter.extension', 'extension', null, 'cmd');

        $this->setState('filter.extension', $extension);
        $parts = explode('.', $extension);

        // Extract the component name
        $this->setState('filter.component', $parts[0]);

        // Extract the optional section name
        $this->setState('filter.section', (count($parts) > 1) ? $parts[1] : null);

        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\Table\Table  A Table object
     *
     * @since  4.0.0
     */
    public function getTable($type = 'Workflow', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since  4.0.0
     */
    public function getItems()
    {
        $items = parent::getItems();

        if ($items) {
            $this->countItems($items);
        }

        return $items;
    }

    /**
     * Get the filter form
     *
     * @param   array    $data      data
     * @param   boolean  $loadData  load current data
     *
     * @return  \Joomla\CMS\Form\Form|bool the Form object or false
     *
     * @since   4.0.0
     */
    public function getFilterForm($data = [], $loadData = true)
    {
        $form = parent::getFilterForm($data, $loadData);

        if ($form) {
            $form->setValue('extension', null, $this->getState('filter.extension'));
        }

        return $form;
    }

    /**
     * Add the number of transitions and states to all workflow items
     *
     * @param   array  $items  The workflow items
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since  4.0.0
     */
    protected function countItems($items)
    {
        $db = $this->getDatabase();

        $ids = [0];

        foreach ($items as $item) {
            $ids[] = (int) $item->id;

            $item->count_states      = 0;
            $item->count_transitions = 0;
        }

        $query = $db->getQuery(true);

        $query->select(
            [
                $db->quoteName('workflow_id'),
                'COUNT(*) AS ' . $db->quoteName('count'),
            ]
        )
            ->from($db->quoteName('#__workflow_stages'))
            ->whereIn($db->quoteName('workflow_id'), $ids)
            ->where($db->quoteName('published') . ' >= 0')
            ->group($db->quoteName('workflow_id'));

        $status = $db->setQuery($query)->loadObjectList('workflow_id');

        $query = $db->getQuery(true);

        $query->select(
            [
                $db->quoteName('workflow_id'),
                'COUNT(*) AS ' . $db->quoteName('count'),
            ]
        )
            ->from($db->quoteName('#__workflow_transitions'))
            ->whereIn($db->quoteName('workflow_id'), $ids)
            ->where($db->quoteName('published') . ' >= 0')
            ->group($db->quoteName('workflow_id'));

        $transitions = $db->setQuery($query)->loadObjectList('workflow_id');

        foreach ($items as $item) {
            if (isset($status[$item->id])) {
                $item->count_states = (int) $status[$item->id]->count;
            }

            if (isset($transitions[$item->id])) {
                $item->count_transitions = (int) $transitions[$item->id]->count;
            }
        }
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  DatabaseQuery  The query to database.
     *
     * @since  4.0.0
     */
    public function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select(
            [
                $db->quoteName('w.id'),
                $db->quoteName('w.title'),
                $db->quoteName('w.created'),
                $db->quoteName('w.modified'),
                $db->quoteName('w.published'),
                $db->quoteName('w.checked_out'),
                $db->quoteName('w.checked_out_time'),
                $db->quoteName('w.ordering'),
                $db->quoteName('w.default'),
                $db->quoteName('w.created_by'),
                $db->quoteName('w.description'),
                $db->quoteName('u.name'),
                $db->quoteName('uc.name', 'editor'),
            ]
        )
            ->from($db->quoteName('#__workflows', 'w'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('w.created_by'))
            ->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('w.checked_out'));

        // Filter by extension
        if ($extension = $this->getState('filter.extension')) {
            $query->where($db->quoteName('extension') . ' = :extension')
                ->bind(':extension', $extension);
        }

        $status = (string) $this->getState('filter.published');

        // Filter by status
        if (is_numeric($status)) {
            $status = (int) $status;
            $query->where($db->quoteName('w.published') . ' = :published')
                ->bind(':published', $status, ParameterType::INTEGER);
        } elseif ($status === '') {
            $query->where($db->quoteName('w.published') . ' IN (0, 1)');
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            $search = '%' . str_replace(' ', '%', trim($search)) . '%';
            $query->where('(' . $db->quoteName('w.title') . ' LIKE :search1 OR ' . $db->quoteName('w.description') . ' LIKE :search2)')
                ->bind([':search1', ':search2'], $search);
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'w.ordering');
        $orderDirn = strtoupper($this->state->get('list.direction', 'ASC'));

        $query->order($db->escape($orderCol) . ' ' . ($orderDirn === 'DESC' ? 'DESC' : 'ASC'));

        return $query;
    }
}
