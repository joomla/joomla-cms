<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Table\Table;

\defined('JPATH_PLATFORM') or die;

/**
 * Interface for a workflow model.
 *
 * @since  4.0.0
 */
interface WorkflowModelInterface
{
	/**
	 * Set Up the workflow
	 *
	 * @param   string  $extension  The option and section separated by.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function setUpWorkflow($extension);

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   Form    $form   A Form object.
	 * @param   mixed   $data   The data expected for the form.
	 *
	 * @return  void
	 *
	 * @see     FormField
	 * @since   4.0.0
	 * @throws  \Exception if there is an error in the form event.
	 */
	public function workflowPreprocessForm(Form $form, $data);

	/**
	 * Let plugins access stage change events
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function workflowBeforeStageChange();

	/**
	 * Preparation of workflow data/plugins
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function workflowBeforeSave();

	/**
	 * Executing of relevant workflow methods
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function workflowAfterSave($data);

	/**
	 * Batch change workflow stage or current.
	 *
	 * @param   integer  $oldId     The ID of the item copied from
	 * @param   integer  $newId     The ID of the new item
	 *
	 * @return  null
	 *
	 * @since   4.0.0
	 */
	public function workflowCleanupBatchMove($oldId, $newId);

	/**
	 * Runs transition for item.
	 *
	 * @param   array    $pks           Id of items to execute the transition
	 * @param   integer  $transitionId  Id of transition
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function executeTransition(array $pks, int $transitionId);

	/**
	 * Method to get state variables.
	 *
	 * @param   string  $property  Optional parameter name
	 * @param   mixed   $default   Optional default value
	 *
	 * @return  mixed  The property where specified, the state object where omitted
	 *
	 * @since   4.0.0
	 */
	public function getState($property = null, $default = null);

	/**
	 * Method to get the model name
	 *
	 * The model name. By default parsed using the classname or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function getName();


	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  Table  A Table object
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getTable($name = '', $prefix = '', $options = array());

}
