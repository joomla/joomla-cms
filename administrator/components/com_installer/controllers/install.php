<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function install()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$model = $this->getModel('install');

		// Initialize (download / prepare package)
		if ($model->initialize())
		{

			// Review Package
			$package = $model->getState('package');

			// Verify Requirements
			if (!empty($package['dir']) && !empty($package['type']))
			{

				// Redirect to installer.install
				$app->setUserState('com_installer.package', $package);
				$this->setRedirect(JRoute::_('index.php?' . http_build_query(array(
					'option' => 'com_installer',
					'task' => 'installer.install',
					JSession::getFormToken() => '1'
					)), false));

			}
			else
			{
				$this->setMessage(JText::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'), 'error');
			}

		}
		else
		{
			$this->setMessage(JText::_('COM_INSTALLER_UNABLE_TO_FIND_INSTALL_PACKAGE'), 'error');
		}

		$this->setRedirect(JRoute::_('index.php?option=com_installer&view=install', false));
	}

}
