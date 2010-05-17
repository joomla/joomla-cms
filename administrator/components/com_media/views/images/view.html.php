<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @since 1.0
 */
class MediaViewImages extends JView
{
	function display($tpl = null)
	{
		$config = &JComponentHelper::getParams('com_media');

		$app = JFactory::getApplication();
		$append = '';
		// if ($app->getClientId() == 1) $append = 'administrator/';

		JHTML::_('script','media/popup-imagemanager.js', true, true);
		JHTML::_('stylesheet','media/popup-imagemanager.css', array(), true);

		if ($config->get('enable_flash', 1)) {
			$fileTypes = $config->get('image_extensions', 'bmp,gif,jpg,png,jpeg');
			$types = explode(',', $fileTypes);
			$displayTypes = '';		// this is what the user sees
			$filterTypes = '';		// this is what controls the logic
			$firstType = true;
			foreach($types AS $type) {
				if(!$firstType) {
					$displayTypes .= ', ';
					$filterTypes .= '; ';
				} else {
					$firstType = false;
				}
				$displayTypes .= '*.'.$type;
				$filterTypes .= '*.'.$type;
			}
			$typeString = '{ \'Images ('.$displayTypes.')\': \''.$filterTypes.'\' }';

			JHtml::_('behavior.uploader', 'upload-flash',
				array(
					'onBeforeStart' => 'function(){ Uploader.setOptions({url: $(\'uploadForm\').action + \'&folder=\' + $(\'imageForm\').folderlist.value}); }',
					'onComplete' 	=> 'function(){ window.frames[\'imageframe\'].location.href = window.frames[\'imageframe\'].location.href; }',
					'targetURL' 	=> '\\$(\'uploadForm\').action',
					'typeFilter' 	=> $typeString,
					'fileSizeMax'	=> $config->get('upload_maxsize'),
				)
			);
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
