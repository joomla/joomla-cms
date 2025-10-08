<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Doctrine\Inflector\InflectorFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for a Joomla List View
 *
 * Class holding methods for displaying presentation data.
 *
 * @since  2.5.5
 */
class ListView extends HtmlView
{
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items = [];

    /**
     * The pagination object
     *
     * @var  \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * The actions the user is authorised to perform
     *
     * @var  Registry
     */
    protected $canDo;

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
    public $activeFilters = [];

    /**
     * The sidebar markup
     *
     * @var  string
     */
    protected $sidebar;

    /**
     * The toolbar title
     *
     * @var string
     */
    protected $toolbarTitle;

    /**
     * The toolbar icon
     *
     * @var string
     */
    protected $toolbarIcon;

    /**
     * The flag which determine whether we want to show archive button
     *
     * @var boolean
     */
    protected $supportsArchive = false;

    /**
     * The flag which determine whether we want to show batch button
     *
     * @var boolean
     */
    protected $supportsBatch = true;

    /**
     * The flag which determine if the current user is allowed to perform batch actions. It is
     * used in layout to determine if we need to render batch layout
     *
     * @var boolean
     */
    protected $batchAllowed = false;

    /**
     * Holds the extension for categories, if available
     *
     * @var string
     *
     * @since 6.0.0
     */
    protected $categorySection;

    /**
     * The help link for the view
     *
     * @var string
     */
    protected $helpLink;

    /**
     * Is this view an Empty State
     *
     * @var   boolean
     *
     * @since 6.0.0
     */
    private $isEmptyState = false;

    /**
     * Constructor
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        // Set class properties from config data passed in constructor
        if (isset($config['toolbar_title'])) {
            $this->toolbarTitle = $config['toolbar_title'];
        } else {
            $this->toolbarTitle = strtoupper($this->option . '_MANAGER_' . $this->getName());
        }

        if (isset($config['toolbar_icon'])) {
            $this->toolbarIcon = $config['toolbar_icon'];
        } else {
            $this->toolbarIcon = strtolower($this->getName());
        }

        if (isset($config['supports_batch'])) {
            $this->supportsBatch = $config['supports_batch'];
        }

        if (isset($config['category'])) {
            $this->categorySection = $config['category'];
        }

        if (isset($config['help_link'])) {
            $this->helpLink = $config['help_link'];
        }

        // Set default value for $canDo to avoid fatal error if child class doesn't set value for this property
        // Return a CanDo object to prevent any BC break, will be changed in 7.0 to Registry
        $this->canDo = new CanDo();
    }

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws  \Exception
     */
    public function display($tpl = null)
    {
        $model = $this->getModel();

        $model->setUseExceptions(true);

        // Prepare view data
        $this->initializeView();

        if (!\count($this->items) && \is_callable([$model, 'getIsEmptyState']) && $this->isEmptyState = $model->getIsEmptyState()) {
            $this->setLayout('emptystate');
        }

        // We don't need toolbar in the modal window.
        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        }

        parent::display($tpl);
    }

    /**
     * Prepare view data
     *
     * @return  void
     */
    protected function initializeView()
    {
        $componentName = substr($this->option, 4);
        $helperClass   = ucfirst($componentName . 'Helper');

        // Include the component helpers.
        \JLoader::register($helperClass, JPATH_COMPONENT . '/helpers/' . $componentName . '.php');

        if ($this->getLayout() !== 'modal') {
            if (\is_callable($helperClass . '::addSubmenu')) {
                \call_user_func([$helperClass, 'addSubmenu'], $this->getName());
            }

            $this->sidebar = HTMLHelper::_('sidebar.render');
        }

        $model = $this->getModel();

        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        // Add form control fields
        $this->filterForm
            ->addControlField('task', '')
            ->addControlField('boxchecked', '0');
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
        $canDo = $this->canDo;
        $user  = $this->getCurrentUser();

        /**
         * @var \Joomla\CMS\Toolbar\Toolbar $toolbar
         */
        $toolbar = $this->getDocument()->getToolbar();

        $viewName         = $this->getName();
        $singularViewName = InflectorFactory::create()->build()->singularize($viewName);

        ToolbarHelper::title(Text::_($this->toolbarTitle), $this->toolbarIcon);

        // @todo here we should add category support with some parameters
        if ($canDo->get('core.create') || (isset($this->categorySection) && \count($user->getAuthorisedCategories($this->categorySection, 'core.create')) > 0)) {
            $toolbar->addNew($singularViewName . '.add');
        }

        if (!$this->isEmptyState && $canDo->get('core.edit.state')) {
            /** @var  \Joomla\CMS\Toolbar\Button\DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group', 'JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            $childBar->publish($viewName . '.publish')->listCheck(true);
            $childBar->unpublish($viewName . '.unpublish')->listCheck(true);

            if (isset($this->items[0]->featured)) {
                $childBar->standardButton('featured', 'JFEATURE', $viewName . '.featured')->listCheck(true);
                $childBar->standardButton('unfeatured', 'JUNFEATURE', $viewName . '.unfeatured')->listCheck(true);
            }

            if ($this->supportsArchive) {
                $childBar->archive($viewName . '.archive')->listCheck(true);
            }

            if ($user->authorise('core.admin')) {
                $childBar->checkin($viewName . '.checkin');
            }

            if ($this->state->get('filter.published') != -2) {
                $childBar->trash($viewName . '.trash')->listCheck(true);
            }

            // Add a batch button
            if (
                $this->supportsBatch && $user->authorise('core.create', $this->option)
                && $user->authorise('core.create', $this->option)
                && $user->authorise('core.edit', $this->option)
            ) {
                $childBar->popupButton('batch', 'JTOOLBAR_BATCH')
                    ->popupType('inline')
                    ->textHeader(Text::_($this->option . '_BATCH_OPTIONS'))
                    ->url('#joomla-dialog-batch')
                    ->modalWidth('800px')
                    ->modalHeight('fit-content')
                    ->listCheck(true);

                $this->batchAllowed = true;
            }
        }

        if (
            !$this->isEmptyState
            && $canDo->get('core.delete')
            && (
                $this->state->get('filter.state') == -2
                || $this->state->get('filter.published') == -2
            )
        ) {
            $toolbar->delete($viewName . '.delete', 'JTOOLBAR_DELETE_FROM_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }

        if ($user->authorise('core.admin', $this->option) || $user->authorise('core.options', $this->option)) {
            $toolbar->preferences($this->option);
        }

        if ($this->helpLink) {
            $toolbar->help($this->helpLink);
        }
    }
}
