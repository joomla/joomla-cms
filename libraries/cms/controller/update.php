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
 * Base Update Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  Controller
 * @since       3.4
 */
class JControllerUpdate extends JControllerCms
{
	/**
	 * Permission needed for the action. Defaults to most restrictive
	 *
	 * @var   string
	 * @since 3.4
	 */
	public $permission = 'core.edit';

	/**
	 * Method to update a record.
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
		// Check for request forgeries
		$this->factory->checkSession();

		// Check if the user is authorized to do this.
		if ($this->app->isAdmin() && !JFactory::getUser()->authorise('core.manage'))
		{
			$this->setRedirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		$this->viewName = $this->options[parent::CONTROLLER_VIEW_FOLDER];
		$saveFormat     = $this->doc->getType();

		try
		{
			$model = $this->getModel();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		// Access check.
		if (!JFactory::getUser()->authorise($this->permission, $model->getState('component.option')))
		{
			throw new RuntimeException(JText::_('JERROR_ALERTNOAUTHOR'), 401);
		}

		$data  = $this->input->post->get('jform', array(), 'array');

		// Handle service requests
		// @todo Potential security risk - we are't validating the data. Data must already be validated if in json view
		if ($saveFormat == 'json')
		{
			try
			{
				$model->update($data);

				return true;
			}
			catch (Exception $e)
			{
				return false;
			}
		}

		// Must load after serving service-requests
		// @todo to fix the above validation risk we need to be able to load the backend form in the frontend
		$form = $model->getForm();

		$context = $this->config['option'] . '.edit.' . $this->viewName;
		$urlVar  = $model->getTable()->getKeyName();

		// Validate the posted data.
		try
		{
			$model->validate($form, $data);
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), $e->getCode());

			// Set the record data in the session and redirect back to the item
			$modelState = $model->getState();
			$recordId   = $modelState->get($this->viewName . '.id');
			$this->setUserState($context . '.data', null);

			// Redirect back to the edit screen.
			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->config['option'] . '&view=' . $this->viewName
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);

			return false;
		}

		try
		{
			$model->update($data);
		}
		catch (Exception $e)
		{
			throw new RuntimeException ($e->getMessage(), $e->getCode());
		}

		$pk = $this->input->getInt($urlVar, 0);

		// If we are closing an item that already exists then we should check it back in.
		if ($pk != 0)
		{
			try
			{
				$model->checkin($pk);
			}
			catch (Exception $e)
			{
				// Enqueue the error message. We will then perform the appropriate redirect.
				// This is only checking back in the the item so it doesn't matter too much.
				$this->app->enqueueMessage($e->getMessage());
			}
		}


		// Redirect the user and adjust session state based on the chosen task.
		switch ($this->options[parent::CONTROLLER_ACTIVITY])
		{
			case 'apply':
				// Set the record data in the session.
				$modelState = $model->getState();
				$recordId   = $modelState->get($this->viewName . '.id');
				$this->setUserState($context . '.data', null);
				$model->checkout($recordId);

				// Redirect back to the edit screen.
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->config['option'] . '&view=' . $this->viewName
						. $this->getRedirectToItemAppend($recordId, $urlVar), false
					)
				);
				break;

			case 'save2new':
				// Clear the record id and data from the session.
				$this->setUserState($context . '.data', null);

				// Redirect back to the edit screen.
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->config['option'] . '&view=' . $this->viewName
						. $this->getRedirectToItemAppend(null, $urlVar), false
					)
				);
				break;

			// What we label as save to close
			default:
				// Clear the record id and data from the session.
				$this->setUserState($context . '.data', null);

				// Redirect to the list screen.
				$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->config['option'] . '&view=' . FOFInflector::pluralize($this->viewName)
						. $this->getRedirectToListAppend(), false
					)
				);
				break;
		}

		return true;
	}
}
