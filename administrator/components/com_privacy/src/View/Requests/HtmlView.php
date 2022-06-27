<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\View\Requests;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Privacy\Administrator\Model\RequestsModel;

/**
 * Requests view class
 *
 * @since  3.9.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The active search tools filters
	 *
	 * @var    array
	 * @since  3.9.0
	 * @note   Must be public to be accessed from the search tools layout
	 */
	public $activeFilters;

	/**
	 * Form instance containing the search tools filter form
	 *
	 * @var    Form
	 * @since  3.9.0
	 * @note   Must be public to be accessed from the search tools layout
	 */
	public $filterForm;

	/**
	 * The items to display
	 *
	 * @var    array
	 * @since  3.9.0
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var    Pagination
	 * @since  3.9.0
	 */
	protected $pagination;

	/**
	 * Flag indicating the site supports sending email
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $sendMailEnabled;

	/**
	 * The state information
	 *
	 * @var    CMSObject
	 * @since  3.9.0
	 */
	protected $state;

	/**
	 * The age of urgent requests
	 *
	 * @var    integer
	 * @since  3.9.0
	 */
	protected $urgentRequestAge;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @see     BaseHtmlView::loadTemplate()
	 * @since   3.9.0
	 * @throws  \Exception
	 */
	public function display($tpl = null)
	{
		/** @var RequestsModel $model */
		$model                  = $this->getModel();
		$this->items            = $model->getItems();
		$this->pagination       = $model->getPagination();
		$this->state            = $model->getState();
		$this->filterForm       = $model->getFilterForm();
		$this->activeFilters    = $model->getActiveFilters();
		$this->urgentRequestAge = (int) ComponentHelper::getParams('com_privacy')->get('notify', 14);
		$this->sendMailEnabled  = (bool) Factory::getApplication()->get('mailonline', 1);

		if (!count($this->items) && $this->get('IsEmptyState'))
		{
			$this->setLayout('emptystate');
		}

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Genericdataexception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(Text::_('COM_PRIVACY_VIEW_REQUESTS'), 'lock');

		// Requests can only be created if mail sending is enabled
		if (Factory::getApplication()->get('mailonline', 1))
		{
			ToolbarHelper::addNew('request.add');
		}

		ToolbarHelper::preferences('com_privacy');
		ToolbarHelper::help('Privacy:_Information_Requests');
	}
}
