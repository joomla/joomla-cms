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
	const CONTROLLER_PREFIX = 0;
	const CONTROLLER_ACTIVITY = 1;
	const CONTROLLER_VIEW_FOLDER = 2;

	/*
	 * Prefix for the view and model classes
	 *
	 * @var  string
	 */
	public $prefix = null;

	/*
	 * Option to send to the model.
	*
	* @var  array
	*/
	public $options;

	/**
	 * @return  mixed  A rendered view or true
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		$context = $this->input->getWord('option', 'com_content') . $this->options[self::CONTROLLER_VIEW_FOLDER];

		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Access check.
		if (!$this->allowDelete())
		{
			// Set the internal error and also the redirect error.
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
			$this->app->enqueueMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->input->get('option') . '&controller=j.display.' . $options[self::CONTROLLER_PREFIX],
					false
				)
			);

			return false;
		}

		// Get items to remove from the request.
		$cid = $this->app->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->prefix . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');
		}
		else
		{
			// Get the model.
			$viewName     = $this->input->getWord('view', 'articles');
			$modelClass = $this->prefix . 'Model' . ucfirst($viewName);

			if (class_exists($modelClass))
			{
				$model = new $modelClass;
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
				$this->app->redirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));
			}
		}

		// Invoke the postDelete method to allow for the child class to access the model.
		if (isset($model) && isset($cid))
		{
			$this->postDeleteHook($model, $cid);
		}

		$this->app->redirect(
				JRoute::_(
					'index.php?option=' . $this->input->get('option') . '&controller=j.display.' . $options[self::CONTROLLER_PREFIX],
					false
				)
			);

	}

	/**
	 * Function that allows child controller access to model data
	 * after the item has been deleted.
	 *
	 * @param   JModel        $model  The data model object.
	 * @param   integer       $id     The validated data.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function postDeleteHook(JModel $model, $id = null)
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
	 * @since   3.3
	 */
	protected function allowDelete($data = array())
	{
		$user = JFactory::getUser();

		return ($user->authorise('core.delete', $this->input->getWord('option')) || count($user->getAuthorisedCategories($this->input->getWord('option'), 'core.delete')));
	}
}
