<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\MVC\Model;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Workflow\Workflow;

/**
 * Trait which supports state behavior
 *
 * @since  4.0.0
 */
trait WorkflowBehaviorTrait {

	/**
	 * The  for the component.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $extension = null;

	/**
	 * Set Up the workflow
	 *
	 * @param   string  $extension  The option and section separated by .
	 */
	public function setUpWorkflow($extension) {

		$this->extension = $extension;
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   Form   $form  A Form object.
	 * @param   mixed  $data  The data expected for the form.
	 *
	 * @return  void
	 *
	 * @throws  \Exception if there is an error in the form event.
	 * @since   4.0.0
	 * @see     FormField
	 */
	public function preprocessFormWorkflow(Form $form, $data) {

		// Import the appropriate plugin group.
		PluginHelper::importPlugin('workflow');
	}

	/**
	 * Batch change workflow stage or current.
	 *
	 * @param   integer  $value     The workflow stage ID.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 *
	 * @since   4.0.0
	 */
	public function batchWorkflowStage(int $value, array $pks, array $contexts) {

		$user = Factory::getApplication()->getIdentity();
		/** @var  $workflow */
		$workflow = Factory::getApplication()->bootComponent('com_workflow');

		if (!$user->authorise('core.admin', $this->option))
		{
			$this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EXECUTE_TRANSITION'));
		}

		// Get workflow stage information
		$stage = $workflow->createTable('Stage', 'Administrator');

		if (empty($value) || !$stage->load($value))
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('JGLOBAL_BATCH_WORKFLOW_STAGE_ROW_NOT_FOUND'), 'error');

			return false;
		}

		if (empty($pks))
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('JGLOBAL_BATCH_WORKFLOW_STAGE_ROW_NOT_FOUND'), 'error');

			return false;
		}

		$workflow = new Workflow(['extension' => $this->option]);

		// Update workflow associations
		return $workflow->updateAssociations($pks, $value);
	}

}
