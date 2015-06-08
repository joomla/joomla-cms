<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Refresh Help Controller for global configuration
 *
 * @since  3.2
 */
class ConfigControllerApplicationRefreshhelp extends JControllerBase
{
	/**
	 * Application object - Redeclared for proper typehinting
	 *
	 * @var    JApplicationCms
	 * @since  3.2
	 */
	protected $app;

	/**
	 * Method to refresh help in global configuration.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		jimport('joomla.filesystem.file');

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		if (($data = file_get_contents('http://help.joomla.org/helpsites.xml')) === false)
		{
			$this->app->enqueueMessage(JText::_('COM_CONFIG_ERROR_HELPREFRESH_FETCH'), 'error');
			$this->app->redirect(JRoute::_('index.php?option=com_config', false));
		}
		elseif (!JFile::write(JPATH_BASE . '/help/helpsites.xml', $data))
		{
			$this->app->enqueueMessage(JText::_('COM_CONFIG_ERROR_HELPREFRESH_ERROR_STORE'), 'error');
			$this->app->redirect(JRoute::_('index.php?option=com_config', false));
		}
		else
		{
			$this->app->enqueueMessage(JText::_('COM_CONFIG_HELPREFRESH_SUCCESS'), 'error');
			$this->app->redirect(JRoute::_('index.php?option=com_config', false));
		}
	}
}
