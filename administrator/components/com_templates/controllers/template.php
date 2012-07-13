<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('InstallerModelInstall', JPATH_ADMINISTRATOR . '/components/com_installer/models/install.php');

/**
 * Template style controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 * @since       1.6
 */
class TemplatesControllerTemplate extends JControllerLegacy
{
	/**
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_templates&view=templates');
	}

	public function copy()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$this->input->set('installtype', 'folder');
		$newName = $this->input->get('new_name');
		$newNameRaw = JRequest::getVar('new_name', null, '', 'string');
		$templateID = $this->input->getInt('id', 0);
		$this->setRedirect('index.php?option=com_templates&view=template&id=' . $templateID);
		$model = $this->getModel('Template', 'TemplatesModel');
		$model->setState('new_name', $newName);
		$model->setState('tmp_prefix', uniqid('template_copy_'));
		$model->setState('to_path', JFactory::getConfig()->get('tmp_path') . '/' . $model->getState('tmp_prefix'));

		// Process only if we have a new name entered
		if (strlen($newName) > 0)
		{
			if (!JFactory::getUser()->authorise('core.create', 'com_templates'))
			{
				// User is not authorised to delete
				JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_CREATE_NOT_PERMITTED'));
				return false;
			}

			// Set FTP credentials, if given
			JClientHelper::setCredentialsFromRequest('ftp');

			// Check that new name is valid
			if (($newNameRaw !== null) && ($newName !== $newNameRaw)) {
				JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_INVALID_TEMPLATE_NAME'));
				return false;
			}

			// Check that new name doesn't already exist
			if (!$model->checkNewName())
			{
				JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_DUPLICATE_TEMPLATE_NAME'));
				return false;
			}

			// Check that from name does exist and get the folder name
			$fromName = $model->getFromName();
			if (!$fromName)
			{
				JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_INVALID_FROM_NAME'));
				return false;
			}

			// Call model's copy method
			if (!$model->copy())
			{
				JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_COULD_NOT_COPY'));
				return false;
			}

			// Call installation model
			$this->input->set('install_directory', JFactory::getConfig()->get('tmp_path') . '/' . $model->getState('tmp_prefix'));
			$installModel = $this->getModel('Install', 'InstallerModel');
			JFactory::getLanguage()->load('com_installer');
			if (!$installModel->install())
			{
				JError::raiseWarning(403, JText::_('COM_TEMPLATES_ERROR_COULD_NOT_INSTALL'));
				return false;
			}

			$this->setMessage(JText::sprintf('COM_TEMPLATES_COPY_SUCCESS', $newName));
			$model->cleanup();
			return true;

		}
	}
}
