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
use Joomla\CMS\Event\Workflow\WorkflowFunctionalityUsedEvent;
use Joomla\CMS\Event\Workflow\WorkflowTransitionEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\SubformField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\DatabaseModelInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Workflow\WorkflowPluginTrait;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Image\Image;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
/**
 * Workflow Publishing Plugin
 *
 * @since  4.0.0
 */
class PlgWorkflowFields extends CMSPlugin implements SubscriberInterface
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
	 * The name of the supported name to check against
	 *
	 * @var   string
	 * @since 4.0.0
	 */
	protected $supportFunctionality = 'core.state';

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
			'onContentPrepareForm'        => 'onContentPrepareForm',
			'onWorkflowBeforeTransition'  => 'onWorkflowBeforeTransition',
			'onWorkflowAfterTransition'   => 'onWorkflowAfterTransition',
			'onContentBeforeSave'         => 'onContentBeforeSave',
			'onWorkflowFunctionalityUsed' => 'onWorkflowFunctionalityUsed',
		];
	}

	/**
	 * The form event.
	 *
	 * @param   EventInterface  $event  The event
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
			$workflow = $this->enhanceWorkflowTransitionForm($form, $data);

			$existingFields = FieldsHelper::getFields($workflow->extension);

			// build subform
			$subform = simplexml_load_file(JPATH_ROOT.'/plugins/workflow/fields/forms/subform.xml');
			$myfield = $subform -> fields -> addChild('field');
			$myfield->addAttribute('name','customfield');
			$myfield->addAttribute('type','list');

			// add all existing Custom Fields as option to field
			foreach ($existingFields as $existingField){
				$option = $myfield->addChild('option',$existingField->name);
				$option -> addAttribute('value', $existingField->id);
			}

			$form->setFieldAttribute('subform','formsource',$subform->asXML(),'options');


			return;
		}

		return;

	}

	/**
	 * Check if we can execute the transition
	 *
	 * @param WorkflowTransitionEvent $event
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 * @since   4.0.0
	 */
	public function onWorkflowBeforeTransition(WorkflowTransitionEvent $event)
	{
		$context    = $event->getArgument('extension');
		$extensionName = $event->getArgument('extensionName');
		$transition = $event->getArgument('transition');
		$pks        = $event->getArgument('pks');

		//get Values from Form
		$reqired = $transition->options->get('required');
		$blankreqired = $transition->options->get('notrequired');
		$contains = $transition->options->get('contains');
		$containsNot = $transition->options->get('containsNot');


		if (!$this->isSupported($context)
			||!is_numeric($reqired)
			||!is_numeric($blankreqired)
			||!is_numeric($contains)
			||!is_numeric($containsNot))
		{
			return true;
		}

		$component = $this->app->bootComponent($extensionName);

		$options = [
			'ignore_request'            => true,
			// We already have triggered onContentBeforeChangeState, so use our own
			'event_before_change_state' => 'onWorkflowBeforeChangeState'
		];

		// Get model

		$modelName = $component->getModelName($context);

		$model = $component->getMVCFactory()->createModel($modelName, $this->app->getName(), $options);



		foreach ($pks as $pk)
		{



		}

		if (!$this->isSupported($context))
		{
			return true;
		}
		return true;
	}

	/**
	 * Change State of an item. Used to disable state change
	 *
	 * @param   WorkflowTransitionEvent  $event
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
			return true;
		}

		$component = $this->app->bootComponent($extensionName);

		$options = [
			'ignore_request'            => true,
			// We already have triggered onContentBeforeChangeState, so use our own
			'event_before_change_state' => 'onWorkflowBeforeChangeState'
		];

		$modelName = $component->getModelName($context);

		$model = $component->getMVCFactory()->createModel($modelName, $this->app->getName(), $options);

		foreach ($pks as $pk){

		}

		return true;

	}

	/**
	 * Change State of an item. Used to disable state change
	 *
	 * @param   EventInterface  $event
	 *
	 * @return boolean
	 *
	 * @throws Exception
	 * @since   4.0.0
	 */
	public function onContentBeforeChangeState(EventInterface $event)
	{
		$context = $event->getArgument('0');
		$pks     = $event->getArgument('1');

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
	 * @param   EventInterface  $event
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function onContentBeforeSave(EventInterface $event)
	{
		$context = $event->getArgument('0');

		/** @var TableInterface $table */
		$table = $event->getArgument('1');
		$isNew = $event->getArgument('2');
		$data  = $event->getArgument('3');

		if (!$this->isSupported($context))
		{
			return true;
		}

		$keyName = $table->getColumnAlias('published');

		// Check for the old value
		$article = clone $table;

		$article->load($table->id);

		/**
		 * We don't allow the change of the state when we use the workflow
		 * As we're setting the field to disabled, no value should be there at all
		 */
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
	 * @param   string  $context
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

	/**
	 * If plugin supports the functionality we set the used variable
	 *
	 * @param   WorkflowFunctionalityUsedEvent  $event
	 *
	 * @since 4.0.0
	 */
	public function onWorkflowFunctionalityUsed(WorkflowFunctionalityUsedEvent $event)
	{
		$functionality = $event->getArgument('functionality');

		if ($functionality !== 'core.state')
		{
			return;
		}

		$event->setUsed();
	}
}
