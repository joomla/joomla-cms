<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Content
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * HTML View class for the Frontpage component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @since 1.6
 */
class ContentViewFrontpage extends JView
{
	protected $user;
	protected $rows;
	protected $pagination;
	protected $filter;
	function display($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		$db					=& JFactory::getDBO();

		JToolBarHelper::title(JText::_('Frontpage Manager'), 'frontpage.png');
		JToolBarHelper::archiveList();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::custom('remove','delete.png','delete_f2.png','Remove', true);
		JToolBarHelper::help('screen.frontpage');

		JSubMenuHelper::addEntry(JText::_('Articles'), 'index.php?option=com_content');
		JSubMenuHelper::addEntry(JText::_('Front Page'), 'index.php?option=com_content&controller=frontpage', true);

		// Get data from the model
		$rows		= & $this->get('Data');
		$total		= & $this->get('Total');
		$pagination = & $this->get('Pagination');
		$filter		= & $this->get('Filter');

		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('rows',		$rows);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('filter',		$filter);

		parent::display($tpl);
	}
}
