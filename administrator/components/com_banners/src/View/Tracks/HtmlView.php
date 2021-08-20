<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\View\Tracks;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Banners\Administrator\Model\TracksModel;

/**
 * View class for a list of tracks.
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
	 * An array of items
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $items = [];

	/**
	 * The pagination object
	 *
	 * @var    Pagination
	 * @since  1.6
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var    CMSObject
	 * @since  1.6
	 */
	protected $state;

	/**
	 * Is this view an Empty State
	 *
	 * @var  boolean
	 * @since 4.0.0
	 */
	private $isEmptyState = false;

	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  Exception
	 */
	public function display($tpl = null): void
	{
		/** @var TracksModel $model */
		$model               = $this->getModel();
		$this->items         = $model->getItems();
		$this->pagination    = $model->getPagination();
		$this->state         = $model->getState();
		$this->filterForm    = $model->getFilterForm();
		$this->activeFilters = $model->getActiveFilters();

		if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState'))
		{
			$this->setLayout('emptystate');
		}

		// Check for errors.
		if (\count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
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
		$canDo = ContentHelper::getActions('com_banners', 'category', $this->state->get('filter.category_id'));

		ToolbarHelper::title(Text::_('COM_BANNERS_MANAGER_TRACKS'), 'bookmark banners-tracks');

		$bar = Toolbar::getInstance('toolbar');

		if (!$this->isEmptyState)
		{
			$bar->popupButton()
				->url(Route::_('index.php?option=com_banners&view=download&tmpl=component'))
				->text('JTOOLBAR_EXPORT')
				->selector('downloadModal')
				->icon('icon-download')
				->footer('<button class="btn btn-secondary" data-bs-dismiss="modal" type="button"'
					. ' onclick="window.parent.Joomla.Modal.getCurrent().close();">'
					. Text::_('COM_BANNERS_CANCEL') . '</button>'
					. '<button class="btn btn-success" type="button"'
					. ' onclick="Joomla.iframeButtonClick({iframeSelector: \'#downloadModal\', buttonSelector: \'#exportBtn\'})">'
					. Text::_('COM_BANNERS_TRACKS_EXPORT') . '</button>'
				);
		}

		if (!$this->isEmptyState && $canDo->get('core.delete'))
		{
			$bar->appendButton('Confirm', 'COM_BANNERS_DELETE_MSG', 'delete', 'COM_BANNERS_TRACKS_DELETE', 'tracks.delete', false);
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::preferences('com_banners');
		}

		ToolbarHelper::help('JHELP_COMPONENTS_BANNERS_TRACKS');
	}
}
