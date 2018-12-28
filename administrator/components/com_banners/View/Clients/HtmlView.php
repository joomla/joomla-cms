<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\View\Clients;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Banners\Administrator\Helper\BannersHelper;

/**
 * View class for a list of clients.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * An array of items
	 *
	 * @var  array
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var  \JPagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		BannersHelper::addSubmenu('clients');

		$this->addToolbar();
		$this->sidebar = \JHtmlSidebar::render();

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
		$canDo = ContentHelper::getActions('com_banners');
		
		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');
		
		ToolbarHelper::title(Text::_('COM_BANNERS_MANAGER_CLIENTS'), 'bookmark banners-clients');

		if ($canDo->get('core.create'))
		{
			$toolbar->addNew('client.add');
		}

		if ($canDo->get('core.edit.state') || ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')))
		{
			$dropdown = $toolbar->dropdownButton('status')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fa fa-globe')
				->buttonClass('btn btn-info')
				->listCheck(true);
			
			$childBar = $dropdown->getChildToolbar();

			if ($canDo->get('core.edit.state'))
			{
				if ($this->state->get('filter.published') != 2)
				{
					$childBar->standardButton('publish')
						->text('JTOOLBAR_PUBLISH')
						->task('clients.publish')
						->listCheck(true);
					$childBar->standardButton('unpublish')
						->text('JTOOLBAR_UNPUBLISH')
						->task('clients.unpublish')
						->listCheck(true);
				}

				if ($this->state->get('filter.published') != -1)
				{
					if ($this->state->get('filter.published') != 2)
					{
						$childBar->standardButton('archive')
							->text('JTOOLBAR_ARCHIVE')
							->task('clients.archive')
							->listCheck(true);
					}
					elseif ($this->state->get('filter.published') == 2)
					{
						$childBar->standardButton('publish')
							->text('JTOOLBAR_PUBLISH')
							->task('clients.publish')
							->listCheck(true);
					}
				}

				$childBar->checkin('clients.checkin')->listCheck(true);
			}

			if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
			{
				$toolbar->delete('clients.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
			elseif ($canDo->get('core.edit.state'))
			{
				$childBar->standardButton('trash')
					->text('JTOOLBAR_TRASH')
					->task('clients.trash')
					->listCheck(true);
			}
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			$toolbar->preferences('com_clients');
		}

		$toolbar->help('JHELP_COMPONENTS_BANNERS_CLIENTS');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   3.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.status'    => Text::_('JSTATUS'),
			'a.name'      => Text::_('COM_BANNERS_HEADING_CLIENT'),
			'contact'     => Text::_('COM_BANNERS_HEADING_CONTACT'),
			'client_name' => Text::_('COM_BANNERS_HEADING_CLIENT'),
			'a.id'        => Text::_('JGRID_HEADING_ID')
		);
	}
}
