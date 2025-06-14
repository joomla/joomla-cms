<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\View\Menu;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Menus\Administrator\Model\MenuModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The HTML Menus Menu Item View.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The Form object
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
     * The model state
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * The actions the user is authorised to perform
     *
     * @var  \Joomla\Registry\Registry
     */
    protected $canDo;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        /** @var MenuModel $model */
        $model = $this->getModel();

        $this->form  = $model->getForm();
        $this->item  = $model->getItem();
        $this->state = $model->getState();

        $this->canDo = ContentHelper::getActions('com_menus', 'menu', $this->item->id);

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Add form control fields
        $this->form->addControlField('task', '');

        parent::display($tpl);
        $this->addToolbar();
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
        $input = Factory::getApplication()->getInput();
        $input->set('hidemainmenu', true);

        $isNew   = ($this->item->id == 0);
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_($isNew ? 'COM_MENUS_VIEW_NEW_MENU_TITLE' : 'COM_MENUS_VIEW_EDIT_MENU_TITLE'), 'list menu');

        // If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
        if ($isNew && $this->canDo->get('core.create') && $this->canDo->get('core.edit')) {
            $toolbar->apply('menu.apply');
        }

        // If user can edit, can save the item.
        if (!$isNew && $this->canDo->get('core.edit')) {
            $toolbar->apply('menu.apply');
        }

        $saveGroup = $toolbar->dropdownButton('save-group');
        $canDo     = $this->canDo;

        $saveGroup->configure(
            function (Toolbar $childBar) use ($isNew, $canDo) {
                // If a new item, can save the item.  Allow users with edit permissions to apply changes to prevent returning to grid.
                if ($isNew && $canDo->get('core.create')) {
                    $childBar->save('menu.save');
                }

                // If user can edit, can save the item.
                if (!$isNew && $canDo->get('core.edit')) {
                    $childBar->save('menu.save');
                }

                // If the user can create new items, allow them to see Save & New
                if ($canDo->get('core.create')) {
                    $childBar->save2new('menu.save2new');
                }
            }
        );

        if ($isNew) {
            $toolbar->cancel('menu.cancel', 'JTOOLBAR_CANCEL');
        } else {
            $toolbar->cancel('menu.cancel');
        }

        $toolbar->divider();
        $toolbar->help('Menus:_Edit');
    }
}
