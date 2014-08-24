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
class JControllerDelete extends JControllerCms
{
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
		// Check for request forgeries
		$this->factory->checkSession();
		$option = $this->input->getWord('option', 'com_content');

		// Get items to remove from the request.
		$cid = $this->app->input->get('cid', array(), 'array');

		if (!is_array($cid) || count($cid) < 1)
		{
			JLog::add(JText::_($this->getPrefix() . '_NO_ITEM_SELECTED'), JLog::WARNING, 'jerror');

			return false;
		}

		try
		{
			$model = $this->getModel(null, $this->options[parent::CONTROLLER_VIEW_FOLDER]);
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
		}

		// Make sure the item ids are integers
		jimport('joomla.utilities.arrayhelper');
		JArrayHelper::toInteger($cid);

		// Remove the items.
		try
		{
			$result = $model->delete($cid);
		}
		catch (RuntimeException $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=' . $option . '&view=' . $this->options[parent::CONTROLLER_VIEW_FOLDER], false));

			return false;
		}

		$this->app->enqueueMessage(JText::plural($this->getPrefix() . '_N_ITEMS_DELETED', $result), 'message');
		$this->app->setHeader('status', '204 Deleted');

		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->input->get('option') . '&view=' . $this->options[parent::CONTROLLER_VIEW_FOLDER],
				false
			)
		);

		return true;
	}
}
