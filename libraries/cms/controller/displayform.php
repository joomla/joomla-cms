<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base Display Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  Controller
 * @since       3.4
*/
class JControllerDisplayform extends JControllerDisplay
{
	/**
	 * Permission needed for the action
	 *
	 * @var    string
	 * @since  3.4
	 */
	public $permission = 'core.edit';

	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @since   3.4
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		if (empty($this->options[parent::CONTROLLER_VIEW_FOLDER]))
		{
			$this->viewName = $this->input->getWord('view', 'article');
		}
		else
		{
			$this->viewName = $this->options[parent::CONTROLLER_VIEW_FOLDER];
		}

		// Disable the main menu in edit view
		$this->input->set('hidemainmenu', true);

		$componentFolder = $this->input->getWord('option', 'com_content');
		$context         = $componentFolder . '.' . $this->viewName;

		try
		{
			$model = $this->getModel();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
		}

		$idName = $model->getTable()->getKeyName(false);

		if (!$this->editCheck($this->app, $context, $idName))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		// Checkout the item if not new
		$id = $this->input->get($idName);

		if ($id != 0)
		{
			$model->checkout($id);
		}

		// The default view for a form is an edit view
		$this->input->set('layout', 'edit');

		return parent::execute();
	}

	/**
	 * Method to check if the user has permission to edit this item
	 *
	 * @param   JApplicationCms  $app  The application
	 *
	 * @return  boolean
	 *
	 * @since 3.4
	 */
	protected function editCheck(JApplicationCms $app, $context, $idName)
	{
		$id = $this->input->getInt($idName, 0);

		// Check for edit form.
		if (!$this->checkEditId($context, $id))
		{
			// Somehow the person just went to the form - we don't allow that.
			$app->enqueueMessage((JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id)), 'error');
			$app->redirect(JRoute::_('index.php?option=com_cpanel', false));

			return false;
		}

		return true;
	}

	/**
	 * Method to check whether an ID is in the edit list.
	 *
	 * @param   string   $context  The context for the session storage.
	 * @param   integer  $id       The ID of the record to add to the edit list.
	 *
	 * @return  boolean  True if the ID is in the edit list.
	 *
	 * @since   3.4
	 */
	protected function checkEditId($context, $id)
	{
		if ($id)
		{
			// Fix this check which is also a bug
			/*
			$values = (array) $this->app->getUserState($context . '.id');


			$result = in_array((int) $id, $values);
			*/
		/*	if (defined('JDEBUG') && JDEBUG)
			{
				JLog::add(
				sprintf(
				'Checking edit ID %s.%s: %d %s',
				$context,
				$id,
				(int) $result,
				str_replace("\n", ' ', print_r($values, 1))
				),
				JLog::INFO,
				'controller'
						);
			}*/

			return true;
		}
		else
		{
			// No id for a new item.
			return true;
		}
	}
}
