<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Administrator\View\Index;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Button\DropdownButton;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Finder\Administrator\Helper\FinderHelper;
use Joomla\Component\Finder\Administrator\Helper\LanguageHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Index view class for Finder.
 *
 * @since  2.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items
     *
     * @var  array
     *
     * @since  3.6.1
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var    \Joomla\CMS\Pagination\Pagination
     *
     * @since  3.6.1
     */
    protected $pagination;

    /**
     * The state of core Smart Search plugins
     *
     * @var  array
     *
     * @since  3.6.1
     */
    protected $pluginState;

    /**
     * The id of the content - finder plugin in mysql
     *
     * @var    integer
     *
     * @since  4.0.0
     */
    protected $finderPluginId = 0;

    /**
     * The model state
     *
     * @var    mixed
     *
     * @since  3.6.1
     */
    protected $state;

    /**
     * The total number of items
     *
     * @var    integer
     *
     * @since  3.6.1
     */
    protected $total;

    /**
     * Form object for search filters
     *
     * @var    \Joomla\CMS\Form\Form
     *
     * @since  4.0.0
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var    array
     *
     * @since  4.0.0
     */
    public $activeFilters;

    /**
     * @var mixed
     *
     * @since  4.0.0
     */
    private $isEmptyState = false;

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  A template file to load. [optional]
     *
     * @return  void
     *
     * @since   2.5
     */
    public function display($tpl = null)
    {
        // Load plugin language files.
        LanguageHelper::loadPluginLanguage();

        $this->items         = $this->get('Items');
        $this->total         = $this->get('Total');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->pluginState   = $this->get('pluginState');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        if ($this->get('TotalIndexed') === 0 && $this->isEmptyState = $this->get('IsEmptyState')) {
            $this->setLayout('emptystate');
        }

        // We do not need to filter by language when multilingual is disabled
        if (!Multilanguage::isEnabled()) {
            unset($this->activeFilters['language']);
            $this->filterForm->removeField('language', 'filter');
        }

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Check that the content - finder plugin is enabled
        if (!PluginHelper::isEnabled('content', 'finder')) {
            $this->finderPluginId = FinderHelper::getFinderPluginId();
        }

        // Configure the toolbar.
        $this->addToolbar();

        parent::display($tpl);
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
        $canDo   = ContentHelper::getActions('com_finder');
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_FINDER_INDEX_TOOLBAR_TITLE'), 'search-plus finder');

        if (JDEBUG) {
            $dropdown = $toolbar->dropdownButton('indexing-group');
            $dropdown->text('COM_FINDER_INDEX')
                ->toggleSplit(false)
                ->icon('icon-archive')
                ->buttonClass('btn btn-action');

            $childBar = $dropdown->getChildToolbar();

            $childBar->popupButton('index', 'COM_FINDER_INDEX')
                ->popupType('iframe')
                ->textHeader(Text::_('COM_FINDER_HEADING_INDEXER'))
                ->url('index.php?option=com_finder&view=indexer&tmpl=component')
                ->modalWidth('800px')
                ->modalHeight('400px')
                ->icon('icon-archive')
                ->title(Text::_('COM_FINDER_HEADING_INDEXER'));

            $childBar->linkButton('indexdebug', 'COM_FINDER_INDEX_TOOLBAR_INDEX_DEBUGGING')
                ->url('index.php?option=com_finder&view=indexer&layout=debug')
                ->icon('icon-tools');
        } else {
            $toolbar->popupButton('index', 'COM_FINDER_INDEX')
                ->popupType('iframe')
                ->textHeader(Text::_('COM_FINDER_HEADING_INDEXER'))
                ->url('index.php?option=com_finder&view=indexer&tmpl=component')
                ->modalWidth('800px')
                ->modalHeight('400px')
                ->icon('icon-archive')
                ->title(Text::_('COM_FINDER_HEADING_INDEXER'));
        }


        if (!$this->isEmptyState) {
            if ($canDo->get('core.edit.state')) {
                $dropdown = $toolbar->dropdownButton('status-group')
                    ->text('JTOOLBAR_CHANGE_STATUS')
                    ->toggleSplit(false)
                    ->icon('icon-ellipsis-h')
                    ->buttonClass('btn btn-action')
                    ->listCheck(true);

                $childBar = $dropdown->getChildToolbar();

                $childBar->publish('index.publish')->listCheck(true);
                $childBar->unpublish('index.unpublish')->listCheck(true);
            }

            if ($canDo->get('core.delete')) {
                $toolbar->confirmButton('delete', 'JTOOLBAR_DELETE', 'index.delete')
                    ->message('COM_FINDER_INDEX_CONFIRM_DELETE_PROMPT')
                    ->icon('icon-delete')
                    ->listCheck(true);
                $toolbar->divider();
            }

            if ($canDo->get('core.edit.state')) {
                /** @var DropdownButton $dropdown */
                $dropdown = $toolbar->dropdownButton('maintenance-group', 'COM_FINDER_INDEX_TOOLBAR_MAINTENANCE')
                    ->toggleSplit(false)
                    ->icon('icon-wrench')
                    ->buttonClass('btn btn-action');

                $childBar = $dropdown->getChildToolbar();

                $childBar->standardButton('cog', 'COM_FINDER_INDEX_TOOLBAR_OPTIMISE', 'index.optimise');
                $childBar->confirmButton('index-purge', 'COM_FINDER_INDEX_TOOLBAR_PURGE', 'index.purge')
                    ->message('COM_FINDER_INDEX_CONFIRM_PURGE_PROMPT')
                    ->icon('icon-trash');
            }

            $toolbar->popupButton('statistics', 'COM_FINDER_STATISTICS')
                ->popupType('iframe')
                ->textHeader(Text::_('COM_FINDER_STATISTICS_TITLE'))
                ->url('index.php?option=com_finder&view=statistics&tmpl=component')
                ->modalWidth('800px')
                ->modalHeight('500px')
                ->title(Text::_('COM_FINDER_STATISTICS_TITLE'))
                ->icon('icon-bars');
        }

        if ($canDo->get('core.admin') || $canDo->get('core.options')) {
            $toolbar->preferences('com_finder');
        }

        $toolbar->help('Smart_Search:_Indexed_Content');
    }
}
