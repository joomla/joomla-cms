<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Import library dependencies
require_once dirname(__FILE__).DS.'extension.php';
jimport('joomla.filesystem.folder');

/**
 * Extension Manager Templates Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @since		1.5
 */
class InstallerModelWarnings extends InstallerModel
{
	/**
	 * Extension Type
	 * @var	string
	 */
	var $_type = 'warnings';

	/**
	 * Overridden constructor
	 * @access	protected
	 */
	function __construct()
	{
		// Call the parent constructor
		parent::__construct();
	}

	/**
	 * Load the data
	 */
	function _loadItems()
	{
		static $messages;
		if ($messages) {
			return $messages;
		}
		$messages = Array();
		$upload_dir = ini_get('upload_tmp_dir');
		if (!$upload_dir) {
			$messages[] = Array('message'=>JText::_('PHPUPLOADNOTSET'), 'description'=>JText::_('PHPUPLOADNOTSETDESC'));
		} else {
			if (!is_writeable($upload_dir)) {
				$messages[] = Array('message'=>JText::_('PHPUPLOADNOTWRITEABLE'), 'description'=>JText::_('PHPUPLOADNOTWRITEABLEDESC'));
			}
		}

		$config =& JFactory::getConfig();
		$tmp_path = $config->getValue('tmp_path');
		if (!$tmp_path) {
			$messages[] = Array('message'=>JText::_('JOOMLATMPNOTSET'), 'description'=>JText::_('JOOMLATMPNOTSETDESC'));
		} else {
			if (!is_writeable($tmp_path)) {
				$messages[] = Array('message'=>JText::_('JOOMLATMPNOTWRITEABLE'), 'description'=>JText::_('JOOMLATMPNOTWRITEABLEDESC'));
			}
		}

		$bytes = JUtility::return_bytes(ini_get('memory_limit'));
		if ($bytes < (8 * 1024 * 1024)) {
			$messages[] = Array('message'=>JText::_('LOWMEMORYWARN'), 'description'=>JText::_('LOWMEMORYDESC'));
		} else if ($bytes < (16 * 1024 * 1024)) {
			$messages[] = Array('message'=>JText::_('MEDMEMORYWARN'), 'description'=>JText::_('MEDMEMORYDESC'));
		}
		$this->_items = $messages;
	}
}