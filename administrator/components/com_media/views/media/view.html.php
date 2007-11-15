<?php
/**
* @version		$Id$
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
 * HTML View class for the Media component
 *
 * @static
 * @package		Joomla
 * @subpackage	Media
 * @since 1.0
 */
class MediaViewMedia extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		$style = $mainframe->getUserStateFromRequest('media.list.layout', 'layout', 'thumbs', 'word');

		$listStyle = "
			<ul id=\"submenu\">
				<li><a id=\"thumbs\" onclick=\"MediaManager.setViewType('thumbs')\">".JText::_('Thumbnail View')."</a></li>
				<li><a id=\"details\" onclick=\"MediaManager.setViewType('details')\">".JText::_('Detail View')."</a></li>
			</ul>
		";

		$document =& JFactory::getDocument();
		$document->setBuffer($listStyle, 'module', 'submenu');

		JHTML::_('behavior.mootools');
		$document->addScript('components/com_media/assets/mediamanager.js');
		$document->addStyleSheet('components/com_media/assets/mediamanager.css');

		JHTML::_('behavior.modal');
		$document->addScriptDeclaration("
		window.addEvent('domready', function() {
			document.preview = SqueezeBox;
		});");

		JHTML::script('mootree.js');
		JHTML::stylesheet('mootree.css');

		JHTML::_('behavior.uploader', 'file-upload', array('onAllComplete' => 'function(){ MediaManager.refreshFrame(); }'));

		$base = str_replace("\\","/",JPATH_ROOT);
		$js = "
			var basepath = '".$base.'/images'."';
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
		$this->assignRef('config', JComponentHelper::getParams('com_media'));
		$this->assignRef('state', $this->get('state'));
		$this->assign('require_ftp', $ftp);
		$this->assign('folders_id', ' id="media-tree"');
		$this->assign('folders', $this->get('folderTree'));

		// Set the toolbar
		$this->_setToolBar();

		parent::display($tpl);
		echo JHTML::_('behavior.keepalive');
	}

	function _setToolBar()
	{
		// Get the toolbar object instance
		$bar =& JToolBar::getInstance('toolbar');

		// Set the titlebar text
		JToolBarHelper::title( JText::_( 'Media Manager' ), 'mediamanager.png');

		// Add a delete button
		$title = JText::_('Delete');
		$dhtml = "<a href=\"#\" onclick=\"MediaManager.submit('folder.delete')\" class=\"toolbar\">
					<span class=\"icon-32-delete\" title=\"$title\" type=\"Custom\"></span>
					$title
				</a>";
		$bar->appendButton( 'Custom', $dhtml, 'delete' );

		// Add a popup configuration button
		JToolBarHelper::help( 'screen.mediamanager' );
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
?>
