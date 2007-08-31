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
class MediaViewImagesList extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		// Do not allow cache
		JResponse::allowCache(false);

		JHTML::_('behavior.mootools');

		$document = &JFactory::getDocument();
		$document->addStyleSheet('components/com_media/assets/popup-imagelist.css');

		$document->addScriptDeclaration("var ImageManager = window.parent.ImageManager;");

		$base = $mainframe->isAdmin() ? $mainframe->getSiteURL() : JURI::base();
		$this->assign('baseURL', COM_MEDIA_BASEURL);
		$this->assignRef('images', $this->get('images'));
		$this->assignRef('folders', $this->get('folders'));
		$this->assignRef('state', $this->get('state'));

		parent::display($tpl);
	}


	function setFolder($index = 0)
	{
		if (isset($this->folders[$index])) {
			$this->_tmp_folder = &$this->folders[$index];
		} else {
			$this->_tmp_folder = new JObject;
		}
	}

	function setImage($index = 0)
	{
		if (isset($this->images[$index])) {
			$this->_tmp_img = &$this->images[$index];
		} else {
			$this->_tmp_img = new JObject;
		}
	}
}
