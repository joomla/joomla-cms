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
 * Refresh Help Controller for global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.2
*/
class ConfigControllerApplicationRefreshhelp extends ConfigControllerBase
{

	/**
	 * Method to refresh help in global configuration.
	 *
	 * @return  bool	True on success.
	 *
	 * @since   3.2
	 */
	public function execute()
	{
		jimport('joomla.filesystem.file');

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		// Get application instance
		$app = JFactory::getApplication();

		if (($data = file_get_contents('http://help.joomla.org/helpsites.xml')) === false)
		{
			$app->redirect(JRoute::_('index.php?option=com_config', false), JText::_('COM_CONFIG_ERROR_HELPREFRESH_FETCH'), 'error');
		}
		elseif (!JFile::write(JPATH_BASE . '/help/helpsites.xml', $data))
		{
			$app->redirect(JRoute::_('index.php?option=com_config', false), JText::_('COM_CONFIG_ERROR_HELPREFRESH_ERROR_STORE'), 'error');
		}
		else
		{
			$app->redirect(JRoute::_('index.php?option=com_config', false), JText::_('COM_CONFIG_HELPREFRESH_SUCCESS'));
		}
	}
}
