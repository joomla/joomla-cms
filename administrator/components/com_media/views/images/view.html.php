<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Media component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       1.0
 */
class MediaViewImages extends JViewLegacy
{
	public function display($tpl = null)
	{
		$config = JComponentHelper::getParams('com_media');
		$app	= JFactory::getApplication();
		$lang	= JFactory::getLanguage();
		$append = '';

		JHtml::_('behavior.framework', true);
		JHtml::_('script', 'media/popup-imagemanager.js', true, true);
		JHtml::_('stylesheet', 'media/popup-imagemanager.css', array(), true);

		if ($lang->isRTL()) {
			JHtml::_('stylesheet', 'media/popup-imagemanager_rtl.css', array(), true);
		}

		if ($config->get('enable_flash', 1)) {
			$fileTypes = $config->get('image_extensions', 'bmp,gif,jpg,png,jpeg');
			$types = explode(',', $fileTypes);
			$displayTypes = '';		// this is what the user sees
			$filterTypes = '';		// this is what controls the logic
			$firstType = true;

			foreach($types as $type)
			{
				if(!$firstType) {
					$displayTypes .= ', ';
					$filterTypes .= '; ';
				}
				else {
					$firstType = false;
				}

				$displayTypes .= '*.'.$type;
				$filterTypes .= '*.'.$type;
			}

			$typeString = '{ \''.JText::_('COM_MEDIA_FILES', 'true').' ('.$displayTypes.')\': \''.$filterTypes.'\' }';

			JHtml::_('behavior.uploader', 'upload-flash',
				array(
					'onBeforeStart' => 'function(){ Uploader.setOptions({url: document.id(\'uploadForm\').action + \'&folder=\' + document.id(\'imageForm\').folderlist.value}); }',
					'onComplete' 	=> 'function(){ window.frames[\'imageframe\'].location.href = window.frames[\'imageframe\'].location.href; }',
					'targetURL' 	=> '\\document.id(\'uploadForm\').action',
					'typeFilter' 	=> $typeString,
					'fileSizeMax'	=> (int) ($config->get('upload_maxsize', 0) * 1024 * 1024),
				)
			);
		}

		/*
		 * Display form for FTP credentials?
		 * Don't set them here, as there are other functions called before this one if there is any file write operation
		 */
		$ftp = !JClientHelper::hasCredentials('ftp');

		$this->session = JFactory::getSession();
		$this->config = $config;
		$this->state = $this->get('state');
		$this->folderList = $this->get('folderList');
		$this->require_ftp = $ftp;

		parent::display($tpl);
	}
}
