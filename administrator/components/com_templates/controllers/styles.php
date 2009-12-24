<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Template styles list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesControllerStyles extends JController
{
	/**
	 * Display is not supported by this class.
	 */
	public function display()
	{
	}

	/**
	 * Proxy for getModel.
	 */
	public function &getModel($name = 'Style', $prefix = 'TemplatesModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Method to clone and existing template style.
	 */
	public function duplicate()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$pks = JRequest::getVar('cid', array(), 'post', 'array');

		try
		{
			if (empty($pks)) {
				throw new Exception(JText::_('JError_No_items_selected'));
			}
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(JText::sprintf('Templates_Success_N_Duplicated', count($pks)));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_templates&view=styles');
	}

	/**
	 * Method to delete a list of selected records.
	 */
	public function delete()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$pks = JRequest::getVar('cid', array(), 'post', 'array');

		try
		{
			if (empty($pks)) {
				throw new Exception(JText::_('JError_No_items_selected'));
			}
			$model = $this->getModel();
			$model->delete($pks);
			$this->setMessage(JText::sprintf('JController_N_Items_deleted', count($pks)));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_templates&view=styles');
	}

	/**
	 * Method to set the home template for a client.
	 */
	public function sethome()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$pks = JRequest::getVar('cid', array(), 'post', 'array');

		try
		{
			if (empty($pks)) {
				throw new Exception(JText::_('JError_No_items_selected'));
			}

			// Pop off the first element.
			$id = array_shift($pks);
			$model = $this->getModel();
			$model->setHome($id);
			$this->setMessage(JText::_('Templates_Success_Home_set'));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_templates&view=styles');
	}
}