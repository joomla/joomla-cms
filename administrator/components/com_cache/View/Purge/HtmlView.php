<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Cache\Administrator\View\Purge;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * HTML View class for the Cache component
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Display a view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->addToolbar();
		$this->sidebar = \JHtmlSidebar::render();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('COM_CACHE_PURGE_EXPIRED_CACHE'), 'lightning purge');
		ToolbarHelper::custom('purge', 'delete.png', 'delete_f2.png', 'COM_CACHE_PURGE_EXPIRED', false);
		ToolbarHelper::divider();

		if (\JFactory::getUser()->authorise('core.admin', 'com_cache'))
		{
			ToolbarHelper::preferences('com_cache');
			ToolbarHelper::divider();
		}

		ToolbarHelper::help('JHELP_SITE_MAINTENANCE_PURGE_EXPIRED_CACHE');
	}
}
