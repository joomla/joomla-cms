<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Workflow\Administrator\Automation\UpcomingTransitionsCalculator;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model for the extension-wide upcoming automated transitions view.
 *
 * A list model for the paging and the filter bar, but the rows are computed rather than queried,
 * so getItems() and getTotal() do their own work instead of going through getListQuery().
 *
 * @since  __DEPLOY_VERSION__
 */
class UpcomingModel extends ListModel
{
    /**
     * Shared by the page and the count, so both see one instance.
     *
     * @var    UpcomingTransitionsCalculator|null
     * @since  __DEPLOY_VERSION__
     */
    private ?UpcomingTransitionsCalculator $calculator = null;

    /**
     * Auto-populate the model state from the request.
     *
     * @param   string  $ordering   Unused: the order is fixed, attention first then longest waiting.
     * @param   string  $direction  Unused, for the same reason.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function populateState($ordering = null, $direction = null)
    {
        $extension = Factory::getApplication()->getUserStateFromRequest(
            $this->context . '.filter.extension',
            'extension',
            'com_content.article',
            'cmd'
        );

        $this->setState('filter.extension', $extension);

        // list.limit, list.start, list.workflow_id and list.stage_id all arrive through the parent.
        parent::populateState($ordering, $direction);
    }

    /**
     * The filter form, with its workflow and stage lists narrowed to what can be chosen.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  Whether to load the form data.
     *
     * @return  Form|null
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getFilterForm($data = [], $loadData = true)
    {
        $form = parent::getFilterForm($data, $loadData);

        if ($form instanceof Form) {
            $db         = $this->getDatabase();
            $extension  = $db->quote((string) $this->getState('filter.extension'));
            $workflowId = (int) $this->getState('list.workflow_id');

            // Narrowed here rather than in the XML, because the extension is only known at runtime.
            $form->setFieldAttribute(
                'workflow_id',
                'sql_where',
                $db->quoteName('published') . ' = 1 AND ' . $db->quoteName('extension') . ' = ' . $extension,
                'list'
            );

            // The chosen workflow's stages, or every stage in the extension while no workflow is chosen.
            $form->setFieldAttribute(
                'stage_id',
                'sql_where',
                $db->quoteName('s.published') . ' = 1 AND ' . $db->quoteName('w.extension') . ' = ' . $extension
                    . ($workflowId > 0 ? ' AND ' . $db->quoteName('s.workflow_id') . ' = ' . $workflowId : ''),
                'list'
            );
        }

        return $form;
    }

    /**
     * Returns one page of upcoming automated transitions.
     *
     * @return  \Joomla\Component\Workflow\Administrator\Automation\UpcomingTransition[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getItems(): array
    {
        $extension = (string) $this->getState('filter.extension');

        if ($extension === '') {
            return [];
        }

        $limit      = (int) $this->getState('list.limit');
        $start      = (int) $this->getStart();
        $workflowId = (int) $this->getState('list.workflow_id');
        $stageId    = $this->getStageFilter();

        return $workflowId > 0
            ? $this->getCalculator()->forWorkflow($workflowId, $limit, $start, $stageId)
            : $this->getCalculator()->forExtension($extension, $limit, $start, $stageId);
    }

    /**
     * The number of items the list can page through.
     *
     * @return  integer
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getTotal(): int
    {
        $extension = (string) $this->getState('filter.extension');

        if ($extension === '') {
            return 0;
        }

        $workflowId = (int) $this->getState('list.workflow_id');
        $stageId    = $this->getStageFilter();

        return $workflowId > 0
            ? $this->getCalculator()->countForWorkflow($workflowId, $stageId)
            : $this->getCalculator()->countForExtension($extension, $stageId);
    }

    /**
     * The stage to filter by, or 0 for any stage.
     *
     * A stage that does not belong to the chosen workflow is ignored. It is left behind when the
     * workflow filter changes, and the stage list no longer offers it, so honouring it would show an
     * empty list with nothing on screen to explain why.
     *
     * @return  integer
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getStageFilter(): int
    {
        $stageId    = (int) $this->getState('list.stage_id');
        $workflowId = (int) $this->getState('list.workflow_id');

        if ($stageId <= 0 || $workflowId <= 0) {
            return max($stageId, 0);
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__workflow_stages'))
            ->where($db->quoteName('id') . ' = :stageId')
            ->where($db->quoteName('workflow_id') . ' = :workflowId')
            ->bind(':stageId', $stageId, ParameterType::INTEGER)
            ->bind(':workflowId', $workflowId, ParameterType::INTEGER);

        return $db->setQuery($query)->loadResult() ? $stageId : 0;
    }

    /**
     * @return  UpcomingTransitionsCalculator
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getCalculator(): UpcomingTransitionsCalculator
    {
        return $this->calculator ??= new UpcomingTransitionsCalculator($this->getDatabase());
    }
}
