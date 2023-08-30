<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\View\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Field View
 *
 * @since  3.7.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var     \Joomla\CMS\Form\Form
     *
     * @since   3.7.0
     */
    protected $form;

    /**
     * @var     CMSObject
     *
     * @since   3.7.0
     */
    protected $item;

    /**
     * @var     CMSObject
     *
     * @since   3.7.0
     */
    protected $state;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @see     HtmlView::loadTemplate()
     *
     * @since   3.7.0
     */
    public function display($tpl = null)
    {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        $this->canDo = ContentHelper::getActions($this->state->get('field.component'), 'field', $this->item->id);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Adds the toolbar.
     *
     * @return  void
     *
     * @since   3.7.0
     */
    protected function addToolbar()
    {
        $component = $this->state->get('field.component');
        $section   = $this->state->get('field.section');
        $userId    = $this->getCurrentUser()->get('id');
        $canDo     = $this->canDo;
        $toolbar   = Toolbar::getInstance();

        $isNew      = ($this->item->id == 0);
        $checkedOut = !(is_null($this->item->checked_out) || $this->item->checked_out == $userId);

        // Avoid nonsense situation.
        if ($component == 'com_fields') {
            return;
        }

        // Load component language file
        $lang = Factory::getLanguage();
        $lang->load($component, JPATH_ADMINISTRATOR)
        || $lang->load($component, Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component));

        $title = Text::sprintf('COM_FIELDS_VIEW_FIELD_' . ($isNew ? 'ADD' : 'EDIT') . '_TITLE', Text::_(strtoupper($component)));

        // Prepare the toolbar.
        ToolbarHelper::title(
            $title,
            'puzzle field-' . ($isNew ? 'add' : 'edit') . ' ' . substr($component, 4) . ($section ? "-$section" : '') . '-field-' .
            ($isNew ? 'add' : 'edit')
        );

        // For new records, check the create permission.
        if ($isNew) {
            $toolbar->apply('field.apply');
            $saveGroup = $toolbar->dropdownButton('save-group');
            $saveGroup->configure(
                function (Toolbar $childBar) {
                    $childBar->save('field.save');
                    $childBar->save2new('field.save2new');
                }
            );
            $toolbar->cancel('field.cancel');
        } else {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

            // Can't save the record if it's checked out and editable
            if (!$checkedOut && $itemEditable) {
                $toolbar->apply('field.apply');
            }

            $saveGroup = $toolbar->dropdownButton('save-group');
            $saveGroup->configure(
                function (Toolbar $childBar) use ($checkedOut, $itemEditable, $canDo) {
                    if (!$checkedOut && $itemEditable) {
                        $childBar->save('field.save');

                        // We can save this record, but check the create permission to see if we can return to make a new one.
                        if ($canDo->get('core.create')) {
                            $childBar->save2new('field.save2new');
                        }
                    }

                    // If an existing item, can save to a copy.
                    if ($canDo->get('core.create')) {
                        $childBar->save2copy('field.save2copy');
                    }
                }
            );

            $toolbar->cancel('field.cancel');
        }

        $toolbar->help('Fields:_Edit');
    }
}
