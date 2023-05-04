<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\View\Step;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit an Step
 *
 * @since 4.3.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The \JForm object
     *
     * @var \Joomla\CMS\Form\Form
     */
    protected $form;

    /**
     * The active item
     *
     * @var object
     */
    protected $item;

    /**
     * The model state
     *
     * @var object
     */
    protected $state;

    /**
     * The actions the user is authorised to perform
     *
     * @var \Joomla\CMS\Object\CMSObject
     */
    protected $canDo;

    /**
     * Execute and display a template script.
     *
     * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws \Exception
     * @since  4.3.0
     */
    public function display($tpl = null)
    {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return void
     *
     * @throws \Exception
     * @since  4.3.0
     */
    protected function addToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $user       = $this->getCurrentUser();
        $userId     = $user->id;
        $isNew      = empty($this->item->id);

        $canDo = ContentHelper::getActions('com_guidedtours');

        ToolbarHelper::title(Text::_($isNew ? 'COM_GUIDEDTOURS_MANAGER_STEP_NEW' : 'COM_GUIDEDTOURS_MANAGER_STEP_EDIT'), 'map-signs');

        $toolbarButtons = [];

        if ($isNew) {
            // For new records, check the create permission.
            if ($canDo->get('core.create')) {
                ToolbarHelper::apply('step.apply');
                $toolbarButtons = [['save', 'step.save'], ['save2new', 'step.save2new']];
            }

            ToolbarHelper::saveGroup(
                $toolbarButtons,
                'btn-success'
            );

            ToolbarHelper::cancel(
                'step.cancel'
            );
        } else {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

            if ($itemEditable) {
                ToolbarHelper::apply('step.apply');
                $toolbarButtons = [['save', 'step.save']];

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($canDo->get('core.create')) {
                    $toolbarButtons[] = ['save2new', 'step.save2new'];
                    $toolbarButtons[] = ['save2copy', 'step.save2copy'];
                }

                ToolbarHelper::saveGroup(
                    $toolbarButtons,
                    'btn-success'
                );

                ToolbarHelper::cancel(
                    'step.cancel',
                    'JTOOLBAR_CLOSE'
                );
            }
        }

        ToolbarHelper::divider();
        $inlinehelp  = (string) $this->form->getXml()->config->inlinehelp['button'] == 'show' ?: false;
        $targetClass = (string) $this->form->getXml()->config->inlinehelp['targetclass'] ?: 'hide-aware-inline-help';

        if ($inlinehelp) {
            ToolbarHelper::inlinehelp($targetClass);
        }
        ToolbarHelper::help('Guided_Tours:_New_or_Edit_Step');
    }
}
