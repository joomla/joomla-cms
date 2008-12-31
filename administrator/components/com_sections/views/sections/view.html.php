<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Sections
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
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
class SectionsViewSections extends JView
{
	protected $filter;
	protected $pagination;
	protected $rows;
	protected $scope;
	protected $user;
	function display($tpl = null)
	{
		global $mainframe, $option;

		// get parameters from the URL or submitted form
		$scope 		= JRequest::getCmd( 'scope' );

		$db					=& JFactory::getDBO();
		$user				=& JFactory::getUser();

		// Set toolbar items for the page
		JToolBarHelper::title( JText::_( 'Section Manager' ), 'sections.png' );
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::customX( 'copyselect', 'copy.png', 'copy_f2.png', 'Copy', true );
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::help( 'screen.sections' );

		// Get data from the model
		$rows		= & $this->get( 'Data');
		$pagination = & $this->get( 'Pagination' );
		$filter		= & $this->get( 'Filter');

		$this->assignRef('user',		$user);
		$this->assignRef('scope',		$scope);
		$this->assignRef('rows',		$rows);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('filter',		$filter);

		parent::display($tpl);
	}
}
