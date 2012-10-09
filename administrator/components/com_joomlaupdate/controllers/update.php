<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       2.5.4
 */

defined('_JEXEC') or die;

/**
 * The Joomla! update controller for the Update view
 *
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 * @since       2.5.4
 */
class JoomlaupdateControllerUpdate extends JControllerLegacy
{
	/**
	 * Performs the download of the update package
	 * 
	 * @return void 
	 * 
	 * @since 2.5.4
	 */
	public function download()
	{
		$this->_applyCredentials();

		$model = $this->getModel('Default');
		$file = $model->download();

		$message = null;
		$messageType = null;

		if ($file)
		{
			JFactory::getApplication()->setUserState('com_joomlaupdate.file', $file);
			$url = 'index.php?option=com_joomlaupdate&task=update.install';
		}
		else
		{
			JFactory::getApplication()->setUserState('com_joomlaupdate.file', null);
			$url = 'index.php?option=com_joomlaupdate';
			$message = JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_DOWNLOADFAILED');
		}

		$this->setRedirect($url, $message, $messageType);
	}

	/**
	 * Start the installation of the new Joomla! version
	 *
	 * @return void
	 *
	 * @since 2.5.4
	 */
	public function install()
	{
		$this->_applyCredentials();

		$model = $this->getModel('Default');

		$file = JFactory::getApplication()->getUserState('com_joomlaupdate.file', null);
		$model->createRestorationFile($file);

		$this->display();
	}

	/**
	 * Finalise the upgrade by running the necessary scripts
	 *
	 * @return void
	 *
	 * @since 2.5.4
	 */
	public function finalise()
	{
		$this->_applyCredentials();

		$model = $this->getModel('Default');

		$model->finaliseUpgrade();

		$url = 'index.php?option=com_joomlaupdate&task=update.cleanup';
		$this->setRedirect($url);
	}

	/**
	 * Clean up after ourselves
	 * 
	 * @return void
	 *
	 * @since 2.5.4
	 */
	public function cleanup()
	{
		$this->_applyCredentials();

		$model = $this->getModel('Default');

		$model->cleanUp();

		$url = 'index.php?option=com_joomlaupdate&layout=complete';
		$this->setRedirect($url);
	}

	/**
	 * Method to display a view.
	 *
	 * @param	boolean  $cachable   If true, the view output will be cached
	 * @param	array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 *
	 * @since	2.5.4
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Get the document object.
		$document = JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName		= JRequest::getCmd('view', 'update');
		$vFormat	= $document->getType();
		$lName		= JRequest::getCmd('layout', 'default');

		// Get and render the view.
		if ($view = $this->getView($vName, $vFormat)) {
			// Get the model for the view.
			$model = $this->getModel('Default');

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->assignRef('document', $document);
			$view->display();
		}

		return $this;
	}

	/**
	 * Applies FTP credentials to Joomla! itself, when required
	 * 
	 * @return void
	 *
	 * @since	2.5.4
	 */
	protected function _applyCredentials()
	{
		jimport('joomla.client.helper');
		
		if (!JClientHelper::hasCredentials('ftp'))
		{
			$user = JFactory::getApplication()->getUserStateFromRequest('com_joomlaupdate.ftp_user', 'ftp_user', null, 'raw');
			$pass = JFactory::getApplication()->getUserStateFromRequest('com_joomlaupdate.ftp_pass', 'ftp_pass', null, 'raw');

			if ($user != '' && $pass != '')
			{
				// Add credentials to the session
				if (JClientHelper::setCredentials('ftp', $user, $pass))
				{
					$return = false;
				}
				else
				{
					$return = JError::raiseWarning('SOME_ERROR_CODE', JText::_('JLIB_CLIENT_ERROR_HELPER_SETCREDENTIALSFROMREQUEST_FAILED'));
				}
			}
		}
	}
}