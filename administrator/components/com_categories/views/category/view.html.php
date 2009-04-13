<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Categories
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Categories component
 *
 * @static
 * @package		Joomla.Administrator
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
		$mainframe = JFactory::getApplication();

		// Initialize variables
		$db =& JFactory::getDBO();
		$user =& JFactory::getUser();
		$uid = $user->get('id');

		$extension = JRequest::getCmd( 'extension', 'com_content' );
		$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
		JArrayHelper::toInteger($cid, array(0));
		$model =& $this->getModel();

		//get the section
		$row =& $this->get('data');
		$form =& $this->get('form');
		$edit = JRequest::getVar('edit',true);

		// fail if checked out not by 'me'
		if ( JTable::isCheckedOut($user->get ('id'), $row->checked_out )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The category' ), $row->title );
			$mainframe->redirect( 'index.php?option=com_categories&extension='. $row->extension, $msg );
		}

		if ( $edit ) {
			$model->checkout( $user->get('id'));
		} else {
			$row->published = 1;
		}

		// build the html select list for ordering
		$query = 'SELECT lft AS value, title AS text'
		. ' FROM #__categories'
		. ' WHERE extension = '.$db->Quote($row->extension)
		. ' ORDER BY lft'
		;
		if($edit)
			$lists['ordering'] = JHtml::_('list.specificordering',  $row, $cid[0], $query );
		else
			$lists['ordering'] = JHtml::_('list.specificordering',  $row, '', $query );

		// build the html select list for the group access
		$lists['access'] = JHtml::_('list.accesslevel',  $row );
		// build the html radio buttons for published
		$published = ($row->id) ? $row->published : 1;
		$lists['published'] = JHtml::_('select.booleanlist',  'published', 'class="inputbox"', $published );
		jimport('joomla.html.parameters');
		$params = new JParameter($row->params, JPATH_ADMINISTRATOR.DS.'components'.DS.$extension.DS.'category.xml');
		
		$this->assignRef('extension',	$extension);
		$this->assignRef('lists',		$lists);
		$this->assignRef('row',			$row);
		$this->assignRef('form',		$form);
		$this->assignRef('params',			$params);

		parent::display($tpl);
	}
}
