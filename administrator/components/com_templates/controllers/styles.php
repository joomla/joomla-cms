<?php
/**
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Template styles list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 * @since		1.6
 */
class TemplatesControllerStyles extends JControllerAdmin
{
	/**
	 * Method to clone and existing template style.
	 */
	public function duplicate()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$pks = JRequest::getVar('cid', array(), 'post', 'array');

		try
		{
			if (empty($pks)) {
				throw new Exception(JText::_('COM_TEMPLATES_NO_TEMPLATE_SELECTED'));
			}

			JArrayHelper::toInteger($pks);

			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(JText::_('COM_TEMPLATES_SUCCESS_DUPLICATED'));
		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_templates&view=styles');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @since	1.6
	 */
	public function getModel($name = 'Style', $prefix = 'TemplatesModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Method to set the home template for a client.
	 *
	 * @since	1.6
	 */
	public function setDefault()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$pks = JRequest::getVar('cid', array(), 'post', 'array');

		try
		{
			if (empty($pks)) {
				throw new Exception(JText::_('COM_TEMPLATES_NO_TEMPLATE_SELECTED'));
			}

			JArrayHelper::toInteger($pks);

			// Pop off the first element.
			$id = array_shift($pks);
			$model = $this->getModel();
			$model->setHome($id);
			$this->setMessage(JText::_('COM_TEMPLATES_SUCCESS_HOME_SET'));

		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_templates&view=styles');
	}
	/**
	 * Method to unset the default template for a client and for a language
	 *
	 * @since	1.6
	 */
	public function unsetDefault()
	{
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$pks = JRequest::getVar('cid', array(), 'get', 'array');
		JArrayHelper::toInteger($pks);

		try
		{
			if (empty($pks)) {
				throw new Exception(JText::_('COM_TEMPLATES_NO_TEMPLATE_SELECTED'));
			}

			// Pop off the first element.
			$id = array_shift($pks);
			$model = $this->getModel();
			$model->unsetHome($id);
			$this->setMessage(JText::_('COM_TEMPLATES_SUCCESS_HOME_UNSET'));

		}
		catch (Exception $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}

		$this->setRedirect('index.php?option=com_templates&view=styles');
	}
}
