<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Doctrine\Inflector\InflectorFactory;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
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
     * @var  CMSObject
     */
    protected $state;

    /**
     * The actions the user is authorised to perform
     *
     * @var  CMSObject
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
    public $activeFilters;

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
     * The flag which determine whether we want to show batch button
     *
     * @var boolean
     */
    protected $supportsBatch = true;

    /**
     * The help link for the view
     *
     * @var string
     */
    protected $helpLink;

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

        if (isset($config['help_link'])) {
            $this->helpLink = $config['help_link'];
        }

        // Set default value for $canDo to avoid fatal error if child class doesn't set value for this property
        $this->canDo = new CMSObject();
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
        // Prepare view data
        $this->initializeView();

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Build toolbar
        $this->addToolbar();

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

            $this->sidebar = \JHtmlSidebar::render();
        }

        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
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
        $user  = Factory::getUser();

        // Get the toolbar object instance
        $bar = Toolbar::getInstance('toolbar');

        $viewName         = $this->getName();
        $singularViewName = InflectorFactory::create()->build()->singularize($viewName);

        ToolbarHelper::title(Text::_($this->toolbarTitle), $this->toolbarIcon);

        if ($canDo->get('core.create')) {
            ToolbarHelper::addNew($singularViewName . '.add');
        }

        if (($canDo->get('core.edit')) || ($canDo->get('core.edit.own'))) {
            ToolbarHelper::editList($singularViewName . '.edit');
        }

        if ($canDo->get('core.edit.state')) {
            ToolbarHelper::publish($viewName . '.publish', 'JTOOLBAR_PUBLISH', true);
            ToolbarHelper::unpublish($viewName . '.unpublish', 'JTOOLBAR_UNPUBLISH', true);

            if (isset($this->items[0]->featured)) {
                ToolbarHelper::custom($viewName . '.featured', 'featured', '', 'JFEATURE', true);
                ToolbarHelper::custom($viewName . '.unfeatured', 'unfeatured', '', 'JUNFEATURE', true);
            }

            ToolbarHelper::archiveList($viewName . '.archive');
            ToolbarHelper::checkin($viewName . '.checkin');
        }

        // Add a batch button
        if (
            $this->supportsBatch && $user->authorise('core.create', $this->option)
            && $user->authorise('core.edit', $this->option)
            && $user->authorise('core.edit.state', $this->option)
        ) {
            $title = Text::_('JTOOLBAR_BATCH');

            // Instantiate a new LayoutFile instance and render the popup button
            $layout = new FileLayout('joomla.toolbar.popup');

            $dhtml = $layout->render(['title' => $title]);
            $bar->appendButton('Custom', $dhtml, 'batch');
        }

        if (
            $canDo->get('core.delete') &&
            (
                $this->state->get('filter.state') == -2 ||
                $this->state->get('filter.published') == -2
            )
        ) {
            ToolbarHelper::deleteList('JGLOBAL_CONFIRM_DELETE', $viewName . '.delete', 'JTOOLBAR_EMPTY_TRASH');
        } elseif ($canDo->get('core.edit.state')) {
            ToolbarHelper::trash($viewName . '.trash');
        }

        if ($user->authorise('core.admin', $this->option) || $user->authorise('core.options', $this->option)) {
            ToolbarHelper::preferences($this->option);
        }

        if ($this->helpLink) {
            ToolbarHelper::help($this->helpLink);
        }
    }
}
