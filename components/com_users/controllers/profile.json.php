<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Profile controller class for Users.
 *
 * @package     Joomla.Site
 * @subpackage  com_users
 * @since       1.6
 */
class UsersControllerProfile extends UsersController
{
	/**
	 * Returns the updated options for help site selector
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @throws  Exception
	 */
	public function gethelpsites()
	{
		jimport('joomla.filesystem.file');

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		if (($data = file_get_contents('http://update.joomla.org/helpsites/helpsites.xml')) === false)
		{
			throw new Exception(JText::_('COM_CONFIG_ERROR_HELPREFRESH_FETCH'), 500);
		}
		elseif (!JFile::write(JPATH_ADMINISTRATOR . '/help/helpsites.xml', $data))
		{
			throw new Exception(JText::_('COM_CONFIG_ERROR_HELPREFRESH_ERROR_STORE'), 500);
		}

		$options = JHelp::createSiteList(JPATH_ADMINISTRATOR . '/help/helpsites.xml');
		echo json_encode($options);
		JFactory::getApplication()->close();
	}
}
