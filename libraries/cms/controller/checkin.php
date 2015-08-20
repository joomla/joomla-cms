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
class JControllerCheckin extends JControllerCms
{
	/**
	 * Method to checkin a record.
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

		$this->viewName = $this->input->getWord('view', 'articles');
		$ids            = $this->input->get('cid', array(), 'array');

		// If there are no id's to checkin then put in an error, set the redirect and return.
		if (empty($ids))
		{
			$this->app->enqueueMessage(JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 'error');
			$this->setRedirect('index.php?option=' . $this->input->get('option', 'com_cpanel') . '&view=' . $this->viewName);

			return false;
		}

		try
		{
			$model = $this->getModel();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
		}

		// Access check.
		if (!JFactory::getUser()->authorise($this->permission, $model->getState('component.option')))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

			return false;
		}

		// Check in the items.
		$this->app->enqueueMessage(JText::plural('JLIB_CONTROLLER_N_ITEMS_CHECKED_IN', $model->checkin($ids)), 'message');
		$this->setRedirect('index.php?option=' . $this->input->get('option', 'com_cpanel') . '&view=' . $this->viewName);

		return true;
	}
}
