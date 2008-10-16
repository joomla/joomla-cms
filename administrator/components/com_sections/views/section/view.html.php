<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Sections
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
 * HTML View class for the Sections component
 *
 * @static
 * @package		Joomla
 * @subpackage	Sections
 * @since 1.0
 */
class SectionsViewSection extends JView
{
	protected $user;
	protected $lists;	
	protected $row;

	function display($tpl = null)
	{
		global $mainframe;

		$db			=& JFactory::getDBO();
		$user 		=& JFactory::getUser();

		$option		= JRequest::getCmd( 'option');
		$scope		= JRequest::getCmd( 'scope' );
		$cid		= JRequest::getVar( 'cid', array(0), '', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		$model	=& $this->getModel();

		//get the section
		$row	=& $this->get('data');
		$edit	= JRequest::getVar('edit',true);

		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The section' ), $row->title );
			$mainframe->redirect( 'index.php?option='. $option .'&scope='. $row->scope, $msg );
		}

		if ( $edit ) {
			$model->checkout( $user->get('id') );
		} else {
			$row->scope 		= $scope;
			$row->published 	= 1;
		}

		// build the html select list for ordering
		$query = 'SELECT ordering AS value, title AS text'
		. ' FROM #__sections'
		. ' WHERE scope='.$db->Quote($row->scope).' ORDER BY ordering'
		;
		if($edit)
			$lists['ordering'] 			= JHTML::_('list.specificordering',  $row, $cid[0], $query );
		else
			$lists['ordering'] 			= JHTML::_('list.specificordering',  $row, '', $query );

		// build the select list for the image positions
		$active =  ( $row->image_position ? $row->image_position : 'left' );
		$lists['image_position'] 	= JHTML::_('list.positions',  'image_position', $active, NULL, 0 );
		// build the html select list for images
		$lists['image'] 			= JHTML::_('list.images',  'image', $row->image );
		// build the html select list for the group access
		$lists['access'] 			= JHTML::_('list.accesslevel',  $row );
		// build the html radio buttons for published
		$lists['published'] 		= JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $row->published );

		$this->assignRef('user',		$user);
		$this->assignRef('lists',		$lists);
		$this->assignRef('row',			$row);

		parent::display($tpl);
	}
}
