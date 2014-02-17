<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

include_once __DIR__ . '/../default/view.php';

/**
 * Extension Manager Update View
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       1.6
 */
class InstallerViewUpdate extends InstallerViewDefault
{
	/**
	 * List of update items
	 *
	 * @var array
	 */
	protected $items;

	/**
	 * Model state object
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * List pagination
	 *
	 * @var JPagination
	 */
	protected $pagination;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  Template
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$app = JFactory::getApplication();

		// Get data from the model
		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$paths = new stdClass;
		$paths->first = '';

		$this->paths = &$paths;
		if (count($this->items) > 0)
		{
			$app->enqueueMessage(JText::_('COM_INSTALLER_MSG_WARNINGS_UPDATE_NOTICE'), 'notice');
		}
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
		JToolbarHelper::custom('update.update', 'upload', 'upload', 'COM_INSTALLER_TOOLBAR_UPDATE', true, false);
		JToolbarHelper::custom('update.find', 'refresh', 'refresh', 'COM_INSTALLER_TOOLBAR_FIND_UPDATES', false, false);
		JToolbarHelper::divider();

		JToolbarHelper::help('JHELP_EXTENSIONS_EXTENSION_MANAGER_UPDATE');
		JHtmlSidebar::setAction('index.php?option=com_installer&view=manage');

		JHtmlSidebar::addFilter(
			JText::_('COM_INSTALLER_VALUE_CLIENT_SELECT'),
			'filter_client_id',
			JHtml::_('select.options', array('0' => 'JSITE', '1' => 'JADMINISTRATOR'), 'value', 'text', $this->state->get('filter.client_id'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_INSTALLER_VALUE_TYPE_SELECT'),
			'filter_type',
			JHtml::_('select.options', InstallerHelper::getExtensionTypes(), 'value', 'text', $this->state->get('filter.type'), true)
		);

		JHtmlSidebar::addFilter(
			JText::_('COM_INSTALLER_VALUE_FOLDER_SELECT'),
			'filter_group',
			JHtml::_(
				'select.options',
				array_merge(InstallerHelper::getExtensionGroupes(), array('*' => JText::_('COM_INSTALLER_VALUE_FOLDER_NONAPPLICABLE'))),
				'value',
				'text',
				$this->state->get('filter.group'),
				true
			)
		);
		parent::addToolbar();
	}
}
