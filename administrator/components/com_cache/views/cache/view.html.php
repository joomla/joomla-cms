<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Cache component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	Cache
 * @since 1.6
 */
class CacheViewCache extends JView
{
	protected $client;
	protected $data;
	protected $pagination;
	protected $state;

	public function display($tpl = null)
	{
		$this->data			= $this->get('Data');
		$this->client		= $this->get('Client');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

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
		$user = JFactory::getUser();
		$condition = ($this->client->name == 'site');
		JSubMenuHelper::addEntry(JText::_('JSITE'), 'index.php?option=com_cache&client=0', $condition);
		JSubMenuHelper::addEntry(JText::_('JADMINISTRATOR'), 'index.php?option=com_cache&client=1', !$condition);

		JToolBarHelper::title(JText::_('COM_CACHE_MANAGER').': '.JText::_('COM_CACHE_CLEAR_CACHE'), 'clear.png');
		JToolBarHelper::custom('delete', 'delete.png', 'delete_f2.png', 'JTOOLBAR_DELETE', true);
		JToolBarHelper::divider();
		if (JFactory::getUser()->authorise('core.admin', 'com_cache')) {
			JToolBarHelper::preferences('com_cache');
		}
		JToolBarHelper::divider();
		JToolBarHelper::help('JHELP_SITE_MAINTENANCE_CLEAR_CACHE');
	}
}
