<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 */
class ConfigControllerApplication extends JController
{
	/**
	 * Class Constructor
	 *
	 * @param	array	$config		An optional associative array of configuration settings.
	 * @return	void
	 * @since	1.5
	 */
	function __construct($config = array())
	{
		parent::__construct($config);

		// Map the apply task to the save method.
		$this->registerTask('apply', 'save');
	}

	/**
	 * Method to save the configuration.
	 *
	 * @return	bool	True on success, false on failure.
	 * @since	1.5
	 */
	public function save()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('Invalid_Token'));

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorize('core.admin'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
			return;
		}

		// Set FTP credentials, if given.
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= $this->getModel('Application');
		$form	= $model->getForm();
		$data	= JRequest::getVar('jform', array(), 'post', 'array');

		// Validate the posted data.
		$return = $model->validate($form, $data);

		// Check for validation errors.
		if ($return === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				} else {
					$app->enqueueMessage($errors[$i], 'notice');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_config.config.global.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_config&view=application', false));
			return false;
		}

		// Attempt to save the configuration.
		$data	= $return;
		$return = $model->save($data);

		// Check the return value.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_config.config.global.data', $data);

			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('JError_Save_Failed', $model->getError());
			$this->setRedirect('index.php?option=com_config&view=application', $message, 'error');
			return false;
		}

		// Set the success message.
		$message = JText::_('Config_Save_Success');

		// Set the redirect based on the task.
		switch ($this->_task)
		{
			case 'apply':
				$this->setRedirect('index.php?option=com_config', $message);
				break;

			case 'save':
			default:
				$this->setRedirect('index.php', $message);
				break;
		}

		return true;
	}

	/**
	 * Cancel operation
	 */
	function cancel()
	{
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorize('core.admin', 'com_config'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
			return;
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_config.config.global.data',	null);

		$this->setRedirect('index.php');
	}

	function refreshHelp()
	{
		jimport('joomla.filesystem.file');

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		if (($data = file_get_contents('http://help.joomla.org/helpsites-15.xml')) === false) {
			$this->setRedirect('index.php?option=com_config', JText::_('HELPREFRESH_ERROR_FETCH'), 'error');
		} else if (!JFile::write(JPATH_BASE.DS.'help'.DS.'helpsites-15.xml', $data)) {
			$this->setRedirect('index.php?option=com_config', JText::_('HELPREFRESH_ERROR_STORE'), 'error');
		} else {
			$this->setRedirect('index.php?option=com_config', JText::_('HELPREFRESH_SUCCESS'));
		}
	}
}
