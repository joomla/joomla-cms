<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\View\Articles;

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

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of articles.
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
     * @var  \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var   \Joomla\CMS\Object\CMSObject
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var  \Joomla\CMS\Form\Form
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
     * @var   boolean
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

        if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
            $this->setLayout('emptystate');
        }

        if (ComponentHelper::getParams('com_content')->get('workflow_enabled')) {
            PluginHelper::importPlugin('workflow');

            $this->transitions = $this->get('Transitions');
        }

        // Check for errors.
        if (\count($errors = $this->get('Errors')) || $this->transitions === false) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();

            // We do not need to filter by language when multilingual is disabled
            if (!Multilanguage::isEnabled()) {
                unset($this->activeFilters['language']);
                $this->filterForm->removeField('language', 'filter');
            }
        } else {
            // In article associations modal we need to remove language filter if forcing a language.
            // We also need to change the category filter to show show categories with All or the forced language.
            if ($forcedLanguage = Factory::getApplication()->input->get('forcedLanguage', '', 'CMD')) {
                // If the language is forced we can't allow to select the language, so transform the language selector filter into a hidden field.
                $languageXml = new \SimpleXMLElement('<field name="language" type="hidden" default="' . $forcedLanguage . '" />');
                $this->filterForm->setField($languageXml, 'filter', true);

                // Also, unset the active language filter so the search tools is not open by default with this filter.
                unset($this->activeFilters['language']);

                // One last changes needed is to change the category filter to just show categories with All language or with the forced language.
                $this->filterForm->setFieldAttribute('category_id', 'language', '*,' . $forcedLanguage, 'filter');
            }
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
        $user  = $this->getCurrentUser();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('COM_CONTENT_ARTICLES_TITLE'), 'copy article');

        if ($canDo->get('core.create') || \count($user->getAuthorisedCategories('com_content', 'core.create')) > 0) {
            $toolbar->addNew('article.add');
        }

        if (!$this->isEmptyState && ($canDo->get('core.edit.state') || \count($this->transitions))) {
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if (\count($this->transitions)) {
                $childBar->separatorButton('transition-headline')
                    ->text('COM_CONTENT_RUN_TRANSITIONS')
                    ->buttonClass('text-center py-2 h3');

                $cmd = "Joomla.submitbutton('articles.runTransition');";
                $messages = "{error: [Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
                $alert = 'Joomla.renderMessages(' . $messages . ')';
                $cmd   = 'if (document.adminForm.boxchecked.value == 0) { ' . $alert . ' } else { ' . $cmd . ' }';

                foreach ($this->transitions as $transition) {
                    $childBar->standardButton('transition')
                        ->text($transition['text'])
                        ->buttonClass('transition-' . (int) $transition['value'])
                        ->icon('icon-project-diagram')
                        ->onclick('document.adminForm.transition_id.value=' . (int) $transition['value'] . ';' . $cmd);
                }

                $childBar->separatorButton('transition-separator');
            }

            if ($canDo->get('core.edit.state')) {
                $childBar->publish('articles.publish')->listCheck(true);

                $childBar->unpublish('articles.unpublish')->listCheck(true);

                $childBar->standardButton('featured')
                    ->text('JFEATURE')
                    ->task('articles.featured')
                    ->listCheck(true);

                $childBar->standardButton('unfeatured')
                    ->text('JUNFEATURE')
                    ->task('articles.unfeatured')
                    ->listCheck(true);

                $childBar->archive('articles.archive')->listCheck(true);

                $childBar->checkin('articles.checkin')->listCheck(true);

                if ($this->state->get('filter.published') != ContentComponent::CONDITION_TRASHED) {
                    $childBar->trash('articles.trash')->listCheck(true);
                }
            }

            // Add a batch button
            if (
                $user->authorise('core.create', 'com_content')
                && $user->authorise('core.edit', 'com_content')
                && $user->authorise('core.execute.transition', 'com_content')
            ) {
                $childBar->popupButton('batch')
                    ->text('JTOOLBAR_BATCH')
                    ->selector('collapseModal')
                    ->listCheck(true);
            }
        }

        if (!$this->isEmptyState && $this->state->get('filter.published') == ContentComponent::CONDITION_TRASHED && $canDo->get('core.delete')) {
            $toolbar->delete('articles.delete')
                ->text('JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        if ($user->authorise('core.admin', 'com_content') || $user->authorise('core.options', 'com_content')) {
            $toolbar->preferences('com_content');
        }

        $toolbar->help('Articles');
    }
}
