<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Weblinks
 * @since		1.5
 */
class WeblinksViewWeblinks extends JView
{
	function display($tpl = null)
	{
		// Set toolbar items for the page
		JToolBarHelper::title(  JText::_('Weblink Manager'), 'generic.png');
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::preferences('com_weblinks', '480');
		JToolBarHelper::help('screen.weblink');

		// Get data from the model
		$items		= & $this->get('Data');
		$total		= & $this->get('Total');
		$pagination = & $this->get('Pagination');
		$filter		= & $this->get('Filter');

		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('filter',		$filter);

		parent::display($tpl);
	}
}