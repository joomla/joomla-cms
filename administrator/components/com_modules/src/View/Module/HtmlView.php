<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\View\Module;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Modules\Administrator\Model\ModuleModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit a module.
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
     * @var    \Joomla\Registry\Registry
     *
     * @since  4.0.0
     */
    protected $canDo;

    /**
     * Array of fieldsets not to display
     *
     * @var    string[]
     *
     * @since  5.2.0
     */
    public $ignore_fieldsets = [];

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        /** @var ModuleModel $model */
        $model = $this->getModel();

        $this->state = $model->getState();

        // Have to stop it earlier, because on cancel task for a new module we do not have an ID, and Model doing redirect on getItem()
        if ($this->getLayout() === 'modalreturn' && !$this->state->get('module.id')) {
            parent::display($tpl);

            return;
        }

        $this->form  = $model->getForm();
        $this->item  = $model->getItem();
        $this->canDo = ContentHelper::getActions('com_modules', 'module', $this->item->id);

        if ($this->getLayout() === 'modalreturn') {
            parent::display($tpl);

            return;
        }

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if ($this->getLayout() !== 'modal') {
            $this->addToolbar();
        } else {
            $this->addModalToolbar();
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
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $user       = $this->getCurrentUser();
        $isNew      = ($this->item->id == 0);
        $checkedOut = !(\is_null($this->item->checked_out) || $this->item->checked_out == $user->id);
        $canDo      = $this->canDo;
        $toolbar    = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::sprintf('COM_MODULES_MANAGER_MODULE', Text::_($this->item->module)), 'cube module');

        // For new records, check the create permission.
        if ($isNew && $canDo->get('core.create')) {
            $toolbar->apply('module.apply');

            $saveGroup = $toolbar->dropdownButton('save-group');

            $saveGroup->configure(
                function (Toolbar $childBar) {
                    $childBar->save('module.save');
                    $childBar->save2new('module.save2new');
                }
            );

            $toolbar->cancel('module.cancel', 'JTOOLBAR_CANCEL');
        } else {
            // Can't save the record if it's checked out.
            if (!$checkedOut && $canDo->get('core.edit')) {
                $toolbar->apply('module.apply');
            }

            $saveGroup = $toolbar->dropdownButton('save-group');

            $saveGroup->configure(
                function (Toolbar $childBar) use ($checkedOut, $canDo) {
                    // Can't save the record if it's checked out. Since it's an existing record, check the edit permission.
                    if (!$checkedOut && $canDo->get('core.edit')) {
                        $childBar->save('module.save');

                        // We can save this record, but check the create permission to see if we can return to make a new one.
                        if ($canDo->get('core.create')) {
                            $childBar->save2new('module.save2new');
                        }
                    }

                    // If checked out, we can still save
                    if ($canDo->get('core.create')) {
                        $childBar->save2copy('module.save2copy');
                    }
                }
            );

            $toolbar->cancel('module.cancel');
        }

        // Get the help information for the menu item.
        $lang = $this->getLanguage();

        /** @var ModuleModel $model */
        $model = $this->getModel();
        $help  = $model->getHelp();

        if ($lang->hasKey($help->url)) {
            $debug = $lang->setDebug(false);
            $url   = Text::_($help->url);
            $lang->setDebug($debug);
        } else {
            $url = null;
        }

        $toolbar->inlinehelp();
        $toolbar->help($help->key, false, $url);
    }

    /**
     * Add the modal toolbar.
     *
     * @return  void
     *
     * @since   5.1.0
     *
     * @throws  \Exception
     */
    protected function addModalToolbar()
    {
        $isNew   = ($this->item->id == 0);
        $toolbar = $this->getDocument()->getToolbar();
        $canDo   = $this->canDo;

        ToolbarHelper::title(Text::sprintf('COM_MODULES_MANAGER_MODULE', Text::_($this->item->module)), 'cube module');

        $canCreate = $isNew && $canDo->get('core.create');
        $canEdit   = $canDo->get('core.edit');

        // For new records, check the create permission.
        if ($canCreate || $canEdit) {
            $toolbar->apply('module.apply');
            $toolbar->save('module.save');
        }

        $toolbar->cancel('module.cancel');

        $toolbar->inlinehelp();
    }
}
