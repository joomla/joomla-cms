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
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\String\Inflector;
use Joomla\Filesystem;
/**
 * Workflow Publishing Plugin
 *
 * @since  4.0.0
 */
class PlgWorkflowImages extends CMSPlugin implements SubscriberInterface
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
			$this->enhanceWorkflowTransitionForm($form, $data);

			return;
		}

		return;
	}

	/**
	 * Add different parameter options to the transition view, we need when executing the transition
	 *
	 * @param   Form      $form The form
	 * @param   stdClass  $data The data
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	/*
	protected function enhanceTransitionForm(Form $form, $data)
	{
		$workflow = $this->enhanceWorkflowTransitionForm($form, $data);

		if (!$workflow)
		{
			return true;
		}

		$form->setFieldAttribute('publishing', 'extension', $workflow->extension, 'options');

		return true;
	}*/



	/**
	 * Check if we can execute the transition
	 *
	 * @param   WorkflowTransitionEvent  $event
	 *
	 * @return boolean
	 *
	 * @since   4.0.0
	 */
	public function onWorkflowBeforeTransition(WorkflowTransitionEvent $event)
	{
		$context    = $event->getArgument('extension');
		$extensionName = $event->getArgument('extensionName');
		$transition = $event->getArgument('transition');
		$pks        = $event->getArgument('pks');

		// Get Values from Form
		$value_intro_image = $transition->options->get('images_intro_image_settings');
		$value_full_article_image = $transition->options->get('images_full_article_image_settings');

		if (!$this->isSupported($context)
			|| !is_numeric($value_intro_image)
			|| !is_numeric($value_full_article_image))
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
			print_r($transition);

			if ($value_intro_image == 0 && $value_full_article_image == 0)
			{
				// Do nothing
			}
			else
			{
				if ($value_intro_image == 1)
				{
					if (!isset($model->getItem($pk)->images["image_intro"]) || !file_exists($model->getItem($pk)->images["image_intro"]))
					{
						Factory::getApplication()->enqueueMessage("ERROR: Intro Image required");

						return false;
					}
				}

				if ($value_full_article_image == 1)
				{
					if (!isset($model->getItem($pk)->images["image_fulltext"]) || !file_exists($model->getItem($pk)->images["image_fulltext"]))
					{
						Factory::getApplication()->enqueueMessage("ERROR: Full Article Image required");

						return false;
					}
				}
			}

			exit();

			// Print_r($model->getItem($pk)->images);
			// print_r(isset($model->getItem($pk)->images["image_intro"])); //+strleng oder isfile
		}

		if (!$this->isSupported($context)
			|| !is_numeric($value_intro_image)
			|| !is_numeric($value_full_article_image))
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

		$value = $transition->options->get('publishing');

		if (!is_numeric($value))
		{
			return;
		}

		$options = [
			'ignore_request'            => true,
			// We already have triggered onContentBeforeChangeState, so use our own
			'event_before_change_state' => 'onWorkflowBeforeChangeState'
		];

		$modelName = $component->getModelName($context);

		$model = $component->getMVCFactory()->createModel($modelName, $this->app->getName(), $options);



		$model->publish($pks, $value);
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
