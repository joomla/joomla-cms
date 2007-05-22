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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
* @package		Joomla
* @subpackage	Wrapper
*/
class WrapperViewWrapper extends JView
{
	function display( $tpl = null )
	{
		// Get the paramaters of the active menu item
		$menus = &JMenu::getInstance();
		$menu  = $menus->getActive();
		
		$this->params->def( 'page_title', $menu->name );

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