<?php
/**
* @version		$Id: $
* @package		Joomla
* @subpackage	Categories
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
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
 * HTML View class for the Categories component
 *
 * @static
 * @package		Joomla
 * @subpackage	Categories
 * @since 1.0
 */
class CategoriesViewCategory extends JView
{
	protected $redirect;
	protected $lists;
	protected $row;
	function display($tpl = null)
	{
		global $mainframe;

		// Initialize variables
		$db			=& JFactory::getDBO();
		$user 		=& JFactory::getUser();
		$uid		= $user->get('id');

		$type		= JRequest::getCmd( 'type' );
		$redirect	= JRequest::getCmd( 'section', 'com_content' );
		$section	= JRequest::getCmd( 'section', 'com_content' );
		$cid		= JRequest::getVar( 'cid', array(0), '', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		$model	=& $this->getModel();

		// check for existance of any sections
		$query = 'SELECT COUNT( id )'
		. ' FROM #__sections'
		. ' WHERE scope = "content"'
		;
		$db->setQuery( $query );
		$sections = $db->loadResult();
		if (!$sections && $type != 'other'
				&& $section != 'com_weblinks'
				&& $section != 'com_newsfeeds'
				&& $section != 'com_contact_details'
				&& $section != 'com_banner') {
			$mainframe->redirect( 'index.php?option=com_categories&section='. $section, JText::_( 'WARNSECTION', true ) );
		}

		//get the section
		$row	=& $this->get('data');
		$edit	= JRequest::getVar('edit',true);

		// fail if checked out not by 'me'
		if ( JTable::isCheckedOut($user->get ('id'), $row->checked_out )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The category' ), $row->title );
			$mainframe->redirect( 'index.php?option=com_categories&section='. $row->section, $msg );
		}

		if ( $edit ) {
			$model->checkout( $user->get('id'));
		} else {
			$row->published 	= 1;
		}


		// make order list
		/*
		$order = array();
		$query = 'SELECT COUNT(*)'
		. ' FROM #__categories'
		. ' WHERE section = '.$db->Quote($row->section)
		;
		$db->setQuery( $query );
		$max = intval( $db->loadResult() ) + 1;

		for ($i=1; $i < $max; $i++) {
			$order[] = JHTML::_('select.option',  $i );
		}
		*/

		// build the html select list for sections
		if ( $section == 'com_content' ) {

			if (!$row->section && JRequest::getInt('sectionid')) {
				$row->section = JRequest::getInt('sectionid');
			}

			$query = 'SELECT s.id AS value, s.title AS text'
			. ' FROM #__sections AS s'
			. ' ORDER BY s.ordering'
			;
			$db->setQuery( $query );
			$sections = $db->loadObjectList();
			$lists['section'] = JHTML::_('select.genericlist',   $sections, 'section', 'class="inputbox" size="1"', 'value', 'text', $row->section );
		} else {
			if ( $type == 'other' ) {
				$section_name = JText::_( 'N/A' );
			} else {
				$temp =& JTable::getInstance('section');
				$temp->load( $row->section );
				$section_name = $temp->name;
			}
			if(!$section_name) $section_name = JText::_( 'N/A' );
			$row->section = $section;
			$lists['section'] = '<input type="hidden" name="section" value="'. $row->section .'" />'. $section_name;
		}

		// build the html select list for ordering
		$query = 'SELECT ordering AS value, title AS text'
		. ' FROM #__categories'
		. ' WHERE section = '.$db->Quote($row->section)
		. ' ORDER BY ordering'
		;
		if($edit)
			$lists['ordering'] 			= JHTML::_('list.specificordering',  $row, $cid[0], $query );
		else
			$lists['ordering'] 			= JHTML::_('list.specificordering',  $row, '', $query );

		// build the select list for the image positions
		$active =  ( $row->image_position ? $row->image_position : 'left' );
		$lists['image_position'] 	= JHTML::_('list.positions',  'image_position', $active, NULL, 0, 0 );
		// Imagelist
		$lists['image'] 			= JHTML::_('list.images',  'image', $row->image );
		// build the html select list for the group access
		$lists['access'] 			= JHTML::_('list.accesslevel',  $row );
		// build the html radio buttons for published
		$published = ($row->id) ? $row->published : 1;
		$lists['published'] 		= JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $published );

		$this->assignRef('redirect',	$redirect);
		$this->assignRef('lists',		$lists);
		$this->assignRef('row',			$row);

		parent::display($tpl);
	}
}
