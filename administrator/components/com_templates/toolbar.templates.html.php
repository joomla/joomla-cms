<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Templates
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Templates
*/
class TOOLBAR_templates {
	function _DEFAULT($client) {

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Template Manager' ).' <small><small>[' .JText::_( $client ) .']</small></small>', 'templatemanager.png' );
		if ($client=="admin") {
			mosMenuBar::custom('publish', 'publish.png', 'publish_f2.png', JText::_( 'Default' ), true);
			mosMenuBar::spacer();
		} else {
			mosMenuBar::makeDefault();
			mosMenuBar::spacer();
			mosMenuBar::assign();
			mosMenuBar::spacer();
		}
		mosMenuBar::deleteList();
		mosMenuBar::spacer();
		mosMenuBar::editHtmlX( 'edit_source' );
		mosMenuBar::spacer();
		mosMenuBar::editCssX( 'choose_css' );
		mosMenuBar::spacer();
		mosMenuBar::addNew();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.templates' );
		mosMenuBar::endTable();
	}
 	function _VIEW($client){

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Template Manager' ).' <small><small>[' .JText::_( $client ) .']</small></small>', 'templatemanager.png' );
		mosMenuBar::back();
		mosMenuBar::endTable();
	}

	function _EDIT_SOURCE($client){

		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Template HTML Editor' ).' <small><small>[' .JText::_( $client ) .']</small></small>', 'templatemanager.png' );
		mosMenuBar::save( 'save_source' );
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}

	function _CHOOSE_CSS($client){
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Template CSS Editor' ).' <small><small>[' .JText::_( $client ) .']</small></small>', 'templatemanager.png' );
		mosMenuBar::custom( 'edit_css', 'next.png', 'next_f2.png', JText::_( 'Next' ), true );
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}

	function _EDIT_CSS($client){
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Template Manager' ).' <small><small>[' .JText::_( $client ) .']</small></small>', 'templatemanager.png' );
		mosMenuBar::save( 'save_css' );
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::endTable();
	}

	function _ASSIGN($client){
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Template Manager' ).' <small><small>[' .JText::_( $client ) .']</small></small>', 'templatemanager.png' );
		mosMenuBar::save( 'save_assign', JText::_( 'Save' ) );
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.templates.assign' );
		mosMenuBar::endTable();
	}

	function _POSITIONS(){
		mosMenuBar::startTable();
		mosMenuBar::title( JText::_( 'Module Positions' ), 'templatemanager.png' );
		mosMenuBar::save( 'save_positions' );
		mosMenuBar::spacer();
		mosMenuBar::cancel();
		mosMenuBar::spacer();
		mosMenuBar::help( 'screen.templates.modules' );
		mosMenuBar::endTable();
	}
}
?>