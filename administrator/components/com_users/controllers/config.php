<?php
/**
 * @version		$Id: config.php 20196 2011-01-09 02:40:25Z ian $
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * The Users Configuration Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @version		1.0
 */
class UsersControllerConfig extends JController
{
	/**
	 * Method to import the configuration via string or upload.
	 *
	 * @return	bool	True on success, false on failure.
	 * @since	1.0
	 */
	public function import()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the configuration values from the Request.
		$string = JRequest::getVar('configString', '', 'post', 'string', JREQUEST_ALLOWHTML);
		$file	= JRequest::getVar('configFile', array(), 'files', 'array');
		$return	= null;

		// Handle the possible import methods.
		if (!empty($file) && ($file['error'] == 0) && ($file['size'] > 0) && (is_readable($file['tmp_name'])))
		{
			// Handle import via uploaded file.
			$string = implode("\n", file($file['tmp_name']));
			$model	= $this->getModel('Config');
			$return	= $model->import($string);
		}
		elseif (strlen($string) > 1)
		{
			// Handle import via pasted string.
			$model	= $this->getModel('Config');
			$return	= $model->import($string);
		}

		// Handle the response.
		if ($return === false)
		{
			$message = JText::sprintf('COM_USERS_CONFIG_IMPORT_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_users&view=config&layout=import&tmpl=component', $message, 'notice');
			return false;
		}
		else
		{
			$this->setRedirect('index.php?option=com_users&view=config&layout=close&tmpl=component');
			return true;
		}
	}

	/**
	 * Method to export the configuration via download.
	 *
	 * @return	void
	 * @since	1.0
	 */
	public function export()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the component configuration values.
		$app	= JFactory::getApplication();
		$config = JComponentHelper::getParams('com_users');
		$string	= (string)$config;

		// Send file headers.
		header('Content-type: application/force-download');
		header('Content-Transfer-Encoding: Binary');
		header('Content-length: '.strlen($string));
		header('Content-disposition: attachment; filename="users.config.ini"');
		header('Pragma: no-cache');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Expires: 0');

		// Print the configuration values.
		echo $string;

		$app->close();
	}

	/**
	 * Method to save the configuration.
	 *
	 * @return	bool	True on success, false on failure.
	 * @since	1.0
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Save the configuration.
		$model	= $this->getModel('Config');
		$return	= $model->save();

		if ($return === false) {
			$message = JText::sprintf('COM_USERS_CONFIG_SAVE_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_users&view=config&tmpl=component', $message, 'notice');
			return false;
		}
		else {
			$this->setRedirect('index.php?option=com_users&view=config&layout=close&tmpl=component');
			return true;
		}
	}
}
