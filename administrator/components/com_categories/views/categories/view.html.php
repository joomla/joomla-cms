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
		$extension	= & $this->get( 'Extension' );
		$filter		= & $this->get( 'Filter');
		
		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'Category Manager' ) .': <small><small>[ '. JText::_($extension->name).' ]</small></small>', 'categories.png' );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::help( 'screen.categories' );

		$this->assignRef('user',		$user);
		$this->assignRef('type',		$type);
		$this->assignRef('extension',	$extension);
		$this->assignRef('rows',		$rows);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('filter',		$filter);

		parent::display($tpl);
	}
}
