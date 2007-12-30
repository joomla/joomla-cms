<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Templates
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Templates
*/
class TOOLBAR_templates
{
	function _DEFAULT(&$client)
	{
		JToolBarHelper::title( JText::_( 'Template Manager' ), 'thememanager' );

		if ($client->id == '1') {
			JToolBarHelper::makeDefault('publish');
		} else {
			JToolBarHelper::makeDefault();
		}
		JToolBarHelper::editListX( 'edit', 'Edit' );
		//JToolBarHelper::addNew();
		JToolBarHelper::help( 'screen.templates' );
	}
 	function _VIEW(&$client){

		JToolBarHelper::title( JText::_( 'Template Manager' ), 'thememanager' );
		JToolBarHelper::back();
	}

	function _EDIT_SOURCE(&$client){

		JToolBarHelper::title( JText::_( 'Template HTML Editor' ), 'thememanager' );
		JToolBarHelper::save( 'save_source' );
		JToolBarHelper::apply( 'apply_source' );
		JToolBarHelper::cancel('edit');
		JToolBarHelper::help( 'screen.templates' );
	}

	function _EDIT(&$client){
		JToolBarHelper::title( JText::_( 'Template' ) . ': <small><small>[ '. JText::_( 'Edit' ) .' ]</small></small>', 'thememanager' );
		JToolBarHelper::custom('preview', 'preview.png', 'preview_f2.png', 'Preview', false, false);
		JToolBarHelper::custom( 'edit_source', 'html.png', 'html_f2.png', 'Edit HTML', false, false );
		JToolBarHelper::custom( 'choose_css', 'css.png', 'css_f2.png', 'Edit CSS', false, false );
		JToolBarHelper::save( 'save' );
		JToolBarHelper::apply();
		JToolBarHelper::cancel( 'cancel', 'Close' );
		JToolBarHelper::help( 'screen.templates' );
	}

	function _CHOOSE_CSS(&$client){
		JToolBarHelper::title( JText::_( 'Template CSS Editor' ), 'thememanager' );
		JToolBarHelper::custom( 'edit_css', 'edit.png', 'edit_f2.png', 'Edit', true );
		JToolBarHelper::cancel('edit');
		JToolBarHelper::help( 'screen.templates' );
	}

	function _EDIT_CSS(&$client){
		JToolBarHelper::title( JText::_( 'Template Manager' ), 'thememanager' );
		JToolBarHelper::save( 'save_css' );
		JToolBarHelper::apply( 'apply_css');
		JToolBarHelper::cancel('choose_css');
		JToolBarHelper::help( 'screen.templates' );
	}
}