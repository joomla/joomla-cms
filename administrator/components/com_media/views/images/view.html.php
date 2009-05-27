<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Media
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Media
 * @since 1.0
 */
class MediaViewImages extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		$config = &JComponentHelper::getParams('com_media');

		$app = JFactory::getApplication();
		$append = '';
		if ($app->getClientId() == 1) $append = 'administrator/';

		JHtml::_('script'    , 'popup-imagemanager.js', $append .'components/com_media/assets/');
		JHtml::_('stylesheet', 'popup-imagemanager.css', $append .'components/com_media/assets/');
		if ($config->get('enable_flash', 0)) {
			JHtml::_('behavior.uploader', 'file-upload', array('onAllComplete' => 'function(){ ImageManager.refreshFrame(); }'));
		}

		/*
		 * Display form for FTP credentials?
		 * Don't set them here, as there are other functions called before this one if there is any file write operation
		 */
		jimport('joomla.client.helper');
		$ftp = !JClientHelper::hasCredentials('ftp');

		$this->assignRef('session',	JFactory::getSession());
		$this->assignRef('config',		$config);
		$this->assignRef('state',		$this->get('state'));
		$this->assignRef('folderList',	$this->get('folderList'));
		$this->assign('require_ftp', $ftp);

		parent::display($tpl);
	}
}
