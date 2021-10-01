<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\View\Templates;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View class for a list of template styles.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The list of templates
	 *
	 * @var		array
	 * @since   1.6
	 */
	protected $items;

	/**
	 * The pagination object
	 *
	 * @var		object
	 * @since   1.6
	 */
	protected $pagination;

	/**
	 * The model state
	 *
	 * @var		object
	 * @since   1.6
	 */
	protected $state;

	/**
	 * @var		string
	 * @since   3.2
	 */
	protected $file;

	/**
	 * Form object for search filters
	 *
	 * @var    \JForm
	 * @since  4.0.0
	 */
	public $filterForm;

	/**
	 * The active search filters
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	public $activeFilters;

	/**
	 * Is the parameter enabled to show template positions in the frontend?
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	public $preview;

	/**
	 * The state of installer override plugin.
	 *
	 * @var  array
	 *
	 * @since  4.0.0
	 */
	protected $pluginState;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->total         = $this->get('Total');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->preview       = ComponentHelper::getParams('com_templates')->get('template_positions_display');
		$this->file          = base64_encode('home');
		$this->pluginState   = PluginHelper::isEnabled('installer', 'override');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
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
	protected function addToolbar()
	{
		$canDo = ContentHelper::getActions('com_templates');

		// Set the title.
		if ((int) $this->get('State')->get('client_id') === 1)
		{
			ToolbarHelper::title(Text::_('COM_TEMPLATES_MANAGER_TEMPLATES_ADMIN'), 'paint-brush thememanager');
		}
		else
		{
			ToolbarHelper::title(Text::_('COM_TEMPLATES_MANAGER_TEMPLATES_SITE'), 'paint-brush thememanager');
		}

		if ($canDo->get('core.admin') || $canDo->get('core.options'))
		{
			ToolbarHelper::preferences('com_templates');
			ToolbarHelper::divider();
		}

		ToolbarHelper::help('Templates:_Templates');
	}
}
