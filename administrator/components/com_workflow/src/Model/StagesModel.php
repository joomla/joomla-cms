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
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model class for stages
 *
 * @since  4.0.0
 */
class StagesModel extends ListModel
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
                'id', 's.id',
                'title', 's.title',
                'ordering','s.ordering',
                'published', 's.published',
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
    protected function populateState($ordering = 's.ordering', $direction = 'ASC')
    {
        $app = Factory::getApplication();

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
    public function getTable($type = 'Stage', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
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
                    $db->quoteName('s.id'),
                    $db->quoteName('s.title'),
                    $db->quoteName('s.ordering'),
                    $db->quoteName('s.default'),
                    $db->quoteName('s.published'),
                    $db->quoteName('s.checked_out'),
                    $db->quoteName('s.checked_out_time'),
                    $db->quoteName('s.description'),
                    $db->quoteName('uc.name', 'editor'),
                ]
            )
            ->from($db->quoteName('#__workflow_stages', 's'))
            ->join('LEFT', $db->quoteName('#__users', 'uc'), $db->quoteName('uc.id') . ' = ' . $db->quoteName('s.checked_out'));

        // Filter by extension
        if ($workflowID = (int) $this->getState('filter.workflow_id')) {
            $query->where($db->quoteName('s.workflow_id') . ' = :id')
                ->bind(':id', $workflowID, ParameterType::INTEGER);
        }

        $status = (string) $this->getState('filter.published');

        // Filter by publish state
        if (is_numeric($status)) {
            $status = (int) $status;
            $query->where($db->quoteName('s.published') . ' = :status')
                ->bind(':status', $status, ParameterType::INTEGER);
        } elseif ($status === '') {
            $query->where($db->quoteName('s.published') . ' IN (0, 1)');
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            $search = '%' . str_replace(' ', '%', trim($search)) . '%';
            $query->where('(' . $db->quoteName('s.title') . ' LIKE :search1 OR ' . $db->quoteName('s.description') . ' LIKE :search2)')
                ->bind([':search1', ':search2'], $search);
        }

        // Add the list ordering clause.
        $query->order($db->escape($this->getState('list.ordering', 's.ordering')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

        return $query;
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
