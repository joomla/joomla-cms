<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * View for the global configuration
 *
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 * @since       3.2
 */
class CacheViewCacheHtml extends JViewCmslist
{
	protected $client;

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since  3.2
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_CACHE_CLEAR_CACHE'), 'clear.png');
		$input = new JInput;
		$layout = $input->get('layout');

		if ($layout == 'purge')
		{
			JToolbarHelper::custom('cache.cleanlist.purge', 'delete.png', 'delete_f2.png', 'COM_CACHE_PURGE_EXPIRED', false);
		}
		else
		{
			JToolbarHelper::custom('cache.cleanlist', 'delete.png', 'delete_f2.png', 'JTOOLBAR_DELETE', true);
		}

		JToolbarHelper::divider();

		if (JFactory::getUser()->authorise('core.admin', 'com_cache'))
		{
			JToolbarHelper::preferences('com_cache');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_SITE_MAINTENANCE_CLEAR_CACHE');

		JHtmlSidebar::setAction('index.php?option=com_cache');

		if (empty($layout) || $layout == 'default')
		{
			JHtmlSidebar::addFilter(
				// @todo We need an actual label here
				'',
				'filter_client_id',
				JHtml::_('select.options', CacheHelperCache::getClientOptions(), 'value', 'text', $this->state->get('clientId'))
			);
		}
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
		CacheHelperCache::addSubmenu('cache');

		$this->sidebar = JHtmlSidebar::render();
	}
}