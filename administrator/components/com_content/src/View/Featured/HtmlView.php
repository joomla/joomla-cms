<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\View\Featured;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Component\Content\Administrator\Helper\ContentHelper;

/**
 * View class for a list of featured articles.
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
	 * @var  \JObject
	 */
	protected $state;

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
	 * All transition, which can be executed of one if the items
	 *
	 * @var  array
	 */
	protected $transitions = [];

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
	 */
	public function display($tpl = null)
	{
		$this->items         = $this->get('Items');
		$this->pagination    = $this->get('Pagination');
		$this->state         = $this->get('State');
		$this->filterForm    = $this->get('FilterForm');
		$this->activeFilters = $this->get('ActiveFilters');
		$this->vote          = PluginHelper::isEnabled('content', 'vote');
		$this->hits          = ComponentHelper::getParams('com_content')->get('record_hits', 1);

		if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState'))
		{
			$this->setLayout('emptystate');
		}

		if (ComponentHelper::getParams('com_content')->get('workflow_enabled'))
		{
			PluginHelper::importPlugin('workflow');

			$this->transitions = $this->get('Transitions');
		}

		// Check for errors.
		if (\count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		// We do not need to filter by language when multilingual is disabled
		if (!Multilanguage::isEnabled())
		{
			unset($this->activeFilters['language']);
			$this->filterForm->removeField('language', 'filter');
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
		$canDo = ContentHelper::getActions('com_content', 'category', $this->state->get('filter.category_id'));
		$user  = Factory::getApplication()->getIdentity();

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('COM_CONTENT_FEATURED_TITLE'), 'star featured');

		if ($canDo->get('core.create') || \count($user->getAuthorisedCategories('com_content', 'core.create')) > 0)
		{
			$toolbar->addNew('article.add');
		}

		if (!$this->isEmptyState && ($canDo->get('core.edit.state') || \count($this->transitions)))
		{
			$dropdown = $toolbar->dropdownButton('status-group')
				->text('JTOOLBAR_CHANGE_STATUS')
				->toggleSplit(false)
				->icon('icon-ellipsis-h')
				->buttonClass('btn btn-action')
				->listCheck(true);

			$childBar = $dropdown->getChildToolbar();

			if (\count($this->transitions))
			{
				$childBar->separatorButton('transition-headline')
					->text('COM_CONTENT_RUN_TRANSITIONS')
					->buttonClass('text-center py-2 h3');

				$cmd = "Joomla.submitbutton('articles.runTransition');";
				$messages = "{error: [Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
				$alert = 'Joomla.renderMessages(' . $messages . ')';
				$cmd   = 'if (document.adminForm.boxchecked.value == 0) { ' . $alert . ' } else { ' . $cmd . ' }';

				foreach ($this->transitions as $transition)
				{
					$childBar->standardButton('transition')
						->text($transition['text'])
						->buttonClass('transition-' . (int) $transition['value'])
						->icon('icon-project-diagram')
						->onclick('document.adminForm.transition_id.value=' . (int) $transition['value'] . ';' . $cmd);
				}

				$childBar->separatorButton('transition-separator');
			}

			if ($canDo->get('core.edit.state'))
			{
				$childBar->publish('articles.publish')->listCheck(true);

				$childBar->unpublish('articles.unpublish')->listCheck(true);

				$childBar->standardButton('unfeatured')
					->text('JUNFEATURE')
					->task('articles.unfeatured')
					->listCheck(true);

				$childBar->archive('articles.archive')->listCheck(true);

				$childBar->checkin('articles.checkin')->listCheck(true);

				if (!$this->state->get('filter.published') == ContentComponent::CONDITION_TRASHED)
				{
					$childBar->trash('articles.trash')->listCheck(true);
				}
			}
		}

		if (!$this->isEmptyState && $this->state->get('filter.published') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete'))
		{
			$toolbar->delete('articles.delete')
				->text('JTOOLBAR_EMPTY_TRASH')
				->message('JGLOBAL_CONFIRM_DELETE')
				->listCheck(true);
		}

		if ($user->authorise('core.admin', 'com_content') || $user->authorise('core.options', 'com_content'))
		{
			$toolbar->preferences('com_content');
		}

		ToolbarHelper::help('JHELP_CONTENT_FEATURED_ARTICLES');
	}
}
