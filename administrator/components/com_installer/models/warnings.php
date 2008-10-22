<?php
/**
 * @version		$Id: templates.php 9764 2007-12-30 07:48:11Z ircmaxell $
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Import library dependencies
require_once(dirname(__FILE__).DS.'extension.php');
jimport( 'joomla.filesystem.folder' );

/**
 * Extension Manager Templates Model
 *
 * @package		Joomla
 * @subpackage	Installer
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
		global $mainframe;

		// Call the parent constructor
		parent::__construct();

	}
	
	/**
	 * Load the data
	 */
	function _loadItems()
	{
		static $messages;
		if($messages) {
			return $messages;
		}
		$messages = Array();
		$upload_dir = ini_get('upload_tmp_dir');
		if(!$upload_dir) {
			$messages[] = Array('message'=>JText::_('PHPUPLOADNOTSET'), 'description'=>JText::_('PHPUPLOADNOTSETDESC'));
		} else {
			if(!is_writeable($upload_dir)) {
				$messages[] = Array('message'=>JText::_('PHPUPLOADNOTWRITEABLE'), 'description'=>JText::_('PHPUPLOADNOTWRITEABLEDESC'));
			}
		}
		
		$config =& JFactory::getConfig();
		$tmp_path = $config->getValue('tmp_path');
		if(!$tmp_path) {
			$messages[] = Array('message'=>JText::_('JOOMLATMPNOTSET'), 'description'=>JText::_('JOOMLATMPNOTSETDESC'));
		} else {
			if(!is_writeable($tmp_path)) {
				$messages[] = Array('message'=>JText::_('JOOMLATMPNOTWRITEABLE'), 'description'=>JText::_('JOOMLATMPNOTWRITEABLEDESC'));
			}
		}
		$this->_items = $messages;
	}	
}