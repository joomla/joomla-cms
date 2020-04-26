<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Workflow.Publishing
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\DatabaseModelInterface;
use Joomla\CMS\MVC\View\ViewInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\String\Inflector;

/**
 * Workflow Publishing Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgWorkflowPublishing extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Loads the CMS Application for direct access
	 *
	 * @var   CMSApplicationInterface
	 * @since __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * The name of the supported name to check against
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	protected $supportname = 'joomla.state';

	/**
	 * The form event.
	 *
	 * @param   Form      $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
		$context = $form->getName();

		// Extend the transition form
		if ($context == 'com_workflow.transition')
		{
			return $this->enhanceTransitionForm($form, $data);
		}

		return $this->enhanceItemForm($form, $data);
	}

	/**
	 * Add different parameter options to the transition view, we need when executing the transition
	 *
	 * @param   Form      $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function enhanceTransitionForm(Form $form, $data)
	{
		$model = $this->app->bootComponent('com_workflow')
				->getMVCFactory()->createModel('Workflow', 'Administrator', ['ignore_request' => true]);

		$workflow_id = !empty($data->workflow_id) ? (int) $data->workflow_id : (int) $form->getValue('workflow_id');

		if (empty($workflow_id))
		{
			$workflow_id = $this->app->input->getInt('workflow_id');
		}

		$workflow = $model->getItem($workflow_id);

		if (!$this->isSupported($workflow->extension))
		{
			return true;
		}

		Form::addFormPath(__DIR__ . '/forms');

		$form->loadFile('workflow_publishing');

		if ($workflow_id)
		{
			$form->setFieldAttribute('publishing', 'extension', $workflow->extension, 'options');
		}
		else
		{
			$form->setFieldAttribute('publishing', 'disabled', 'true', 'options');
		}

		return true;
	}

	/**
	 * Disable certain fields in the item  form view, when we want to take over this function in the transition
	 * Check also for the workflow implementation and if the field exists
	 *
	 * @param   Form      $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function enhanceItemForm(Form $form, $data)
	{
		$context = $form->getName();

		if (!$this->isSupported($context))
		{
			return true;
		}

		$parts = explode('.', $context);

		$component = $this->app->bootComponent($parts[0]);

		$modelName = $component->getModelName($context);

		$table = $component->getMVCFactory()->createModel($modelName, $this->app->getName(), ['ignore_request' => true])
					->getTable();

		$form->setFieldAttribute($table->getColumnAlias('published'), 'disabled', 'true');

		return true;
	}

	/**
	 * Manipulate the generic list view
	 *
	 * @param type $context
	 * @param type $view
	 * @param type $result
	 */
	public function onAfterDisplay(string $context, ViewInterface $view, $result)
	{
		$parts = explode('.', $context);

		if ($parts < 2)
		{
			return true;
		}

		$app = Factory::getApplication();

		// We need the single model context for checking for workflow
		$singularsection = Inflector::singularize($parts[1]);

		$newcontext = $parts[0] . '.' . $singularsection;

		if (!$app->isClient('administrator') || !$this->isSupported($newcontext))
		{
			return true;
		}

		// That's the hard coded list from the AdminController publish method => change, when it's make dynamic in the future
		$states = ['publish', 'unpublish', 'archive', 'trash', 'report'];

		$js = "
			document.addEventListener('DOMContentLoaded', function()
			{
				var dropdown = document.getElementById('toolbar-dropdown-status-group');

				if (!dropdown)
				{
					reuturn;
				}

				" . \json_encode($states) . ".forEach((action) => {
					var button = document.getElementById('status-group-children-' + action);

					if (button)
					{
						button.classList.add('d-none');
					}
				});

			});
		";

		$app->getDocument()->addScriptDeclaration($js);
	}

	/**
	 * Check if we can execute the transition
	 *
	 * @param   string   $context     The context
	 * @param   array    $pks         IDs of the items
	 * @param   object   $transition  The value to change to
	 *
	 * @return boolean
	 */
	public function onWorkflowBeforeTransition($context, $pks, $transition)
	{
		if (!$this->isSupported($context) || !is_numeric($transition->options->get('publishing')))
		{
			return true;
		}

		$value = (int) $transition->options->get('publishing');

		// Here it becomes tricky. We would like to use the component models publish method, so we will
		// Execute the normal "onContentBeforeChangeState" plugins. But they could cancel the execution,
		// So we have to precheck and cancel the whole transition stuff if not allowed.
		$this->app->set('plgWorkflowPublishing.' . $context, $pks);

		$result = $this->app->triggerEvent('onContentBeforeChangeState', [$context, $pks, $value]);

		// Release whitelist, the job is done
		$this->app->set('plgWorkflowPublishing.' . $context, []);

		if (\in_array(false, $result, true))
		{
			return false;
		}

		return true;
	}

	/**
	 * Change State of an item. Used to disable state change
	 *
	 * @param   string   $context     The context
	 * @param   array    $pks         IDs of the items
	 * @param   object   $transition  The value to change to
	 *
	 * @return boolean
	 */
	public function onWorkflowAfterTransition($context, $pks, $transition)
	{
		if (!$this->isSupported($context))
		{
			return true;
		}

		$parts = explode('.', $context);

		// We need at least the extension + view for loading the table fields
		if (count($parts) < 2)
		{
			return false;
		}

		$component = $this->app->bootComponent($parts[0]);

		$value = (int) $transition->options->get('publishing');

		$options = [
			'ignore_request' => true,
			// We already have triggered onContentBeforeChangeState, so use our own
			'event_before_change_state' => 'onWorkflowBeforeChangeState'
		];

		$modelName = $component->getModelName($context);

		$model = $component->getMVCFactory()->createModel($modelName, $this->app->getName(), $options);

		return $model->publish($pks, $value);
	}

	/**
	 * Change State of an item. Used to disable state change
	 *
	 * @param   string   $context  The context
	 * @param   array    $pks      IDs of the items
	 * @param   int      $value    The value to change to
	 * @return boolean
	 */
	public function onContentBeforeChangeState($context, $pks, $value)
	{
		if (!$this->isSupported($context))
		{
			return true;
		}

		// We have whitelisted the pks, so we're the one who triggered
		// With onWorkflowBeforeTransition => free pass
		if ($this->app->get('plgWorkflowPublishing.' . $context) === $pks)
		{
			return true;
		}

		throw new Exception(Text::_('PLG_WORKFLOW_PUBLISHING_CHANGE_STATE_NOT_ALLOWED'));
	}

	/**
	 * The save event.
	 *
	 * @param   string   $context  The context
	 * @param   object   $table    The item
	 * @param   boolean  $isNew    Is new item
	 * @param   array    $data     The validated data
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function onContentBeforeSave($context, TableInterface $table, $isNew, $data)
	{
		if (!$this->isSupported($context))
		{
			return true;
		}

		$keyName = $table->getColumnAlias('published');

		// Check for the old value
		$article = clone $table;

		$article->load($table->id);

		// We don't allow the change of the state when we use the workflow
		// As we're setting the field to disabled, no value should be there at all
		if (isset($data[$keyName]))
		{
			$this->app->enqueueMessage(Text::_('PLG_WORKFLOW_PUBLISHING_CHANGE_STATE_NOT_ALLOWED'), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Check if the current plugin should execute workflow related activities
	 *
	 * @param type $context
	 * @return boolean
	 */
	protected function isSupported($context)
	{
		$parts = explode('.', $context);

		// We need at least the extension + view for loading the table fields
		if (count($parts) < 2)
		{
			return false;
		}

		$component = $this->app->bootComponent($parts[0]);

		if (!$component instanceof WorkflowServiceInterface || !$component->isWorkflowActive($context) || !$component->supportFunctionality($this->supportname, $context))
		{
			return false;
		}

		$modelName = $component->getModelName($context);

		$model = $component->getMVCFactory()->createModel($modelName, $this->app->getName(), ['ignore_request' => true]);

		if (!$model instanceof DatabaseModelInterface || !method_exists($model, 'publish'))
		{
			return false;
		}

		$table = $model->getTable();

		if (!$table instanceof TableInterface || !$table->hasField('published'))
		{
			return false;
		}

		return true;
	}
}
