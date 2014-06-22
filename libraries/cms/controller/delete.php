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
class JControllerDelete extends JControllerCmsbase
{
	/*
	 * Prefix for the view and model classes
	 *
	 * @var  string
	 */
	public $prefix = null;

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
		$option = $this->input->getWord('option', 'com_content');
		$context = $option . $this->options[self::CONTROLLER_VIEW_FOLDER];

		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Access check.
		if (!$this->allowDelete())
		{
			// Set the internal error and also the redirect error.
			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->input->get('option') . '&controller=j.display.' . $this->options[parent::CONTROLLER_PREFIX],
					false
				)
			);

			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'), 401);
		}

		// Get items to remove from the request.
		$cid = $this->app->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');

			return false;
		}

		// Get the model.
		$viewName = $this->input->getWord('view', 'articles');

		try
		{
			$model = $this->getModel($this->prefix, ucfirst($viewName));
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		// Make sure the item ids are integers
		jimport('joomla.utilities.arrayhelper');
		JArrayHelper::toInteger($cid);

		// Remove the items.
		if ($model->delete($cid))
		{
			$this->app->enqueueMessage(JText::plural($this->prefix . '_N_ITEMS_DELETED', count($cid)), 'notice');
		}
		else
		{
			$this->app->enqueueMessage('NO_ITEMS_FOUND', 'error');
			$this->setRedirect(JRoute::_('index.php?option=' . $option . '&view=' . $this->view_list, false));
		}

		// Invoke the postDelete method to allow for the child class to access the model.
		if (isset($model) && isset($cid))
		{
			$this->postDeleteHook($model, $cid);
		}

		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->input->get('option') . '&controller=j.display.' . $this->options[parent::CONTROLLER_PREFIX],
				false
			)
		);

		return true;
	}

	/**
	 * Function that allows child controller access to model data
	 * after the item has been deleted.
	 *
	 * @param   JModelCms   $model  The data model object.
	 * @param   integer     $id     The validated data.
	 *
	 * @return  void
	 *
	 * @since   3.4
	 */
	protected function postDeleteHook(JModelCms $model, $id = null)
	{
	}

	/**
	 * Method to check if you can delete record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   3.4
	 */
	protected function allowDelete($data = array())
	{
		$user = JFactory::getUser();

		return ($user->authorise('core.delete', $this->input->getWord('option')) || count($user->getAuthorisedCategories($this->input->getWord('option'), 'core.delete')));
	}
}
