<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_cache
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Cache component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	com_cache
 * @since 1.6
 */
class CacheViewPurge extends JViewLegacy
{
	public function display($tpl = null)
	{
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addToolbar()
	{
		//JSubMenuHelper::addEntry(JText::_('COM_CACHE_BACK_CACHE_MANAGER'), 'index.php?option=com_cache', false);

		JToolBarHelper::title(JText::_('COM_CACHE_PURGE_EXPIRED_CACHE'), 'purge.png');
		JToolBarHelper::custom('purge', 'delete.png', 'delete_f2.png', 'COM_CACHE_PURGE_EXPIRED', false);
		JToolBarHelper::divider();
		if (JFactory::getUser()->authorise('core.admin', 'com_cache')) {
			JToolBarHelper::preferences('com_cache');
			JToolBarHelper::divider();
		}
		JToolBarHelper::help('JHELP_SITE_MAINTENANCE_PURGE_EXPIRED_CACHE');
	}
}
