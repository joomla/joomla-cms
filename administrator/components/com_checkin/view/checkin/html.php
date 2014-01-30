<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View for the global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 * @since       3.2
 */
class CheckinViewCheckinHtml extends JViewHtmlCmslist
{
	protected $tables;

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since	3.2
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_CHECKIN_GLOBAL_CHECK_IN'), 'checkin.png');

		if (JFactory::getUser()->authorise('core.admin', 'com_checkin'))
		{
			JToolbarHelper::custom('j.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			JToolbarHelper::divider();
			JToolbarHelper::preferences('com_checkin');
			JToolbarHelper::divider();
		}

		JToolbarHelper::help('JHELP_SITE_MAINTENANCE_GLOBAL_CHECK-IN');
	}

	/**
	 * Add the submenu.
	 *
	 * @return  void
	 *
	 * @since	3.2
	 */
	protected function addSubmenu()
	{
		CheckinHelperCheckin::addSubmenu('checkin');

		$this->sidebar = JHtmlSidebar::render();
	}
}