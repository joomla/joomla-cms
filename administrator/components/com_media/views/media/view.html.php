<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
class MediaViewMedia extends JViewLegacy
{
	public function display($tpl = null)
	{
		$app	= JFactory::getApplication();
		$config = JComponentHelper::getParams('com_media');

		$lang	= JFactory::getLanguage();

		$style = $app->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');

		$document = JFactory::getDocument();

		JHtml::_('behavior.framework', true);

		JHtml::_('script', 'media/mediamanager.js', true, true);
		/*
		JHtml::_('stylesheet', 'media/mediamanager.css', array(), true);
		if ($lang->isRTL()) :
			JHtml::_('stylesheet', 'media/mediamanager_rtl.css', array(), true);
		endif;
		*/
		JHtml::_('behavior.modal');
		$document->addScriptDeclaration("
		window.addEvent('domready', function()
		{
			document.preview = SqueezeBox;
		});");

		// JHtml::_('script', 'system/mootree.js', true, true, false, false);
		JHtml::_('stylesheet', 'system/mootree.css', array(), true);
		if ($lang->isRTL()) :
			JHtml::_('stylesheet', 'media/mootree_rtl.css', array(), true);
		endif;

		if (DIRECTORY_SEPARATOR == '\\')
		{
			$base = str_replace(DIRECTORY_SEPARATOR, "\\\\", COM_MEDIA_BASE);
		}
		else
		{
			$base = COM_MEDIA_BASE;
		}

		$js = "
			var basepath = '".$base."';
			var viewstyle = '".$style."';
		";
		$document->addScriptDeclaration($js);

		/*
		 * Display form for FTP credentials?
		 * Don't set them here, as there are other functions called before this one if there is any file write operation
		 */
		$ftp = !JClientHelper::hasCredentials('ftp');

		$session	= JFactory::getSession();
		$state		= $this->get('state');
		$this->session = $session;
		$this->config = &$config;
		$this->state = &$state;
		$this->require_ftp = $ftp;
		$this->folders_id = ' id="media-tree"';
		$this->folders = $this->get('folderTree');

		// Set the toolbar
		$this->addToolbar();

		parent::display($tpl);
		echo JHtml::_('behavior.keepalive');
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');
		$user = JFactory::getUser();

		// Set the titlebar text
		JToolbarHelper::title(JText::_('COM_MEDIA'), 'mediamanager.png');

		// Add a upload button
		if ($user->authorise('core.create', 'com_media'))
		{
			$title = JText::_('JTOOLBAR_UPLOAD');
			$dhtml = "<button data-toggle=\"collapse\" data-target=\"#collapseUpload\" class=\"btn btn-small btn-success\">
						<i class=\"icon-plus icon-white\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'upload');
			JToolbarHelper::divider();
		}

		// Add a create folder button
		if ($user->authorise('core.create', 'com_media'))
		{
			$title = JText::_('COM_MEDIA_CREATE_NEW_FOLDER');
			$dhtml = "<button data-toggle=\"collapse\" data-target=\"#collapseFolder\" class=\"btn btn-small\">
						<i class=\"icon-folder\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'folder');
			JToolbarHelper::divider();
		}

		// Add a delete button
		if ($user->authorise('core.delete', 'com_media'))
		{
			$title = JText::_('JTOOLBAR_DELETE');
			$dhtml = "<button onclick=\"MediaManager.submit('folder.delete')\" class=\"btn btn-small\">
						<i class=\"icon-remove\" title=\"$title\"></i>
						$title</button>";
			$bar->appendButton('Custom', $dhtml, 'delete');
			JToolbarHelper::divider();
		}
		// Add a delete button
		if ($user->authorise('core.admin', 'com_media'))
		{
			JToolbarHelper::preferences('com_media');
			JToolbarHelper::divider();
		}
		JToolbarHelper::help('JHELP_CONTENT_MEDIA_MANAGER');
	}

	function getFolderLevel($folder)
	{
		$this->folders_id = null;
		$txt = null;
		if (isset($folder['children']) && count($folder['children']))
		{
			$tmp = $this->folders;
			$this->folders = $folder;
			$txt = $this->loadTemplate('folders');
			$this->folders = $tmp;
		}
		return $txt;
	}
}
