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

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Template Manager' ).': <small><small>[' .JText::_( $client ) .']</small></small>', 'templatemanager.png' );
		if ($client=="admin") {
			JMenuBar::custom('publish', 'publish.png', 'publish_f2.png', JText::_( 'Default' ), true);
			JMenuBar::spacer();
		} else {
			JMenuBar::makeDefault();
			JMenuBar::spacer();
			JMenuBar::assign();
			JMenuBar::spacer();
		}
		JMenuBar::editListX( 'edit_params', 'Params' );
		JMenuBar::spacer();
		JMenuBar::editHtmlX( 'edit_source' );
		JMenuBar::spacer();
		JMenuBar::editCssX( 'choose_css' );
		JMenuBar::spacer();
		//JMenuBar::addNew();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.templates' );
		JMenuBar::endTable();
	}
 	function _VIEW($client){

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Template Manager' ).': <small><small>[' .JText::_( $client ) .']</small></small>', 'templatemanager.png' );
		JMenuBar::back();
		JMenuBar::endTable();
	}

	function _EDIT_SOURCE($client){

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Template HTML Editor' ).': <small><small>[' .JText::_( $client ) .']</small></small>', 'templatemanager.png' );
		JMenuBar::save( 'save_source' );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::endTable();
	}

	function _EDIT_PARAMS($client){

		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Template Parameters' ).': <small><small>[' .JText::_( $client ) .']</small></small>', 'templatemanager.png' );
		JMenuBar::save( 'save_params' );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::endTable();
	}

	function _CHOOSE_CSS($client){
		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Template CSS Editor' ).': <small><small>[' .JText::_( $client ) .']</small></small>', 'templatemanager.png' );
		JMenuBar::custom( 'edit_css', 'next.png', 'next_f2.png', JText::_( 'Next' ), true );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::endTable();
	}

	function _EDIT_CSS($client){
		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Template Manager' ).': <small><small>[' .JText::_( $client ) .']</small></small>', 'templatemanager.png' );
		JMenuBar::save( 'save_css' );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::endTable();
	}

	function _ASSIGN($client){
		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Template Manager' ).': <small><small>[' .JText::_( $client ) .']</small></small>', 'templatemanager.png' );
		JMenuBar::save( 'save_assign', JText::_( 'Save' ) );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.templates.assign' );
		JMenuBar::endTable();
	}

	function _POSITIONS(){
		JMenuBar::startTable();
		JMenuBar::title( JText::_( 'Module Positions' ), 'templatemanager.png' );
		JMenuBar::save( 'save_positions' );
		JMenuBar::spacer();
		JMenuBar::cancel();
		JMenuBar::spacer();
		JMenuBar::help( 'screen.templates.modules' );
		JMenuBar::endTable();
	}
}
?>