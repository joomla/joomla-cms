<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Joomla! update controller for the Update view
 *
 * @since  2.5.4
 */
class JoomlaupdateControllerUpdate extends JControllerLegacy
{
	/**
	 * Performs the download of the update package
	 *
	 * @return  void
	 *
	 * @since   2.5.4
	 */
	public function download()
	{
		$this->checkToken();

		$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_update.php';
		JLog::addLogger($options, JLog::INFO, array('Update', 'databasequery', 'jerror'));
		$user = JFactory::getUser();

		try
		{
			JLog::add(JText::sprintf('COM_JOOMLAUPDATE_UPDATE_LOG_START', $user->id, $user->name, JVERSION), JLog::INFO, 'Update');
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		$this->_applyCredentials();

		/** @var JoomlaupdateModelDefault $model */
		$model       = $this->getModel('Default');
		$result      = $model->download();
		$file        = $result['basename'];
		$message     = null;
		$messageType = null;

		// The validation was not successful for now just a warning.
		// TODO: In Joomla 4 this will abort the installation
		if ($result['check'] === false)
		{
			$message = JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_CHECKSUM_WRONG');
			$messageType = 'warning';

			try
			{
				JLog::add($message, JLog::INFO, 'Update');
			}
			catch (RuntimeException $exception)
			{
				// Informational log only
			}
		}

		if ($file)
		{
			JFactory::getApplication()->setUserState('com_joomlaupdate.file', $file);
			$url = 'index.php?option=com_joomlaupdate&task=update.install&' . JFactory::getSession()->getFormToken() . '=1';

			try
			{
				JLog::add(JText::sprintf('COM_JOOMLAUPDATE_UPDATE_LOG_FILE', $file), JLog::INFO, 'Update');
			}
			catch (RuntimeException $exception)
			{
				// Informational log only
			}
		}
		else
		{
			JFactory::getApplication()->setUserState('com_joomlaupdate.file', null);
			$url = 'index.php?option=com_joomlaupdate';
			$message = JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_DOWNLOADFAILED');
			$messageType = 'error';
		}

		$this->setRedirect($url, $message, $messageType);
	}

	/**
	 * Start the installation of the new Joomla! version
	 *
	 * @return  void
	 *
	 * @since   2.5.4
	 */
	public function install()
	{
		$this->checkToken('get');
		JFactory::getApplication()->setUserState('com_joomlaupdate.oldversion', JVERSION);

		$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_update.php';
		JLog::addLogger($options, JLog::INFO, array('Update', 'databasequery', 'jerror'));

		try
		{
			JLog::add(JText::_('COM_JOOMLAUPDATE_UPDATE_LOG_INSTALL'), JLog::INFO, 'Update');
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		$this->_applyCredentials();

		/** @var JoomlaupdateModelDefault $model */
		$model = $this->getModel('Default');

		$file = JFactory::getApplication()->getUserState('com_joomlaupdate.file', null);
		$model->createRestorationFile($file);

		$this->display();
	}

	/**
	 * Finalise the upgrade by running the necessary scripts
	 *
	 * @return  void
	 *
	 * @since   2.5.4
	 */
	public function finalise()
	{
		/*
		 * Finalize with login page. Used for pre-token check versions
		 * to allow updates without problems but with a maximum of security.
		 */
		if (!JSession::checkToken('get'))
		{
			$this->setRedirect('index.php?option=com_joomlaupdate&view=update&layout=finaliseconfirm');

			return false;
		}

		$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_update.php';
		JLog::addLogger($options, JLog::INFO, array('Update', 'databasequery', 'jerror'));

		try
		{
			JLog::add(JText::_('COM_JOOMLAUPDATE_UPDATE_LOG_FINALISE'), JLog::INFO, 'Update');
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		$this->_applyCredentials();

		/** @var JoomlaupdateModelDefault $model */
		$model = $this->getModel('Default');

		$model->finaliseUpgrade();

		$url = 'index.php?option=com_joomlaupdate&task=update.cleanup&' . JFactory::getSession()->getFormToken() . '=1';
		$this->setRedirect($url);
	}

	/**
	 * Clean up after ourselves
	 *
	 * @return  void
	 *
	 * @since   2.5.4
	 */
	public function cleanup()
	{
		/*
		 * Cleanup with login page. Used for pre-token check versions to be able to update
		 * from =< 3.2.7 to allow updates without problems but with a maximum of security.
		 */
		if (!JSession::checkToken('get'))
		{
			$this->setRedirect('index.php?option=com_joomlaupdate&view=update&layout=finaliseconfirm');

			return false;
		}

		$options['format'] = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_update.php';
		JLog::addLogger($options, JLog::INFO, array('Update', 'databasequery', 'jerror'));

		try
		{
			JLog::add(JText::_('COM_JOOMLAUPDATE_UPDATE_LOG_CLEANUP'), JLog::INFO, 'Update');
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		$this->_applyCredentials();

		/** @var JoomlaupdateModelDefault $model */
		$model = $this->getModel('Default');

		$model->cleanUp();

		$url = 'index.php?option=com_joomlaupdate&view=default&layout=complete';
		$this->setRedirect($url);

		try
		{
			JLog::add(JText::sprintf('COM_JOOMLAUPDATE_UPDATE_LOG_COMPLETE', JVERSION), JLog::INFO, 'Update');
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}
	}

	/**
	 * Purges updates.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function purge()
	{
		// Check for request forgeries
		$this->checkToken();

		// Purge updates
		/** @var JoomlaupdateModelDefault $model */
		$model = $this->getModel('Default');
		$model->purge();

		$url = 'index.php?option=com_joomlaupdate';
		$this->setRedirect($url, $model->_message);
	}

	/**
	 * Uploads an update package to the temporary directory, under a random name
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	public function upload()
	{
		// Check for request forgeries
		$this->checkToken();

		// Did a non Super User tried to upload something (a.k.a. pathetic hacking attempt)?
		JFactory::getUser()->authorise('core.admin') or jexit(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));

		$this->_applyCredentials();

		/** @var JoomlaupdateModelDefault $model */
		$model = $this->getModel('Default');

		try
		{
			$model->upload();
		}
		catch (RuntimeException $e)
		{
			$url = 'index.php?option=com_joomlaupdate';
			$this->setRedirect($url, $e->getMessage(), 'error');

			return;
		}

		$token = JSession::getFormToken();
		$url = 'index.php?option=com_joomlaupdate&task=update.captive&' . $token . '=1';
		$this->setRedirect($url);
	}

	/**
	 * Checks there is a valid update package and redirects to the captive view for super admin authentication.
	 *
	 * @return  array
	 *
	 * @since   3.6.0
	 */
	public function captive()
	{
		// Check for request forgeries
		$this->checkToken('get');

		// Did a non Super User tried to upload something (a.k.a. pathetic hacking attempt)?
		if (!JFactory::getUser()->authorise('core.admin'))
		{
			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		// Do I really have an update package?
		$tempFile = JFactory::getApplication()->getUserState('com_joomlaupdate.temp_file', null);

		JLoader::import('joomla.filesystem.file');

		if (empty($tempFile) || !JFile::exists($tempFile))
		{
			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		$this->input->set('view', 'upload');
		$this->input->set('layout', 'captive');

		$this->display();
	}

	/**
	 * Checks the admin has super administrator privileges and then proceeds with the update.
	 *
	 * @return  array
	 *
	 * @since   3.6.0
	 */
	public function confirm()
	{
		// Check for request forgeries
		$this->checkToken();

		// Did a non Super User tried to upload something (a.k.a. pathetic hacking attempt)?
		if (!JFactory::getUser()->authorise('core.admin'))
		{
			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		// Get the model
		/** @var JoomlaupdateModelDefault $model */
		$model = $this->getModel('default');

		// Get the captive file before the session resets
		$tempFile = JFactory::getApplication()->getUserState('com_joomlaupdate.temp_file', null);

		// Do I really have an update package?
		if (!$model->captiveFileExists())
		{
			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		// Try to log in
		$credentials = array(
			'username'  => $this->input->post->get('username', '', 'username'),
			'password'  => $this->input->post->get('passwd', '', 'raw'),
			'secretkey' => $this->input->post->get('secretkey', '', 'raw'),
		);

		$result = $model->captiveLogin($credentials);

		if (!$result)
		{
			$model->removePackageFiles();

			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		// Set the update source in the session
		JFactory::getApplication()->setUserState('com_joomlaupdate.file', basename($tempFile));

		try
		{
			JLog::add(JText::sprintf('COM_JOOMLAUPDATE_UPDATE_LOG_FILE', $tempFile), JLog::INFO, 'Update');
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		// Redirect to the actual update page
		$url = 'index.php?option=com_joomlaupdate&task=update.install&' . JFactory::getSession()->getFormToken() . '=1';
		$this->setRedirect($url);
	}

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JoomlaupdateControllerUpdate  This object to support chaining.
	 *
	 * @since   2.5.4
	 */
	public function display($cachable = false, $urlparams = array())
	{
		// Get the document object.
		$document = JFactory::getDocument();

		// Set the default view name and format from the Request.
		$vName   = $this->input->get('view', 'update');
		$vFormat = $document->getType();
		$lName   = $this->input->get('layout', 'default', 'string');

		// Get and render the view.
		if ($view = $this->getView($vName, $vFormat))
		{
			// Get the model for the view.
			/** @var JoomlaupdateModelDefault $model */
			$model = $this->getModel('Default');

			// Push the model into the view (as default).
			$view->setModel($model, true);
			$view->setLayout($lName);

			// Push document object into the view.
			$view->document = $document;
			$view->display();
		}

		return $this;
	}

	/**
	 * Applies FTP credentials to Joomla! itself, when required
	 *
	 * @return  void
	 *
	 * @since   2.5.4
	 */
	protected function _applyCredentials()
	{
		JFactory::getApplication()->getUserStateFromRequest('com_joomlaupdate.method', 'method', 'direct', 'cmd');

		if (!JClientHelper::hasCredentials('ftp'))
		{
			$user = JFactory::getApplication()->getUserStateFromRequest('com_joomlaupdate.ftp_user', 'ftp_user', null, 'raw');
			$pass = JFactory::getApplication()->getUserStateFromRequest('com_joomlaupdate.ftp_pass', 'ftp_pass', null, 'raw');

			if ($user != '' && $pass != '')
			{
				// Add credentials to the session
				if (!JClientHelper::setCredentials('ftp', $user, $pass))
				{
					JError::raiseWarning(500, JText::_('JLIB_CLIENT_ERROR_HELPER_SETCREDENTIALSFROMREQUEST_FAILED'));
				}
			}
		}
	}

	/**
	 * Checks the admin has super administrator privileges and then proceeds with the final & cleanup steps.
	 *
	 * @return  array
	 *
	 * @since   3.6.3
	 */
	public function finaliseconfirm()
	{
		// Check for request forgeries
		$this->checkToken();

		// Did a non Super User try do this?
		if (!JFactory::getUser()->authorise('core.admin'))
		{
			throw new RuntimeException(JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'), 403);
		}

		// Get the model
		/** @var JoomlaupdateModelDefault $model */
		$model = $this->getModel('default');

		// Try to log in
		$credentials = array(
			'username'  => $this->input->post->get('username', '', 'username'),
			'password'  => $this->input->post->get('passwd', '', 'raw'),
			'secretkey' => $this->input->post->get('secretkey', '', 'raw'),
		);

		$result = $model->captiveLogin($credentials);

		// The login fails?
		if (!$result)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JGLOBAL_AUTH_INVALID_PASS'), 'warning');
			$this->setRedirect('index.php?option=com_joomlaupdate&view=update&layout=finaliseconfirm');

			return false;
		}

		// Redirect back to the actual finalise page
		$this->setRedirect('index.php?option=com_joomlaupdate&task=update.finalise&' . JFactory::getSession()->getFormToken() . '=1');
	}
}
