<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Controller;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * The workflow Graphical View and Api controller
 *
 * @since __DEPLOY_VERSION__
 */
class GraphController extends AdminController
{
    /**
     * Present workflow id
     *
     * @var    integer
     * @since  __DEPLOY_VERSION__
     */
    protected $workflowId;

    /**
     * The extension
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $extension;

    /**
     * The component name
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $component;

    /**
     * The section of the current extension
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $section;

    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $text_prefix = 'COM_WORKFLOW_GRAPH';


    public function __construct($config = [], ?MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        $this->registerTask('trash', 'publish');

        // If workflow id is not set try to get it from input or throw an exception
        if (empty($this->workflowId)) {
            $this->workflowId = $this->input->getInt('id');
            if (empty($this->workflowId)) {
                $this->workflowId = $this->input->getInt('workflow_id');
            }

            if (empty($this->workflowId)) {
                throw new \InvalidArgumentException(Text::_('COM_WORKFLOW_GRAPH_ERROR_WORKFLOW_ID_NOT_SET'));
            }
        }

        // If extension is not set try to get it from input or throw an exception
        if (empty($this->extension)) {
            $extension = $this->input->getCmd('extension');

            $parts = explode('.', $extension);

            $this->component = reset($parts);

            $this->extension = array_shift($parts);

            if (!empty($parts)) {
                $this->section = array_shift($parts);
            }

            if (empty($this->extension)) {
                throw new \InvalidArgumentException(Text::_('COM_WORKFLOW_GRAPH_ERROR_EXTENSION_NOT_SET'));
            }
        }
    }


    /**
     * Retrieves workflow data for graphical display in the workflow graph view.
     *
     * This method fetches the workflow details by ID, checks user permissions,
     * and returns the workflow information as a JSON response for use in the
     * graphical workflow editor or API consumers.
     *
     * @return  void  Outputs a JSON response with workflow data or error message.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getWorkflow(): void
    {

        try {
            $id    = $this->workflowId;
            $model = $this->getModel('Workflow');

            if (empty($id)) {
                throw new \InvalidArgumentException(Text::_('COM_WORKFLOW_GRAPH_ERROR_INVALID_ID'));
            }

            $workflow = $model->getItem($id);

            if (empty($workflow->id)) {
                throw new \RuntimeException(Text::_('COM_WORKFLOW_GRAPH_ERROR_WORKFLOW_NOT_FOUND'));
            }

            // Check permissions
            if (!$this->app->getIdentity()->authorise('core.edit', $this->extension . '.workflow.' . $id)) {
                throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'));
            }

            $canDo        = ContentHelper::getActions($this->extension, 'workflow', $workflow->id);
            $canCreate    = $canDo->get('core.create');

            $response = [
                'id'          => $workflow->id,
                'title'       => Text::_($workflow->title),
                'description' => Text::_($workflow->description),
                'published'   => (bool) $workflow->published,
                'default'     => (bool) $workflow->default,
                'extension'   => $workflow->extension,
                'canCreate'   => $canCreate,
            ];

            echo new JsonResponse($response);
        } catch (\Exception $e) {
            $this->app->setHeader('status', 500);
            echo new JsonResponse($e->getMessage(), 'error', true);
        }

        $this->app->close();
    }

    /**
     * Retrieves all stages for the specified workflow to be used in the workflow graph view.
     *
     * Fetches stages by workflow ID, decodes position data if available, and returns
     * the result as a JSON response for graphical editors or API consumers.
     *
     * @return  void  Outputs a JSON response with stages data or error message.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getStages()
    {
        try {
            $workflowId = $this->workflowId;
            $model      = $this->getModel('Stages');

            if (empty($workflowId)) {
                throw new \InvalidArgumentException(Text::_('COM_WORKFLOW_GRAPH_ERROR_INVALID_ID'));
            }

            $model->setState('filter.workflow_id', $workflowId);
            $model->setState('list.limit', 0); // Get all stages

            $stages   = $model->getItems();

            if (empty($stages)) {
                throw new \RuntimeException(Text::_('COM_WORKFLOW_GRAPH_ERROR_STAGES_NOT_FOUND'));
            }

            $response = [];
            $user     = $this->app->getIdentity();

            foreach ($stages as $stage) {
                $canEdit    = $user->authorise('core.edit', $this->extension . '.stage.' . $stage->id);
                $canDelete  = $user->authorise('core.delete', $this->extension . '.stage.' . $stage->id);

                $response[] = [
                    'id'          => (int) $stage->id,
                    'title'       => Text::_($stage->title),
                    'description' => Text::_($stage->description),
                    'published'   => (bool) $stage->published,
                    'default'     => (bool) $stage->default,
                    'ordering'    => (int) $stage->ordering,
                    'position'    => $stage->position ? json_decode($stage->position, true) : null,
                    'workflow_id' => $stage->workflow_id,
                    'permissions' => [
                        'edit'   => $canEdit,
                        'delete' => $canDelete,
                    ],
                ];
            }
            echo new JsonResponse($response);
        } catch (\Exception $e) {
            $this->app->setHeader('status', 500);
            echo new JsonResponse($e->getMessage(), 'error', true);
        }

        $this->app->close();
    }


    /**
     * Retrieves all transitions for the specified workflow to be used in the workflow graph view.
     *
     * Fetches transitions by workflow ID and returns the result as a JSON response
     * for graphical editors or API consumers.
     *
     * @return  void  Outputs a JSON response with transitions data or error message.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getTransitions()
    {

        try {
            $workflowId = $this->workflowId;
            $model      = $this->getModel('Transitions');

            if (empty($workflowId)) {
                throw new \InvalidArgumentException(Text::_('COM_WORKFLOW_GRAPH_ERROR_INVALID_ID'));
            }

            $model->setState('filter.workflow_id', $workflowId);
            $model->setState('list.limit', 0);

            $transitions = $model->getItems();

            $response    = [];
            $user        = $this->app->getIdentity();

            foreach ($transitions as $transition) {
                $canEdit   = $user->authorise('core.edit', $this->extension . '.transition.' . (int) $transition->id);
                $canDelete = $user->authorise('core.delete', $this->extension . '.transition.' . (int) $transition->id);
                $canRun    = $user->authorise('core.execute.transition', $this->extension . '.transition.' . (int) $transition->id);

                $response[] = [
                    'id'            => (int) $transition->id,
                    'title'         => Text::_($transition->title),
                    'description'   => Text::_($transition->description),
                    'published'     => (bool) $transition->published,
                    'from_stage_id' => (int) $transition->from_stage_id,
                    'to_stage_id'   => (int) $transition->to_stage_id,
                    'ordering'      => (int) $transition->ordering,
                    'workflow_id'   => (int) $transition->workflow_id,
                    'permissions'   => [
                        'edit'           => $canEdit,
                        'delete'         => $canDelete,
                        'run_transition' => $canRun,
                    ],
                ];
            }

            echo new JsonResponse($response);
        } catch (\Exception $e) {
            $this->app->setHeader('status', 500);
            echo new JsonResponse($e->getMessage(), 'error', true);
        }

        $this->app->close();
    }


    public function publish($type = 'stage')
    {

        try {
            // Check for request forgeries
            if (!$this->checkToken('post', false)) {
                throw new \RuntimeException(Text::_('JINVALID_TOKEN'));
            }

            // Check if the user has permission to publish items
            if (!$this->app->getIdentity()->authorise('core.edit.state', $this->extension . '.workflow.' . $this->workflowId)) {
                throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'));
            }

            // Get items to publish from the request.
            $cid   = (array) $this->input->get('cid', [], 'int');
            $data  = ['publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3];
            $task  = $this->getTask();
            $type  = $this->input->getCmd('type');
            $value = ArrayHelper::getValue($data, $task, 0, 'int');

            if (empty($type)) {
                throw new \RuntimeException(Text::_($this->text_prefix . '_' . strtoupper($type) . '_NO_ITEM_SELECTED'));
            }

            // Remove zero values resulting from input filter
            $cid = array_filter($cid);

            if (empty($cid)) {
                throw new \RuntimeException(Text::_($this->text_prefix . '_' . strtoupper($type) . '_NO_ITEM_SELECTED'));
            }
            // Get the model.
            $model = $this->getModel($type);


            $model->publish($cid, $value);
            $errors = $model->getErrors();
            $ntext  = null;

            if ($value === 1) {
                if ($errors) {
                    echo new JsonResponse(Text::plural($this->text_prefix . '_' . strtoupper($type) . '_N_ITEMS_FAILED_PUBLISHING', \count($cid)), 'error', true);
                } else {
                    $ntext = $this->text_prefix . '_' . strtoupper($type) . '_N_ITEMS_PUBLISHED';
                }
            } elseif ($value === 0) {
                $ntext = $this->text_prefix . '_' . strtoupper($type) . '_N_ITEMS_UNPUBLISHED';
            } elseif ($value === 2) {
                $ntext = $this->text_prefix . '_' . strtoupper($type) . '_N_ITEMS_ARCHIVED';
            } else {
                $ntext = $this->text_prefix . '_' . strtoupper($type) . '_N_ITEMS_TRASHED';
            }

            $response = [
                'success' => true,
                'message' => Text::plural($ntext, \count($cid)),
            ];

            if (\count($cid)) {
                echo new JsonResponse($response);
            }
        } catch (\Exception $e) {
            $this->app->setHeader('status', 500);
            echo new JsonResponse($e->getMessage(), 'error', true);
        }
        $this->app->close();
    }

    public function delete($type = 'stage')
    {
        try {
            // Check for request forgeries
            if (!$this->checkToken('post', false)) {
                throw new \RuntimeException(Text::_('JINVALID_TOKEN'));
            }

            // Get items to remove from the request.
            $cid   = (array) $this->input->get('cid', [], 'int');
            $type  = $this->input->getCmd('type');
            $cid   = array_filter($cid);

            if (empty($cid)) {
                throw new \RuntimeException(Text::_($this->text_prefix . '_' . strtoupper($type) . '_NO_ITEM_SELECTED'));
            }
            // Get the model.
            $model = $this->getModel($type);

            // Remove the items.
            if ($model->delete($cid)) {
                $response = [
                    'success' => true,
                    'message' => Text::plural($this->text_prefix . '_' . strtoupper($type) . '_N_ITEMS_DELETED', \count($cid)),
                ];
            } else {
                throw new \RuntimeException(Text::plural($this->text_prefix . '_' . strtoupper($type) . '_N_ITEMS_FAILED_DELETING', \count($cid)));
            }

            if (isset($response)) {
                echo new JsonResponse($response);
            }

            // Invoke the postDelete method to allow for the child class to access the model.
            $this->postDeleteHook($model, $cid);
        } catch (\Exception $e) {
            $this->app->setHeader('status', 500);
            echo new JsonResponse($e->getMessage(), 'error', true);
        }

        $this->app->close();
    }

    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  BaseDatabaseModel|boolean  Model object on success; otherwise false on failure.
     *
     * @since   __DEPLOYMENT_VERSION__
     */
    public function getModel($name = '', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }
}
