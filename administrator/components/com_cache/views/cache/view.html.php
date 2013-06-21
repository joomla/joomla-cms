<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Cache component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 * @since       1.6
 */
class CacheViewCache extends JViewLegacy
{
	/**
	 * Array containing client information
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $client;

	/**
	 * Array containing the cache data
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $data;

	/**
	 * Pagination object
	 *
	 * @var    JPagination
	 * @since  1.6
	 */
	protected $pagination;

	/**
	 * State object
	 *
	 * @var    object
	 * @since  1.6
	 */
	protected $state;

	/**
	 * HTML markup for the sidebar
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $sidebar;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->data       = $this->get('Data');
		$this->client     = $this->get('Client');
		$this->pagination = $this->get('Pagination');
		$this->state      = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		return parent::display($tpl);
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
		JToolbarHelper::title(JText::_('COM_CACHE_CLEAR_CACHE'), 'clear.png');
		JToolbarHelper::custom('delete', 'delete.png', 'delete_f2.png', 'JTOOLBAR_DELETE', true);
		JToolbarHelper::divider();

		if (JFactory::getUser()->authorise('core.admin', 'com_cache'))
		{
			JToolbarHelper::preferences('com_cache');
		}

		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_SITE_MAINTENANCE_CLEAR_CACHE');

		JHtmlSidebar::setAction('index.php?option=com_cache');

		JHtmlSidebar::addFilter(
			// @todo We need an actual label here
			'',
			'filter_client_id',
			JHtml::_('select.options', CacheHelper::getClientOptions(), 'value', 'text', $this->state->get('clientId'))
		);
	}
}
