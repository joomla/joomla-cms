<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Wrapper
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.component.view');

/**
* @package		Joomla
* @subpackage	Wrapper
*/
class WrapperViewWrapper extends JView
{
	function display( $tpl = null )
	{
		global $Itemid;

		// get menu
		$menus	=& JMenu::getInstance();
		$menu	=& $menus->getItem($Itemid);

		$this->params->def( 'header', $menu->name );
		$this->params->def( 'scrolling', 'auto' );
		$this->params->def( 'page_title', '1' );
		$this->params->def( 'pageclass_sfx', '' );
		$this->params->def( 'height', '500' );
		$this->params->def( 'height_auto', '0' );
		$this->params->def( 'width', '100%' );
		$this->params->def( 'add', '1' );


		// auto height control
		if ( $this->params->def( 'height_auto' ) ) {
			$this->wrapper->load = 'onload="iFrameHeight()"';
		} else {
			$this->wrapper->load = '';
		}

		parent::display($tpl);
	}
}
?>