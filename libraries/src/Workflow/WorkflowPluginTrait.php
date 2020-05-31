<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
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
	 * @param   Form      $form The form
	 * @param   \stdClass $data The data
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
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
	 * @param   int|null $workflow_id ID of the workflow
	 *
	 * @return  CMSObject|boolean  Object on success, false on failure.
	 *
	 * @since   4.0.0
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
	 * @param   string $context Context to check
	 *
	 * @return boolean
	 *
	 * @since   4.0.0
	 */
	protected function isSupported($context)
	{
		return false;
	}

	/**
	 * Check if the context is listed in the whitelist or in the blacklist and return the result
	 *
	 * @param   string $context Context to check
	 *
	 * @return boolean
	 */
	protected function checkWhiteAndBlacklist($context)
	{
		$whitelist = \array_filter((array) $this->params->get('whitelist', []));
		$blacklist = \array_filter((array) $this->params->get('blacklist', []));

		if (!empty($whitelist))
		{
			foreach ($whitelist as $allowed)
			{
				if ($context === $allowed)
				{
					return true;
				}
			}

			return false;
		}

		foreach ($blacklist as $forbidden)
		{
			if ($context === $forbidden)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if the context is listed in the whitelist or in the blacklist and return the result
	 *
	 * @param   string  $context       Context to check
	 * @param   string  $functionality The funcationality
	 *
	 * @return boolean
	 */
	protected function checkExtensionSupport($context, $functionality)
	{
		$parts = explode('.', $context);

		$component = $this->app->bootComponent($parts[0]);

		if (!$component instanceof WorkflowServiceInterface
			|| !$component->isWorkflowActive($context)
			|| !$component->supportFunctionality($functionality, $context))
		{
			return false;
		}

		return true;
	}

}
