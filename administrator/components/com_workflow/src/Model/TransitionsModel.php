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
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for transitions
 *
 * @since  4.0.0
 */
class TransitionsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @see     JController
     * @since  4.0.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 't.id',
                'published', 't.published',
                'ordering', 't.ordering',
                'title', 't.title',
                'from_stage', 't.from_stage_id',
                'to_stage', 't.to_stage_id',
            ];
        }

        parent::__construct($config, $factory);
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
    protected function populateState($ordering = 't.ordering', $direction = 'ASC')
    {
        $app        = Factory::getApplication();
        $workflowID = $app->getUserStateFromRequest($this->context . '.filter.workflow_id', 'workflow_id', 1, 'int');
        $extension  = $app->getUserStateFromRequest($this->context . '.filter.extension', 'extension', null, 'cmd');

        if ($workflowID) {
            $table = $this->getTable('Workflow', 'Administrator');

            if ($table->load($workflowID)) {
                $this->setState('active_workflow', $table->title);
            }
        }

        $this->setState('filter.workflow_id', $workflowID);
        $this->setState('filter.extension', $extension);

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
    public function getTable($type = 'Transition', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param   object  $table  A record object.
     *
     * @return  array  An array of conditions to add to ordering queries.
     *
     * @since   4.0.0
     */
    protected function getReorderConditions($table)
    {
        return [
            $this->getDatabase()->quoteName('workflow_id') . ' = ' . (int) $table->workflow_id,
        ];
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  QueryInterface  The query to database.
     *
     * @since  4.0.0
     */
    public function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query
            ->select(
                [
                    $db->quoteName('t.id'),
                    $db->quoteName('t.title'),
                    $db->quoteName('t.from_stage_id'),
                    $db->quoteName('t.to_stage_id'),
                    $db->quoteName('t.published'),
                    $db->quoteName('t.checked_out'),
                    $db->quoteName('t.checked_out_time'),
                    $db->quoteName('t.ordering'),
                    $db->quoteName('t.description'),
                    $db->quoteName('f_stage.title', 'from_stage'),
                    $db->quoteName('t_stage.title', 'to_stage'),
                    $db->quoteName('uc.name', 'editor'),
                ]
            )
            ->from($db->quoteName('#__workflow_transitions', 't'))
            ->join('LEFT', $db->quoteName('#__workflow_stages', 'f_stage'), $db->quoteName('f_stage.id') . ' = ' . $db->quoteName('t.from_stage_id'))
            ->join('LEFT', $db->quoteName('#__workflow_stages', 't_stage'), $db->quoteName('t_stage.id') . ' = ' . $db->quoteName('t.to_stage_id'))
            ->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('t.checked_out'));

        // Filter by extension
        if ($workflowID = (int) $this->getState('filter.workflow_id')) {
            $query->where($db->quoteName('t.workflow_id') . ' = :id')
                ->bind(':id', $workflowID, ParameterType::INTEGER);
        }

        $status = (string) $this->getState('filter.published');

        // Filter by status
        if (is_numeric($status)) {
            $status = (int) $status;
            $query->where($db->quoteName('t.published') . ' = :status')
                ->bind(':status', $status, ParameterType::INTEGER);
        } elseif ($status === '') {
            $query->where($db->quoteName('t.published') . ' IN (0, 1)');
        }

        // Filter by column from_stage_id
        if ($fromStage = (int) $this->getState('filter.from_stage')) {
            $query->where($db->quoteName('from_stage_id') . ' = :fromStage')
                ->bind(':fromStage', $fromStage, ParameterType::INTEGER);
        }

        // Filter by column to_stage_id
        if ($toStage = (int) $this->getState('filter.to_stage')) {
            $query->where($db->quoteName('to_stage_id') . ' = :toStage')
                ->bind(':toStage', $toStage, ParameterType::INTEGER);
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            $search = '%' . str_replace(' ', '%', trim($search)) . '%';
            $query->where('(' . $db->quoteName('t.title') . ' LIKE :search1 OR ' . $db->quoteName('t.description') . ' LIKE :search2)')
                ->bind([':search1', ':search2'], $search);
        }

        // Add the list ordering clause.
        $orderCol   = $this->state->get('list.ordering', 't.id');
        $orderDirn  = strtoupper($this->state->get('list.direction', 'ASC'));

        $query->order($db->escape($orderCol) . ' ' . ($orderDirn === 'DESC' ? 'DESC' : 'ASC'));

        return $query;
    }

    /**
     * Get the filter form
     *
     * @param   array    $data      data
     * @param   boolean  $loadData  load current data
     *
     * @return  \Joomla\CMS\Form\Form|boolean The Form object or false on error
     *
     * @since   4.0.0
     */
    public function getFilterForm($data = [], $loadData = true)
    {
        $form = parent::getFilterForm($data, $loadData);

        $id = (int) $this->getState('filter.workflow_id');

        if ($form) {
            $where = $this->getDatabase()->quoteName('workflow_id') . ' = ' . $id . ' AND ' . $this->getDatabase()->quoteName('published') . ' = 1';

            $form->setFieldAttribute('from_stage', 'sql_where', $where, 'filter');
            $form->setFieldAttribute('to_stage', 'sql_where', $where, 'filter');
        }

        return $form;
    }

    /**
     * Returns a workflow object
     *
     * @return  object  The workflow
     *
     * @since  4.0.0
     */
    public function getWorkflow()
    {
        $table = $this->getTable('Workflow', 'Administrator');

        $workflowId = (int) $this->getState('filter.workflow_id');

        if ($workflowId > 0) {
            $table->load($workflowId);
        }

        return (object) $table->getProperties();
    }
}
