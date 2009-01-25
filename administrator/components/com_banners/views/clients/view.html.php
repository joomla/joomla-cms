<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Administrator
 * @subpackage	Banners
 */
class BannerViewClients extends JView
{
	function display($tpl = null)
	{
		// Set toolbar items for the page
		JToolBarHelper::title(JText::_('Banner Client Manager'), 'generic.png');
		JToolBarHelper::deleteList('', 'remove');
		JToolBarHelper::editListX('edit');
		JToolBarHelper::addNewX('add');
		JToolBarHelper::help('screen.banners.client');

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