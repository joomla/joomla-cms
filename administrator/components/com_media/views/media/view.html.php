<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Media
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Media component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Media
 * @since 1.0
 */
class MediaViewMedia extends JView
{
	function display($tpl = null)
	{
		$app	= &JFactory::getApplication();
		$config = &JComponentHelper::getParams('com_media');

		$style = $app->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');

		$listStyle = "
			<ul id=\"submenu\">
				<li><a id=\"thumbs\" onclick=\"MediaManager.setViewType('thumbs')\">".JText::_('Thumbnail View')."</a></li>
				<li><a id=\"details\" onclick=\"MediaManager.setViewType('details')\">".JText::_('Detail View')."</a></li>
			</ul>
		";

		$document = &JFactory::getDocument();
		$document->setBuffer($listStyle, 'modules', 'submenu');

		JHtml::_('behavior.framework', true);
		$document->addScript('components/com_media/assets/mediamanager.js');
		$document->addStyleSheet('components/com_media/assets/mediamanager.css');

		JHtml::_('behavior.modal');
		$document->addScriptDeclaration("
		window.addEvent('domready', function() {
			document.preview = SqueezeBox;
		});");

		JHtml::script('mootree.js');
		JHtml::stylesheet('mootree.css');

		if ($config->get('enable_flash', 0)) {
			JHtml::_('behavior.uploader', 'file-upload', array('onAllComplete' => 'function(){ MediaManager.refreshFrame(); }'));
		}

		if (DS == '\\')
		{
			$base = str_replace(DS,"\\\\",COM_MEDIA_BASE);
		} else {
			$base = COM_MEDIA_BASE;
		}

		$js = "
			var basepath = '".$base."';
			var viewstyle = '".$style."';
		" ;
		$document->addScriptDeclaration($js);

		/*
		 * Display form for FTP credentials?
		 * Don't set them here, as there are other functions called before this one if there is any file write operation
		 */
		jimport('joomla.client.helper');
		$ftp = !JClientHelper::hasCredentials('ftp');

		$this->assignRef('session', JFactory::getSession());
		$this->assignRef('config', $config);
		$this->assignRef('state', $this->get('state'));
		$this->assign('require_ftp', $ftp);
		$this->assign('folders_id', ' id="media-tree"');
		$this->assign('folders', $this->get('folderTree'));

		// Set the toolbar
		$this->_setToolBar();

		parent::display($tpl);
		echo JHtml::_('behavior.keepalive');
	}

	function _setToolBar()
	{
		// Get the toolbar object instance
		$bar = &JToolBar::getInstance('toolbar');

		// Set the titlebar text
		JToolBarHelper::title(JText::_('Media Manager'), 'mediamanager.png');

		// Add a delete button
		$title = JText::_('Delete');
		$dhtml = "<a href=\"#\" onclick=\"MediaManager.submit('folder.delete')\" class=\"toolbar\">
					<span class=\"icon-32-delete\" title=\"$title\" type=\"Custom\"></span>
					$title</a>";
		$bar->appendButton('Custom', $dhtml, 'delete');
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_media');
		JToolBarHelper::divider();
		JToolBarHelper::help('screen.mediamanager');
	}

	function getFolderLevel($folder)
	{
		$this->folders_id = null;
		$txt = null;
		if (isset($folder['children']) && count($folder['children'])) {
			$tmp = $this->folders;
			$this->folders = $folder;
			$txt = $this->loadTemplate('folders');
			$this->folders = $tmp;
		}
		return $txt;
	}
}
