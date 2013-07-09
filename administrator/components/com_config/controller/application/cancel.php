<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Cancel Controller for global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.1
 */
class ConfigControllerApplicationCancel extends JControllerBase
{

	/**
	 * Method to cancel global configuration.
	 *
	 * @return  bool	True on success.
	 *
	 * @since   1.5
	 */
	public function execute()
	{
		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', 'com_config'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));

			return;
		}

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_config.config.global.data',	null);

		$app->redirect(JRoute::_('index.php', false));

		return true;
	}
}
