<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Base Display Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  controller
 * @since       3.2
*/

class JControllerUpdate extends JControllerCmsbase
{
	/*
	 * Prefix for the view and model classes
	 *
	 * @var  string
	 */
	public $prefix = 'Content';

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
			$this->app->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$tasks = explode('.', $this->input->get('task'));
		$this->viewName     = ucfirst($tasks[parent::CONTROLLER_VIEW_FOLDER]);
		$saveFormat   = JFactory::getDocument()->getType();

		try
		{
			$model = $this->getModel();
		}
		catch (ErrorException $e)
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
		if ($saveFormat == 'json')
		{
			return $model->save($data);
		}

		// Must load after serving service-requests
		$form  = $model->getForm();

		// Validate the posted data.
		return  $model->validate($form, $this->data);
	}
}
