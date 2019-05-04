<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\View\Banners;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Banners\Administrator\Helper\BannersHelper;

/**
 * View class for a list of banners.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Category data
	 *
	 * @var  array
	 */
	protected $categories;

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
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise an \Exception object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->categories    = $this->get('CategoryOrders');
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		BannersHelper::addSubmenu('banners');

		$this->addToolbar();

		$this->sidebar = \JHtmlSidebar::render();

		// We do not need to filter by language when multilingual is disabled
		if (!Multilanguage::isEnabled())
		{
			unset($this->activeFilters['language']);
			$this->filterForm->removeField('language', 'filter');
		}

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
		$canDo = ContentHelper::getActions('com_banners', 'category', $this->state->get('filter.category_id'));
		$user  = Factory::getUser();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_BANNERS_MANAGER_BANNERS'), 'bookmark banners');

		if (count($user->getAuthorisedCategories('com_banners', 'core.create')) > 0)
		{
			$toolbar->addNew('banner.add');
		}

		if ($canDo->get('core.edit.state') || ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('fa fa-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if ($canDo->get('core.edit.state'))
			{
				if ($this->state->get('filter.published') != 2)
				{
					$childBar->publish('banners.publish')->listCheck(true);

					$childBar->unpublish('banners.unpublish')->listCheck(true);
				}

				if ($this->state->get('filter.published') != -1)
				{
					if ($this->state->get('filter.published') != 2)
					{
						$childBar->archive('banners.archive')->listCheck(true);
					}
					elseif ($this->state->get('filter.published') == 2)
					{
						$childBar->publish('publish')->task('banners.publish')->listCheck(true);
					}
				}

				$childBar->checkin('banners.checkin')->listCheck(true);

				if ($this->state->get('filter.published') != -2)
				{
					$childBar->trash('banners.trash')->listCheck(true);
				}
			}

			if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete'))
			{
				$toolbar->delete('banners.delete')
					->text('JTOOLBAR_EMPTY_TRASH')
					->message('JGLOBAL_CONFIRM_DELETE')
					->listCheck(true);
			}
		}

		// Add a batch button
		if ($user->authorise('core.create', 'com_banners')
			&& $user->authorise('core.edit', 'com_banners')
			&& $user->authorise('core.edit.state', 'com_banners'))
		{
			$toolbar->popupButton('batch')
				->text('JTOOLBAR_BATCH')
				->selector('collapseModal')
				->listCheck(true);
		}

		if ($user->authorise('core.admin', 'com_banners') || $user->authorise('core.options', 'com_banners'))
		{
			$toolbar->preferences('com_banners');
		}

		$toolbar->help('JHELP_COMPONENTS_BANNERS_BANNERS');
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
			'ordering'    => Text::_('JGRID_HEADING_ORDERING'),
			'a.state'     => Text::_('JSTATUS'),
			'a.name'      => Text::_('COM_BANNERS_HEADING_NAME'),
			'a.sticky'    => Text::_('COM_BANNERS_HEADING_STICKY'),
			'client_name' => Text::_('COM_BANNERS_HEADING_CLIENT'),
			'impmade'     => Text::_('COM_BANNERS_HEADING_IMPRESSIONS'),
			'clicks'      => Text::_('COM_BANNERS_HEADING_CLICKS'),
			'a.language'  => Text::_('JGRID_HEADING_LANGUAGE'),
			'a.id'        => Text::_('JGRID_HEADING_ID'),
		);
	}
}
