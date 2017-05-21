<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
namespace Joomla\Component\Finder\Administrator\View\Filter;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\View\HtmlView;

/**
 * Filter view class for Finder.
 *
 * @since  2.5
 */
class Html extends HtmlView
{
	/**
	 * The filter object
	 *
	 * @var  \Joomla\Component\Finder\Administrator\Table\Filter
	 */
	protected $filter;

	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  \JObject
	 */
	protected $state;

	/**
	 * The total number of indexed items
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	protected $total;

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  A string if successful, otherwise a \JError object.
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		// Load the view data.
		$this->filter = $this->get('Filter');
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$this->state = $this->get('State');
		$this->total = $this->get('Total');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		\JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		\JHtml::addIncludePath(JPATH_SITE . '/components/com_finder/helpers/html');

		// Configure the toolbar.
		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Method to configure the toolbar for this view.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function addToolbar()
	{
		\JFactory::getApplication()->input->set('hidemainmenu', true);

		$isNew = ($this->item->filter_id == 0);
		$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == \JFactory::getUser()->id);
		$canDo = ContentHelper::getActions('com_finder');

		// Configure the toolbar.
		ToolbarHelper::title(
			$isNew ? \JText::_('COM_FINDER_FILTER_NEW_TOOLBAR_TITLE') : \JText::_('COM_FINDER_FILTER_EDIT_TOOLBAR_TITLE'),
			'zoom-in finder'
		);

		// Set the actions for new and existing records.
		if ($isNew)
		{
			// For new records, check the create permission.
			if ($canDo->get('core.create'))
			{
				ToolbarHelper::saveGroup(
					[
						['apply', 'filter.apply'],
						['save', 'filter.save'],
						['save2new', 'filter.save2new']
					],
					'btn-success'
				);
			}

			ToolbarHelper::cancel('filter.cancel');
		}
		else
		{
			$toolbarButtons = [];

			// Can't save the record if it's checked out.
			if (!$checkedOut)
			{
				// Since it's an existing record, check the edit permission.
				if ($canDo->get('core.edit'))
				{
					$toolbarButtons[] = ['apply', 'filter.apply'];
					$toolbarButtons[] = ['save', 'filter.save'];

					// We can save this record, but check the create permission to see if we can return to make a new one.
					if ($canDo->get('core.create'))
					{
						$toolbarButtons[] = ['save2new', 'filter.save2new'];
					}
				}
			}

			// If an existing item, can save as a copy
			if ($canDo->get('core.create'))
			{
				$toolbarButtons[] = ['save2copy', 'filter.save2copy'];
			}

			ToolbarHelper::saveGroup(
				$toolbarButtons,
				'btn-success'
			);

			ToolbarHelper::cancel('filter.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_COMPONENTS_FINDER_MANAGE_SEARCH_FILTERS_EDIT');
	}
}
