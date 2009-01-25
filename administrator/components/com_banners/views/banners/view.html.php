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
class BannerViewBanners extends JView
{
	function display($tpl = null)
	{
		JToolBarHelper::title(JText::_('Banner Manager'), 'generic.png');
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::customX('copy', 'copy.png', 'copy_f2.png', 'Copy');
		JToolBarHelper::deleteList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::preferences('com_banners', '200');
		JToolBarHelper::help('screen.banners');

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