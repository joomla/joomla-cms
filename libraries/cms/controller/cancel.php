<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Cancel Controller
 *
 * @package     Joomla.Libraries
 * @subpackage  Controller
 * @since       3.4
 */
class JControllerCancel extends JControllerCms
{
	/**
	 * Generic method to cancel
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.4
	 */
	public function execute()
	{
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.edit'))
		{
			$this->app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'));
			$this->setRedirect('index.php');

			return false;
		}

		$redirectUrl = '';

		// By default we go to the default component view of the current component
		$redirectUrl     .= $this->input->getWord('option', 'com_content');

		if (!empty($this->options[parent::CONTROLLER_VIEW_FOLDER]))
		{
			$redirectUrl .= '&view=' . $this->options[parent::CONTROLLER_VIEW_FOLDER];
		}

		try
		{
			$model = $this->getModel();
		}
		catch (RuntimeException $e)
		{
			throw new RuntimeException($e->getMessage(), $e->getCode());
		}

		$keyName = $model->getKeyName();

		$input = $this->input;
		$pk    = $input->getInt($keyName, 0);

		// If we are cancelling an item that already exists then we should
		// check if back in.
		if ($pk != 0)
		{
			try
			{
				$model->checkin($pk);
			}
			catch (Exception $e)
			{
				$msg = $e->getMessage();
				$this->setRedirect($redirectUrl, $msg, 'warning');

				return false;
			}
		}

		// Clear the form state
		$key = $model->getContext() . '.jform.data';
		$this->setUserState($key, null);

		// By default cancel goes to the default component view.
		$this->setRedirect(JRoute::_('index.php?option=' . $redirectUrl, false));

		return true;
	}
}
