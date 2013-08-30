<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Controller for global configuration
 *
 * @package    Joomla.Administrator
 * @subpackage com_config
 * @since      3.2
 */
class ConfigControllerApplication extends JControllerLegacy
{
	/**
	 * Returns the updated options for help site selector
	 *
	 * @return  void
	 *
	 * @since   3.2
	 * @throws  Exception
	 */
	public function refreshHelp()
	{
		jimport('joomla.filesystem.file');

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		if (($data = file_get_contents('http://update.joomla.org/helpsites/helpsites.xml')) === false)
		{
			throw new Exception(JText::_('COM_CONFIG_ERROR_HELPREFRESH_FETCH'), 500);
		}
		elseif (!JFile::write(JPATH_BASE . '/help/helpsites.xml', $data))
		{
			throw new Exception(JText::_('COM_CONFIG_ERROR_HELPREFRESH_ERROR_STORE'), 500);
		}

		if ($this->input->get('format') == 'json')
		{
			$options = JHelp::createSiteList(JPATH_ADMINISTRATOR . '/help/helpsites.xml');
			echo json_encode($options);
			JFactory::getApplication()->close();
		}
	}
}
