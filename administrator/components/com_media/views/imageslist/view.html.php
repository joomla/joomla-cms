<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Media
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

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
	protected $baseURL = null;
	protected $images = null;
	protected $folders = null;
	protected $state = null;
	protected $_tmp_folder = null;
	protected $_tmp_img = null;

	public function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		// Do not allow cache
		JResponse::allowCache(false);

		JHtml::_('behavior.mootools');
		JHtml::_('stylesheet', 'popup-imagelist.css', 'administrator/components/com_media/assets/');

		$document =& JFactory::getDocument();
		$document->addScriptDeclaration("var ImageManager = window.parent.ImageManager;");

		$this->assign('baseURL', COM_MEDIA_BASEURL);
		$this->assignRef('images', $this->get('images'));
		$this->assignRef('folders', $this->get('folders'));
		$this->assignRef('state', $this->get('state'));

		parent::display($tpl);
	}


	function setFolder($index = 0)
	{
		if (isset($this->folders[$index])) {
			$this->_tmp_folder = $this->folders[$index];
		} else {
			$this->_tmp_folder = new JStdClass;
		}
	}

	function setImage($index = 0)
	{
		if (isset($this->images[$index])) {
			$this->_tmp_img = $this->images[$index];
		} else {
			$this->_tmp_img = new JStdClass();
		}
	}
}
