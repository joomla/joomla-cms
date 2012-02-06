<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Import library dependencies
jimport('joomla.application.component.modellist');
jimport('joomla.filesystem.folder');

/**
 * Extension Manager Templates Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.6
 */
class InstallerModelWarnings extends JModelList
{
	/**
	 * Extension Type
	 * @var	string
	 */
	var $type = 'warnings';

	/**
	 * Load the data.
	 *
	 * @since	1.6
	 */
	function getItems()
	{
		static $messages;
		if ($messages) {
			return $messages;
		}
		$messages = array();
		$file_uploads = ini_get('file_uploads');
		if(!$file_uploads)
		{
			$messages[] = array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_FILEUPLOADSDISABLED'), 'description'=>JText::_('COM_INSTALLER_MSG_WARNINGS_FILEUPLOADISDISABLEDDESC'));
		}


		$upload_dir = ini_get('upload_tmp_dir');
		if (!$upload_dir) {
			$messages[] = array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSET'), 'description'=>JText::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSETDESC'));
		} else {
			if (!is_writeable($upload_dir)) {
				$messages[] = array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTWRITEABLE'), 'description'=>JText::sprintf('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTWRITEABLEDESC', $upload_dir));
			}
		}

		$config = JFactory::getConfig();
		$tmp_path = $config->get('tmp_path');
		if (!$tmp_path) {
			$messages[] = array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTSET'), 'description'=>JText::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTSETDESC'));
		} else {
			if (!is_writeable($tmp_path)) {
				$messages[] = array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTWRITEABLE'), 'description'=>JText::sprintf('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTWRITEABLEDESC', $tmp_path));
			}
		}

		//$memory_limit = $this->return_bytes(ini_get('memory_limit'));
		$memory_limit = JHtml::_('number.bytes', ini_get('memory_limit'));
		if ($memory_limit < (8 * 1024 * 1024)) { // 8MB
			$messages[] = array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_LOWMEMORYWARN'), 'description'=>JText::_('COM_INSTALLER_MSG_WARNINGS_LOWMEMORYDESC'));
		} elseif ($memory_limit < (16 * 1024 * 1024)) { //16MB
			$messages[] = array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_MEDMEMORYWARN'), 'description'=>JText::_('COM_INSTALLER_MSG_WARNINGS_MEDMEMORYDESC'));
		}


		//$post_max_size = $this->return_bytes(ini_get('post_max_size'));
		$post_max_size = JHtml::_('number.bytes', ini_get('post_max_size'));
		//$upload_max_filesize = $this->return_bytes(ini_get('upload_max_filesize'));
		$upload_max_filesize = JHtml::_('number.bytes', ini_get('upload_max_filesize'));

		if($post_max_size < $upload_max_filesize)
		{
			$messages[] = array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_UPLOADBIGGERTHANPOST'), 'description'=>JText::_('COM_INSTALLER_MSG_WARNINGS_UPLOADBIGGERTHANPOSTDESC'));
		}

		if($post_max_size < (4 * 1024 * 1024)) // 4MB
		{
			$messages[] = array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_SMALLPOSTSIZE'), 'description'=>JText::_('COM_INSTALLER_MSG_WARNINGS_SMALLPOSTSIZEDESC'));
		}

		if($upload_max_filesize < (4 * 1024 * 1024)) // 4MB
		{
			$messages[] = array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZE'), 'description'=>JText::_('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZEDESC'));
		}


		return $messages;
	}
}
