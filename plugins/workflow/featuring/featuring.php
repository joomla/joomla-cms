<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Workflow.Featuring
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Event\View\DisplayEvent;
use Joomla\CMS\Event\Workflow\WorkflowFunctionalityUsedEvent;
use Joomla\CMS\Event\Workflow\WorkflowTransitionEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\DatabaseModelInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Workflow\WorkflowPluginTrait;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\Component\Content\Administrator\Event\Model\FeatureEvent;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\String\Inflector;

/**
 * Workflow Featuring Plugin
 *
 * @since  4.0.0
 */
class PlgWorkflowFeaturing extends CMSPlugin implements SubscriberInterface
{
	use WorkflowPluginTrait;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Loads the CMS Application for direct access
	 *
	 * @var   CMSApplicationInterface
	 * @since 4.0.0
	 */
	protected $app;

	/**
	 * The name of the supported functionality to check against
	 *
	 * @var   string
	 * @since 4.0.0
	 */
	protected $supportFunctionality = 'core.featured';

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onContentPrepareForm'          => 'onContentPrepareForm',
			'onAfterDisplay'                => 'onAfterDisplay',
			'onWorkflowBeforeTransition'    => 'onWorkflowBeforeTransition',
			'onWorkflowAfterTransition'     => 'onWorkflowAfterTransition',
			'onContentBeforeChangeFeatured' => 'onContentBeforeChangeFeatured',
			'onContentBeforeSave'           => 'onContentBeforeSave',
			'onWorkflowFunctionalityUsed'   => 'onWorkflowFunctionalityUsed',
		];
	}

	/**
	 * The form event.
	 *
	 * @param EventInterface $event The event
	 *
	 * @since   4.0.0
	 */
	public function onContentPrepareForm(EventInterface $event)
	{
		$form = $event->getArgument('0');
		$data = $event->getArgument('1');

		$context = $form->getName();

		// Extend the transition form
		if ($context === 'com_workflow.transition')
		{
			$this->enhanceWorkflowTransitionForm($form, $data);

			return;
		}

		$this->enhanceItemForm($form, $data);

		return;
	}

	/**
	 * Disable certain fields in the item form view, when we want to take over this function in the transition
	 * Check also for the workflow implementation and if the field exists
	 *
	 * @param Form     $form The form
	 * @param stdClass $data The data
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
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

		$fieldname = $table->getColumnAlias('featured');

		$options = $form->getField($fieldname)->options;

		$value = isset($data->$fieldname) ? $data->$fieldname : $form->getValue($fieldname, null, 0);

		$text = '-';

		$textclass = 'body';

		switch ($value)
		{
			case 1:
				$textclass = 'success';
				break;

			case 0:
			case -2:
				$textclass = 'danger';
		}

		if (!empty($options))
		{
			foreach ($options as $option)
			{
				if ($option->value == $value)
				{
					$text = $option->text;

					break;
				}
			}
		}

		$form->setFieldAttribute($fieldname, 'type', 'spacer');

		$label = '<span class="text-' . $textclass . '">' . htmlentities($text, ENT_COMPAT, 'UTF-8') . '</span>';
		$form->setFieldAttribute(
			$fieldname,
			'label',
			Text::sprintf('PLG_WORKFLOW_FEATURING_FEATURED', $label)
		);

		return true;
	}

	/**
	 * Manipulate the generic list view
	 *
	 * @param DisplayEvent $event
	 *
	 * @since   4.0.0
	 */
	public function onAfterDisplay(DisplayEvent $event)
	{
		$app = Factory::getApplication();

		if (!$app->isClient('administrator'))
		{
			return;
		}

		$component = $event->getArgument('extensionName');
		$section   = $event->getArgument('section');

		// We need the single model context for checking for workflow
		$singularsection = Inflector::singularize($section);

		if (!$this->isSupported($component . '.' . $singularsection))
		{
			return true;
		}

		// List of releated batch functions we need to hide
		$states = [
			'featured',
			'unfeatured'
		];

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

		return true;
	}

	/**
	 * Check if we can execute the transition
	 *
	 * @param WorkflowTransitionEvent $event
	 *
	 * @return boolean
	 *
	 * @since   4.0.0
	 */
	public function onWorkflowBeforeTransition(WorkflowTransitionEvent $event)
	{
		$context    = $event->getArgument('extension');
		$transition = $event->getArgument('transition');
		$pks        = $event->getArgument('pks');

		if (!$this->isSupported($context) || !is_numeric($transition->options->get('featuring')))
		{
			return true;
		}

		$value = $transition->options->get('featuring');

		if (!is_numeric($value))
		{
			return true;
		}

		/**
		 * Here it becomes tricky. We would like to use the component models featured method, so we will
		 * Execute the normal "onContentBeforeChangeFeatured" plugins. But they could cancel the execution,
		 * So we have to precheck and cancel the whole transition stuff if not allowed.
		 */
		$this->app->set('plgWorkflowFeaturing.' . $context, $pks);

		// Trigger the change state event.
		$eventResult = $this->app->getDispatcher()->dispatch(
			'onAfterDisplay',
			AbstractEvent::create(
				'onContentBeforeChangeFeatured',
				[
					'eventClass' => 'Joomla\Component\Content\Administrator\Event\Model\FeatureEvent',
					'subject'    => $this,
					'extension'  => $context,
					'pks'        => $pks,
					'value'      => $value,
					'abort'      => false,
					'abortReason' => '',
				]
			)
		);

		// Release whitelist, the job is done
		$this->app->set('plgWorkflowFeaturing.' . $context, []);

		if ($eventResult->getArgument('abort'))
		{
			return false;
		}

		return true;
	}

	/**
	 * Change Feature State of an item. Used to disable feature state change
	 *
	 * @param WorkflowTransitionEvent $event
	 *
	 * @return boolean
	 *
	 * @since   4.0.0
	 */
	public function onWorkflowAfterTransition(WorkflowTransitionEvent $event)
	{
		$context       = $event->getArgument('extension');
		$extensionName = $event->getArgument('extensionName');
		$transition    = $event->getArgument('transition');
		$pks           = $event->getArgument('pks');

		if (!$this->isSupported($context))
		{
			return;
		}

		$component = $this->app->bootComponent($extensionName);

		$value = $transition->options->get('featuring');

		if (!is_numeric($value))
		{
			return;
		}

		$options = [
			'ignore_request'               => true,
			// We already have triggered onContentBeforeChangeFeatured, so use our own
			'event_before_change_featured' => 'onWorkflowBeforeChangeFeatured'
		];

		$modelName = $component->getModelName($context);

		$model = $component->getMVCFactory()->createModel($modelName, $this->app->getName(), $options);

		$model->featured($pks, $value);
	}

	/**
	 * Change Feature State of an item. Used to disable Feature state change
	 *
	 * @param FeatureEvent $event
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since   4.0.0
	 */
	public function onContentBeforeChangeFeatured(FeatureEvent $event)
	{
		$extension = $event->getArgument('extension');
		$pks       = $event->getArgument('pks');

		if (!$this->isSupported($extension))
		{
			return true;
		}

		// We have whitelisted the pks, so we're the one who triggered
		// With onWorkflowBeforeTransition => free pass
		if ($this->app->get('plgWorkflowFeaturing.' . $extension) === $pks)
		{
			return true;
		}

		$event->setAbort('PLG_WORKFLOW_FEATURING_CHANGE_STATE_NOT_ALLOWED');
	}

	/**
	 * The save event.
	 *
	 * @param EventInterface $event
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function onContentBeforeSave(EventInterface $event)
	{
		$context = $event->getArgument('0');

		// @var TableInterface

		$table = $event->getArgument('1');
		$isNew = $event->getArgument('2');
		$data  = $event->getArgument('3');

		if (!$this->isSupported($context))
		{
			return true;
		}

		$keyName = $table->getColumnAlias('featured');

		// Check for the old value
		$article = clone $table;

		$article->load($table->id);

		// We don't allow the change of the feature state when we use the workflow
		// As we're setting the field to disabled, no value should be there at all
		if (isset($data[$keyName]))
		{
			$this->app->enqueueMessage(Text::_('PLG_WORKFLOW_FEATURING_CHANGE_STATE_NOT_ALLOWED'), 'error');

			return false;
		}

		return true;
	}

	/**
	 * Check if the current plugin should execute workflow related activities
	 *
	 * @param string $context
	 *
	 * @return boolean
	 *
	 * @since   4.0.0
	 */
	protected function isSupported($context)
	{
		if (!$this->checkWhiteAndBlacklist($context) || !$this->checkExtensionSupport($context, $this->supportFunctionality))
		{
			return false;
		}

		$parts = explode('.', $context);

		// We need at least the extension + view for loading the table fields
		if (count($parts) < 2)
		{
			return false;
		}

		$component = $this->app->bootComponent($parts[0]);

		if (!$component instanceof WorkflowServiceInterface
			|| !$component->isWorkflowActive($context)
			|| !$component->supportFunctionality($this->supportFunctionality, $context))
		{
			return false;
		}

		$modelName = $component->getModelName($context);

		$model = $component->getMVCFactory()->createModel($modelName, $this->app->getName(), ['ignore_request' => true]);

		if (!$model instanceof DatabaseModelInterface || !method_exists($model, 'featured'))
		{
			return false;
		}

		$table = $model->getTable();

		if (!$table instanceof TableInterface || !$table->hasField('featured'))
		{
			return false;
		}

		return true;
	}

	/**
	 * If plugin supports the functionality we set the used variable
	 *
	 * @param WorkflowFunctionalityUsedEvent $event
	 *
	 * @since 4.0.0
	 */
	public function onWorkflowFunctionalityUsed(WorkflowFunctionalityUsedEvent $event)
	{
		$functionality = $event->getArgument('functionality');

		if ($functionality !== 'core.featured')
		{
			return;
		}

		$event->setUsed();
	}
}
