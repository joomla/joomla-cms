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
	/*
	 * @var   object  Object holding information about the client.
	 * @since  1.6
	 */
	protected $client;

	/*
	 * @var   object  Object holding information about the client.
	 * @since  1.6
	 */
	protected $data;

	/*
	 * @var   JPagination  Pagination object.
	 * @since  1.6
	 */
	protected $pagination;

	/*
	 * @var   JObject  The JObject holding state data for this view such as parameters, paths and filters.
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Method to display the view
	 *
	 * @param   string  $tpl The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since  1.6
	 */
	public function display($tpl = null)
	{
		$this->data			= $this->get('Data');
		$this->client		= $this->get('Client');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		parent::display($tpl);var_dump($this->pagination);
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
		$user = JFactory::getUser();
		$condition = ($this->client->name == 'site');

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
