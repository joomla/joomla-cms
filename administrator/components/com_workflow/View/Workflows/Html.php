<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\View\Workflows;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\View\HtmlView;

/**
 * Workflows view class for the Workflow package.
 *
 * @since  1.6
 */
class Html extends HtmlView
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
	 * @var  Pagination
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * Flag if an association exists
	 *
	 * @var  boolean
	 */
	protected $assoc;

	/**
	 * Form object for search filters
	 *
	 * @var  \JForm
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var  array
	 */
	public $activeFilters;

	/**
	 * The sidebar markup
	 *
	 * @var  string
	 */
	protected $string;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
//		$this->items = $this->get('Items');
//		$this->pagination = $this->get('Pagination');
//		$this->state = $this->get('State');
//		$this->addToolbar();
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
		\JToolbarHelper::title(JText::_('COM_PROVE_LIST_EMAILS'), 'address contact');
		\JToolbarHelper::addNew('item.add');
		\JToolbarHelper::publish('items.publish', 'JTOOLBAR_PUBLISH', true);
		\JToolbarHelper::unpublish('items.unpublish', 'JTOOLBAR_UNPUBLISH', true);
		\JToolbarHelper::trash('items.trash');
		\JToolBarHelper::deleteList('Are you sure ?', 'items.delete');
		\JToolbarHelper::archiveList('items.archive');
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
			'a.lft' => \JText::_('JGRID_HEADING_ORDERING'),
			'a.published' => \JText::_('JSTATUS'),
			'a.title' => \JText::_('JGLOBAL_TITLE'),
			'a.access' => \JText::_('JGRID_HEADING_ACCESS'),
			'language' => \JText::_('JGRID_HEADING_LANGUAGE'),
			'a.id' => \JText::_('JGRID_HEADING_ID')
		);
	}
}
