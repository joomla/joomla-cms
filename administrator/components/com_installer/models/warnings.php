<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
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
	 * Return the byte value of a particular string.
	 *
	 * @param	string	String optionally with G, M or K suffix
	 * @return	int		size in bytes
	 * @since 1.6
	 */
	function return_bytes($val)
	{
		$val = trim($val);
		$last = strtolower($val{strlen($val)-1});
		switch($last) {
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}

		return $val;
	}

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
		$messages = Array();
		$upload_dir = ini_get('upload_tmp_dir');
		if (!$upload_dir) {
			$messages[] = Array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSET'), 'description'=>JText::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSETDESC'));
		} else {
			if (!is_writeable($upload_dir)) {
				$messages[] = Array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTWRITEABLE'), 'description'=>JText::sprintf('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTWRITEABLEDESC', $upload_dir));
			}
		}

		$config = JFactory::getConfig();
		$tmp_path = $config->get('tmp_path');
		if (!$tmp_path) {
			$messages[] = Array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTSET'), 'description'=>JText::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTSETDESC'));
		} else {
			if (!is_writeable($tmp_path)) {
				$messages[] = Array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTWRITEABLE'), 'description'=>JText::sprintf('COM_INSTALLER_MSG_WARNINGS_JOOMLATMPNOTWRITEABLEDESC', $tmp_path));
			}
		}

		$bytes = $this->return_bytes(ini_get('memory_limit'));
		if ($bytes < (8 * 1024 * 1024)) {
			$messages[] = Array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_LOWMEMORYWARN'), 'description'=>JText::_('COM_INSTALLER_MSG_WARNINGS_LOWMEMORYDESC'));
		} else if ($bytes < (16 * 1024 * 1024)) {
			$messages[] = Array('message'=>JText::_('COM_INSTALLER_MSG_WARNINGS_MEDMEMORYWARN'), 'description'=>JText::_('COM_INSTALLER_MSG_WARNINGS_MEDMEMORYDESC'));
		}
		return $messages;
	}
}