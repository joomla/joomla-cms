<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Base Update Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  Controller
 * @since       3.4
*/
class JControllerUpdate extends JControllerCms
{
	/*
	 * Permission needed for the action. Defaults to most restrictive
	*
	* @var  string
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
		parent::execute();

		// Check if the user is authorized to do this.
		if ($this->app->isAdmin() && !JFactory::getUser()->authorise('core.manage'))
		{
			$this->setRedirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$this->viewName = $this->options[parent::CONTROLLER_VIEW_FOLDER];
		$saveFormat   = $this->doc->getType();

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
			return $model->update($data);
		}

		// Must load after serving service-requests
		$form  = $model->getForm();

		// Validate the posted data.
		if (!$model->validate($form, $data))
		{
			// @todo Throw an appropriate exception/error notice here
		}

		try
		{
			$model->update($data);
		}
		catch (Exception $e)
		{
			throw new RuntimeException ($e->getMessage(), $e->getCode());
		}

		$context = $this->config['option'] . '.edit.' . $this->viewName;
		$urlVar = $model->getTable()->getKeyName();

		// Redirect the user and adjust session state based on the chosen task.
		switch ($this->options[parent::CONTROLLER_ACTIVITY])
		{
			case 'apply':
				// Set the record data in the session.
				$modelState = $model->getState();
				$recordId = $modelState->get($this->viewName . '.id');
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
						'index.php?option=' . $this->config['option'] . '&view=' . $this->viewName
						. $this->getRedirectToListAppend(), false
					)
				);
				break;
		}

		return true;
	}
}
