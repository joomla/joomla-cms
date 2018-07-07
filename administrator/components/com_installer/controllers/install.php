<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Installer controller for Joomla! installer class.
 *
 * @since  1.5
 */
class InstallerControllerInstall extends JControllerLegacy
{
	/**
	 * Install an extension.
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function install()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		/** @var InstallerModelInstall $model */
		$model = $this->getModel('install');

		// TODO: Reset the users acl here as well to kill off any missing bits.
		$result = $model->install();

		$app = JFactory::getApplication();
		$redirect_url = $app->getUserState('com_installer.redirect_url');

		if (!$redirect_url)
		{
			$redirect_url = base64_decode($app->input->get('return'));
		}

		// Don't redirect to an external URL.
		if (!JUri::isInternal($redirect_url))
		{
			$redirect_url = '';
		}

		if (empty($redirect_url))
		{
			$redirect_url = JRoute::_('index.php?option=com_installer&view=install', false);
		}
		else
		{
			// Wipe out the user state when we're going to redirect.
			$app->setUserState('com_installer.redirect_url', '');
			$app->setUserState('com_installer.message', '');
			$app->setUserState('com_installer.extension_message', '');
		}

		$this->setRedirect($redirect_url);

		return $result;
	}

	/**
	 * Install an extension from drag & drop ajax upload.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function ajax_upload()
	{
		$app = JFactory::getApplication();
		$message = $app->getUserState('com_installer.message');

		// Do install
		$result = $this->install();

		// Get redirect URL
		$redirect = $this->redirect;

		// Push message queue to session because we will redirect page by Javascript, not $app->redirect().
		// The "application.queue" is only set in redirect() method, so we must manually store it.
		$app->getSession()->set('application.queue', $app->getMessageQueue());

		header('Content-Type: application/json');

		echo new JResponseJson(array('redirect' => $redirect), $message, !$result);

		exit();
	}
}
