<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\View\Tour;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Guidedtours\Administrator\Model\TourModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit an tour.
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
     * @var \Joomla\Registry\Registry
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
        /** @var TourModel $model */
        $model = $this->getModel();

        $this->form  = $model->getItem();
        $this->item  = $model->getItem();
        $this->state = $model->getState();

        if (\count($errors = $model->getErrors())) {
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
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $user       = $this->getCurrentUser();
        $userId     = $user->id;
        $isNew      = empty($this->item->id);

        $canDo = ContentHelper::getActions('com_guidedtours');

        ToolbarHelper::title(Text::_($isNew ? 'COM_GUIDEDTOURS_MANAGER_TOUR_NEW' : 'COM_GUIDEDTOURS_MANAGER_TOUR_EDIT'), 'map-signs');

        $toolbarButtons = [];

        if ($isNew) {
            // For new records, check the create permission.
            if ($canDo->get('core.create')) {
                ToolbarHelper::apply('tour.apply');
                $toolbarButtons = [['save', 'tour.save'], ['save2new', 'tour.save2new']];
            }

            ToolbarHelper::saveGroup(
                $toolbarButtons,
                'btn-success'
            );

            ToolbarHelper::cancel(
                'tour.cancel'
            );
        } else {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

            if ($itemEditable) {
                ToolbarHelper::apply('tour.apply');
                $toolbarButtons = [['save', 'tour.save']];

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($canDo->get('core.create')) {
                    $toolbarButtons[] = ['save2new', 'tour.save2new'];
                    $toolbarButtons[] = ['save2copy', 'tour.save2copy'];
                }

                ToolbarHelper::saveGroup(
                    $toolbarButtons,
                    'btn-success'
                );

                ToolbarHelper::cancel(
                    'tour.cancel',
                    'JTOOLBAR_CLOSE'
                );
            }
        }

        ToolbarHelper::divider();

        $inlinehelp  = (string) $this->form->getXml()->config->inlinehelp['button'] === 'show';
        $targetClass = (string) $this->form->getXml()->config->inlinehelp['targetclass'] ?: 'hide-aware-inline-help';

        if ($inlinehelp) {
            ToolbarHelper::inlinehelp($targetClass);
        }
        ToolbarHelper::help('Guided_Tours:_New_or_Edit_Tour');
    }
}
