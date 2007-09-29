<?php
/**
* @version		$Id: view.html.php 8582 2007-08-27 14:37:02Z jinx $
* @package		Joomla
* @subpackage	Media
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Media
 * @since 1.0
 */
class MediaViewImages extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		JHTML::_('script'    , 'popup-imagemanager.js', 'administrator/components/com_media/assets/');
		JHTML::_('stylesheet', 'popup-imagemanager.css', 'administrator/components/com_media/assets/');

		JHTML::_('behavior.uploader', 'file-upload', array('onAllComplete' => 'function(){ ImageManager.refreshFrame(); }'));

		/*
		 * Display form for FTP credentials?
		 * Don't set them here, as there are other functions called before this one if there is any file write operation
		 */
		jimport('joomla.client.helper');
		$ftp = !JClientHelper::hasCredentials('ftp');

		$this->assignRef('session', JFactory::getSession());
		$this->assignRef('config', JComponentHelper::getParams('com_media'));
		$this->assignRef('state', $this->get('state'));
		$this->assignRef('folderList', $this->get('folderList'));
		$this->assign('require_ftp', $ftp);

		parent::display($tpl);
	}
}
