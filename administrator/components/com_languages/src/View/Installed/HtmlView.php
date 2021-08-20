<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Languages\Administrator\View\Installed;

\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Displays a list of the installed languages.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Option (component) name
	 *
	 * @var string
	 */
	protected $option = null;

	/**
	 * The pagination object
	 *
	 * @var  \Joomla\CMS\Pagination\Pagination
	 */
	protected $pagination;

	/**
	 * Languages information
	 *
	 * @var array
	 */
	protected $rows = null;

	/**
	 * The model state
	 *
	 * @var    \JObject
	 * @since  4.0.0
	 */
	protected $state;

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
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$this->option        = $this->get('Option');
		$this->pagination    = $this->get('Pagination');
		$this->rows          = $this->get('Data');
		$this->total         = $this->get('Total');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');

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
		$canDo = ContentHelper::getActions('com_languages');

		if ((int) $this->state->get('client_id') === 1)
		{
			ToolbarHelper::title(Text::_('COM_LANGUAGES_VIEW_INSTALLED_ADMIN_TITLE'), 'comments langmanager');
		}
		else
		{
			ToolbarHelper::title(Text::_('COM_LANGUAGES_VIEW_INSTALLED_SITE_TITLE'), 'comments langmanager');
		}

		if ($canDo->get('core.edit.state'))
		{
			ToolbarHelper::makeDefault('installed.setDefault');
			ToolbarHelper::divider();
		}

		if ($canDo->get('core.admin'))
		{
			// Add install languages link to the lang installer component.
			$bar = Toolbar::getInstance('toolbar');

			// Switch administrator language
			if ($this->state->get('client_id', 0) == 1)
			{
				ToolbarHelper::custom('installed.switchadminlanguage', 'refresh', '', 'COM_LANGUAGES_SWITCH_ADMIN', true);
				ToolbarHelper::divider();
			}

			$bar->appendButton('Link', 'upload', 'COM_LANGUAGES_INSTALL', 'index.php?option=com_installer&view=languages');
			ToolbarHelper::divider();

			ToolbarHelper::preferences('com_languages');
			ToolbarHelper::divider();
		}

		ToolbarHelper::help('JHELP_EXTENSIONS_LANGUAGE_MANAGER_INSTALLED');
	}
}
