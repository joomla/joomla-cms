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
class CategoriesViewCategories extends JView
{
	protected $user;
	protected $type;
	protected $section_name;
	protected $rows;
	protected $pagination;
	protected $filter;
	function display($tpl = null)
	{
		global $mainframe, $option;

		// get parameters from the URL or submitted form
		$db					=& JFactory::getDBO();
		$user				=& JFactory::getUser();

		// Get data from the model
		$rows		= & $this->get( 'Data');
		$pagination = & $this->get( 'Pagination' );
		$type		= & $this->get( 'Type');
		$section_name	= & $this->get( 'SectionName');
		$filter		= & $this->get( 'Filter');

		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'Category Manager' ) .': <small><small>[ '. JText::_(JString::substr($filter->section, 4)).' ]</small></small>', 'categories.png' );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();

		if ( $filter->section == 'com_content' || ( $filter->section > 0 ) ) {
			JToolBarHelper::customX( 'moveselect', 'move.png', 'move_f2.png', 'Move', true );
			JToolBarHelper::customX( 'copyselect', 'copy.png', 'copy_f2.png', 'Copy', true );
		}
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::help( 'screen.categories' );

		$this->assignRef('user',		$user);
		$this->assignRef('type',		$type);
		$this->assignRef('section_name',$section_name);
		$this->assignRef('rows',		$rows);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('filter',		$filter);

		parent::display($tpl);
	}
}
