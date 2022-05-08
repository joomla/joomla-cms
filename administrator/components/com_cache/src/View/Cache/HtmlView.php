<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cache\Administrator\View\Cache;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Cache\Administrator\Model\CacheModel;

/**
 * HTML View class for the Cache component
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The search tools form
	 *
	 * @var    Form
	 * @since  1.6
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  1.6
	 */
	public $activeFilters = [];

	/**
	 * The cache data
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $data = [];

	/**
	 * The pagination object
	 *
	 * @var    Pagination
	 * @since  1.6
	 */
	protected $pagination;

	/**
	 * Total number of cache groups
	 *
	 * @var    integer
	 * @since  1.6
	 */
	protected $total = 0;

	/**
	 * The model state
	 *
	 * @var    CMSObject
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Display a view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 *
	 * @throws  GenericDataException
	 */
	public function display($tpl = null): void
	{
		/** @var CacheModel $model */
		$model               = $this->getModel();
		$this->data          = $model->getData();
		$this->pagination    = $model->getPagination();
		$this->total         = $model->getTotal();
		$this->state         = $model->getState();
		$this->filterForm    = $model->getFilterForm();
		$this->activeFilters = $model->getActiveFilters();

		// Check for errors.
		if (\count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		if (!\count($this->data))
		{
			$this->setLayout('emptystate');
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar(): void
	{
		ToolbarHelper::title(Text::_('COM_CACHE_CLEAR_CACHE'), 'bolt clear');

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		if (\count($this->data))
		{
			ToolbarHelper::custom('delete', 'delete', '', 'JTOOLBAR_DELETE', true);
			ToolbarHelper::custom('deleteAll', 'remove', '', 'JTOOLBAR_DELETE_ALL', false);
			$toolbar->appendButton('Confirm', 'COM_CACHE_RESOURCE_INTENSIVE_WARNING', 'delete', 'COM_CACHE_PURGE_EXPIRED', 'purge', false);
			ToolbarHelper::divider();
		}

		if (Factory::getUser()->authorise('core.admin', 'com_cache'))
		{
			ToolbarHelper::preferences('com_cache');
			ToolbarHelper::divider();
		}

		ToolbarHelper::help('JHELP_SITE_MAINTENANCE_CLEAR_CACHE');
	}
}
