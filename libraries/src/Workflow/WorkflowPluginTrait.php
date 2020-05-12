<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Workflow;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Object\CMSObject;
use ReflectionClass;

/**
 * Trait for component workflow plugins.
 *
 * @since  4.0.0
 */
trait WorkflowPluginTrait
{
	/**
	 * Add different parameter options to the transition view, we need when executing the transition
	 *
	 * @param   Form       $form  The form
	 * @param   \stdClass  $data  The data
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function enhanceWorkflowTransitionForm(Form $form, $data)
	{
		$workflow_id = (int) ($data->workflow_id ?? $form->getValue('workflow_id'));

		$workflow = $this->getWorkflow($workflow_id);

		if (empty($workflow->id) || !$this->isSupported($workflow->extension))
		{
			return false;
		}

		// Load XML file from "parent" plugin
		$path = dirname((new ReflectionClass(static::class))->getFileName());

		if (file_exists($path . '/forms/action.xml'))
		{
			$form->loadFile($path . '/forms/action.xml');
		}

		return $workflow;
	}

	/**
	 * Get the workflow for a given ID
	 *
	 * @param   int|null  $workflow_id  ID of the workflow
	 *
	 * @return  CMSObject|boolean  Object on success, false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getWorkflow(int $workflow_id = null)
	{
		$workflow_id = !empty($workflow_id) ? $workflow_id : $this->app->input->getInt('workflow_id');

		if (is_array($workflow_id))
		{
			return false;
		}

		return $this->app->bootComponent('com_workflow')
			->getMVCFactory()
			->createModel('Workflow', 'Administrator', ['ignore_request' => true])
			->getItem($workflow_id);
	}

	/**
	 * Check if the current plugin should execute workflow related activities
	 *
	 * @param   string  $context  Context to check
	 *
	 * @return boolean
	 *
	 * @since   4.0.0
	 */
	protected function isSupported($context)
	{
		return false;
	}
}
