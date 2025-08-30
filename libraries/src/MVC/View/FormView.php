<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Associations\Administrator\Helper\AssociationsHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for a Joomla Form View
 *
 * Class holding methods for displaying presentation data.
 *
 * @since  2.5.5
 */
class FormView extends HtmlView
{
    /**
     * The \JForm object
     *
     * @var  \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The active item
     *
     * @var  object
     */
    protected $item;

    /**
     * The item primary key name
     *
     * @var  string
     */
    protected $keyName;

    /**
     * The model state
     *
     * @var  object
     */
    protected $state;

    /**
     * The actions the user is authorised to perform
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $canDo;

    /**
     * Array of fieldsets not to display
     *
     * @var    string[]
     */
    public $ignore_fieldsets = [];

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
     * The preview link
     *
     * @var string
     */
    protected $previewLink;

    /**
     * The jooa11y link
     *
     * @var string
     */
    protected $jooa11yLink;

    /**
     * The help link
     *
     * @var string
     */
    protected $helpLink;

    /**
     * Set to true, if saving to menu should be supported
     *
     * @var boolean
     */
    protected $supportSaveMenu = false;

    /**
     * Holds the extension for categories, if available
     *
     * @var string
     */
    protected $categorySection;

    /**
     * Constructor
     *
     * @param   array  $config  An optional associative array of configuration settings.
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        if (isset($config['help_link'])) {
            $this->helpLink = $config['help_link'];
        }

        if (isset($config['preview_link'])) {
            $this->previewLink = $config['preview_link'];
        }

        if (isset($config['jooa11y_link'])) {
            $this->jooa11yLink = $config['jooa11y_link'];
        }

        if (isset($config['toolbar_icon'])) {
            $this->toolbarIcon = $config['toolbar_icon'];
        } else {
            $this->toolbarIcon = 'pencil-2 ' . $this->getName() . '-add';
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
        /**
         * @var AdminModel
         */
        $model = $this->getModel();

        $this->form  = $model->getForm();
        $this->item  = $model->getItem();
        $this->state = $model->getState();
        $table       = $model->getTable();

        $this->keyName = $table instanceof TableInterface ? $table->getKeyName() : 'id';
        $action        = empty($this->item->{$this->keyName}) ? '_NEW' : '_EDIT';

        // Set default toolbar title
        $this->toolbarTitle = Text::_(strtoupper($this->option . '_MANAGER_' . $this->getName() . $action));
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
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $user       = $this->getCurrentUser();
        $userId     = $user->id;
        $isNew      = empty($this->item->{$this->keyName});
        $viewName   = $this->getName();
        $checkedOut = !$isNew && $this->getModel()->isCheckedOut($this->item);
        $canDo      = $this->canDo;

        /**
         * @var Toolbar $toolbar
         */
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(
            $this->toolbarTitle,
            $this->toolbarIcon
        );

        // For new records, check the create permission.
        if ($isNew) {
            if ($canDo->get('core.create') || (isset($this->categorySection) && \count($user->getAuthorisedCategories($this->categorySection, 'core.create')) > 0)) {
                $toolbar->apply($viewName . '.apply');

                $saveGroup = $toolbar->dropdownButton('save-group');

                $saveGroup->configure(
                    function (Toolbar $childBar) use ($user, $viewName) {
                        $childBar->save($viewName . '.save');

                        if ($this->supportSaveMenu && $user->authorise('core.create', 'com_menus.menu')) {
                            $childBar->save($viewName . '.save2menu', 'JTOOLBAR_SAVE_TO_MENU');
                        }

                        $childBar->save2new($viewName . '.save2new');
                    }
                );
            }

            $toolbar->cancel($viewName . '.cancel', 'JTOOLBAR_CANCEL');
        } else {
            $itemEditable = $canDo->get('core.edit');

            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            if (property_exists($this->item, 'created_by')) {
                $itemEditable = $itemEditable || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);
            }

            if (!$checkedOut && $itemEditable) {
                $toolbar->apply($viewName . '.apply');
            }

            $saveGroup = $toolbar->dropdownButton('save-group');

            $saveGroup->configure(
                function (Toolbar $childBar) use ($checkedOut, $itemEditable, $canDo, $user, $viewName) {
                    // Can't save the record if it's checked out and editable
                    if (!$checkedOut && $itemEditable) {
                        $childBar->save($viewName . '.save');

                        // We can save this record, but check the create permission to see if we can return to make a new one.
                        if ($canDo->get('core.create')) {
                            $childBar->save2new($viewName . '.save2new');
                        }
                    }

                    // If checked out, we can still save2menu
                    if ($this->supportSaveMenu && $user->authorise('core.create', 'com_menus.menu')) {
                        $childBar->save($viewName . '.save2menu', 'JTOOLBAR_SAVE_TO_MENU');
                    }

                    // If checked out, we can still save
                    if ($canDo->get('core.create')) {
                        $childBar->save2copy($viewName . '.save2copy');
                    }
                }
            );

            $toolbar->cancel($viewName . '.cancel', 'JTOOLBAR_CLOSE');

            if (ComponentHelper::isEnabled('com_contenthistory') && $this->state->get('params')->get('save_history', 0) && $itemEditable) {
                $toolbar->versions(
                    $this->option . '.' . $viewName,
                    $this->item->{$this->keyName}
                );
            }

            if ($this->previewLink) {
                $toolbar->preview(Route::link('site', $this->previewLink, true), Text::_('JGLOBAL_PREVIEW'))
                    ->bodyHeight(80)
                    ->modalWidth(90);
            }

            if ($this->jooa11yLink && PluginHelper::isEnabled('system', 'jooa11y')) {
                $toolbar->jooa11y(Route::link('site', $this->jooa11yLink, true), 'JGLOBAL_JOOA11Y')
                    ->bodyHeight(80)
                    ->modalWidth(90);
            }

            if (
                Associations::isEnabled() &&
                ComponentHelper::isEnabled('com_associations') &&
                AssociationsHelper::hasSupport($this->option)
            ) {
                $toolbar->standardButton('contract', 'JTOOLBAR_ASSOCIATIONS', $viewName . '.editAssociations')
                    ->icon('icon-contract')
                    ->listCheck(false);
            }
        }

        $toolbar->divider();

        if ($this->form instanceof Form) {
            $formConfig  = $this->form->getXml()->config->inlinehelp;

            if ($formConfig && (string) $formConfig['button'] === 'show') {
                $targetClass = (string) $formConfig['targetclass'] ?: 'hide-aware-inline-help';

                $toolbar->inlinehelp($targetClass);
            }
        }

        if ($this->helpLink) {
            $toolbar->help($this->helpLink);
        }
    }
}
