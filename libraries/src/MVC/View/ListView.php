<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Joomla\CMS\Factory;
use Doctrine\Inflector\InflectorFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Component\ComponentHelper;


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
     * Is the vote plugin enabled on the site
     *
     * @var   boolean
     * @since 4.4.0
     */
    protected $vote = false;

    /**
     * Are hits being recorded on the site?
     *
     * @var   boolean
     * @since 4.4.0
     */
    protected $hits = false;

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

            $this->sidebar = HTMLHelper::_('sidebar.render');
        }

        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        
        $this->canDo   = ContentHelper::getActions($this->option, 'category', $this->state->get('filter.category_id'));
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
        $this->initializeToolbar();
        
        if ($this->canDo->get('core.admin') || $this->canDo->get('core.options')) {
            ToolbarHelper::preferences($this->option);
        }
        
        if ($this->helpLink) {
            ToolbarHelper::help($this->helpLink);
        }
    }
    
    
    protected function appendMoreButton()
    {
        // For example:
        // if (($this->canDo->get('core.edit')) || ($this->canDo->get('core.edit.own'))) {
            // ToolbarHelper::editList($singularViewName . '.edit');
        // }
    }
    
    protected function initializeToolbar() 
    {
        $viewName         = $this->getName();
        $singularViewName = InflectorFactory::create()->build()->singularize($viewName);
        $componentName = substr($this->option, 4);
        $extensionClass   = ucfirst($componentName) . 'Component';
        
        // @TODO: call CONDITION_TRASHED constant in src/Extension/$extensionClass
        // $reflection = new \ReflectionClass($this);
        // $viewNamespace = $reflection->getNamespaceName();
        // $developer = explode('\\', $viewNamespace)[0];
        // var_dump($developer."\\Component\\".ucfirst($componentName)."\\Administrator\\Extension\\".$extensionClass);
        // var_dump($developer\\Component\\ucfirst($componentName)\\Administrator\\Extension\\$extensionClass::CONDITION_TRASHED);
        // var_dump($extensionClass::CONDITION_TRASHED);
        // $trashCondition = ($developer."\\Component\\Mrjoomlacart\\Administrator\\Extension\\".$extensionClass::CONDITION_TRASHED) ?: -2;        
        $trashCondition = -2;

        $user    = $this->getCurrentUser();
        $toolbar = Toolbar::getInstance();
       
        ToolbarHelper::title(Text::_($this->toolbarTitle), $this->toolbarIcon);

        if ($this->canDo->get('core.create') || \count($user->getAuthorisedCategories($this->option, 'core.create')) > 0) {
            $toolbar->addNew($singularViewName . '.add');
        }

        if (!$this->isEmptyState && ($this->canDo->get('core.edit.state') || \count($this->transitions))) {
            /** @var  DropdownButton $dropdown */
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);

            $childBar = $dropdown->getChildToolbar();

            if ($this->canDo->get('core.execute.transition') && \count($this->transitions)) {
                $childBar->separatorButton('transition-headline')
                    ->text('JTOOLBAR_RUN_TRANSITIONS')
                    ->buttonClass('text-center py-2 h3');

                $cmd      = "Joomla.submitbutton(".$viewName."'.runTransition');";
                $messages = "{error: [Joomla.JText._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST')]}";
                $alert    = 'Joomla.renderMessages(' . $messages . ')';
                $cmd      = 'if (document.adminForm.boxchecked.value == 0) { ' . $alert . ' } else { ' . $cmd . ' }';

                foreach ($this->transitions as $transition) {
                    $childBar->standardButton('transition', $transition['text'])
                        ->buttonClass('transition-' . (int) $transition['value'])
                        ->icon('icon-project-diagram')
                        ->onclick('document.adminForm.transition_id.value=' . (int) $transition['value'] . ';' . $cmd);
                }

                $childBar->separatorButton('transition-separator');
            }
                        

            if ($this->canDo->get('core.edit.state')) {
                $childBar->publish($viewName . '.publish')->listCheck(true);
                $childBar->unpublish($viewName . '.unpublish')->listCheck(true);
                
                if (isset($this->items[0]->featured)) {
                    $childBar->standardButton('featured', 'JFEATURE', $viewName . '.featured')
                    ->listCheck(true);
                    $childBar->standardButton('unfeatured', 'JUNFEATURE', $viewName . '.unfeatured')
                    ->listCheck(true);
                }

                $childBar->archive($viewName . '.archive')->listCheck(true);

                $childBar->checkin($viewName . '.checkin');

                if ($this->state->get('filter.published') != $trashCondition) {
                    $childBar->trash($viewName . '.trash')->listCheck(true);
                }
            }

            // Add a batch button
            if (
            $this->supportsBatch && $this->canDo->get('core.create')
            && $this->canDo->get('core.edit')
            && $this->canDo->get('core.edit.state')
            )
            {
                $childBar->popupButton('batch', 'JTOOLBAR_BATCH')
                    ->selector('collapseModal')
                    ->listCheck(true);
            }
        }
        
        $this->appendMoreButton();

        if (!$this->isEmptyState && $this->state->get('filter.published') == $trashCondition && $this->canDo->get('core.delete')) {
            $toolbar->delete($viewName.'.delete', 'JTOOLBAR_EMPTY_TRASH')
                ->message('JGLOBAL_CONFIRM_DELETE')
                ->listCheck(true);
        }
    }
}
