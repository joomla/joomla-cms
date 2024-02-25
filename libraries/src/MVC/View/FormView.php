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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
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
     * @var  CMSObject
     */
    protected $canDo;

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
     * The help link
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

        if (isset($config['help_link'])) {
            $this->helpLink = $config['help_link'];
        }

        if (isset($config['toolbar_icon'])) {
            $this->toolbarIcon = $config['toolbar_icon'];
        } else {
            $this->toolbarIcon = 'pencil-2 ' . $this->getName() . '-add';
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
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');
        $table       = $this->get('Table');

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

        $user       = Factory::getUser();
        $userId     = $user->id;
        $isNew      = empty($this->item->{$this->keyName});
        $viewName   = $this->getName();
        $checkedOut = $this->getModel()->isCheckedOut($this->item);
        $canDo      = $this->canDo;

        ToolbarHelper::title(
            $this->toolbarTitle,
            $this->toolbarIcon
        );

        // For new records, check the create permission.
        if ($isNew && $canDo->get('core.create')) {
            ToolbarHelper::saveGroup(
                [
                    ['apply', $viewName . '.apply'],
                    ['save', $viewName . '.save'],
                    ['save2new', $viewName . '.save2new'],
                ],
                'btn-success'
            );

            ToolbarHelper::cancel($viewName . '.cancel');
        } else {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            if (property_exists($this->item, 'created_by')) {
                $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);
            } else {
                $itemEditable = $canDo->get('core.edit');
            }

            $toolbarButtons = [];

            // Can't save the record if it's checked out and editable
            if (!$checkedOut && $itemEditable) {
                $toolbarButtons[] = ['apply', $viewName . '.apply'];
                $toolbarButtons[] = ['save', $viewName . '.save'];

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($canDo->get('core.create')) {
                    $toolbarButtons[] = ['save2new', $viewName . '.save2new'];
                }
            }

            // If checked out, we can still save
            if ($canDo->get('core.create')) {
                $toolbarButtons[] = ['save2copy', $viewName . '.save2copy'];
            }

            ToolbarHelper::saveGroup(
                $toolbarButtons,
                'btn-success'
            );

            if (ComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $itemEditable) {
                ToolbarHelper::versions($this->option . '.' . $viewName, $this->item->id);
            }

            if (!$isNew && $this->previewLink) {
                ToolbarHelper::preview($this->previewLink, Text::_('JGLOBAL_PREVIEW'), 'eye', 80, 90);
            }

            ToolbarHelper::cancel($viewName . '.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();

        if ($this->helpLink) {
            ToolbarHelper::help($this->helpLink);
        }
    }
}
