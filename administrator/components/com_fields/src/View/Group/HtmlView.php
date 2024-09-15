<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\View\Group;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Group View
 *
 * @since  3.7.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * @var    \Joomla\CMS\Form\Form
     *
     * @since  3.7.0
     */
    protected $form;

    /**
     * @var    \stdClass
     *
     * @since  3.7.0
     */
    protected $item;

    /**
     * @var    \Joomla\Registry\Registry
     *
     * @since  3.7.0
     */
    protected $state;

    /**
     * The actions the user is authorised to perform
     *
     * @var    \Joomla\Registry\Registry
     *
     * @since  3.7.0
     */
    protected $canDo;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function display($tpl = null)
    {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        $component = '';
        $parts     = FieldsHelper::extract($this->state->get('filter.context'));

        if ($parts) {
            $component = $parts[0];
        }

        $this->canDo = ContentHelper::getActions($component, 'fieldgroup', $this->item->id);

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

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
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $component = '';
        $parts     = FieldsHelper::extract($this->state->get('filter.context'));
        $toolbar   = $this->getDocument()->getToolbar();

        if ($parts) {
            $component = $parts[0];
        }

        $userId    = $this->getCurrentUser()->id;
        $canDo     = $this->canDo;

        $isNew      = ($this->item->id == 0);
        $checkedOut = !(\is_null($this->item->checked_out) || $this->item->checked_out == $userId);

        // Avoid nonsense situation.
        if ($component == 'com_fields') {
            return;
        }

        // Load component language file
        $lang = $this->getLanguage();
        $lang->load($component, JPATH_ADMINISTRATOR)
        || $lang->load($component, Path::clean(JPATH_ADMINISTRATOR . '/components/' . $component));

        $title = Text::sprintf('COM_FIELDS_VIEW_GROUP_' . ($isNew ? 'ADD' : 'EDIT') . '_TITLE', Text::_(strtoupper($component)));

        // Prepare the toolbar.
        ToolbarHelper::title(
            $title,
            'puzzle-piece field-' . ($isNew ? 'add' : 'edit') . ' ' . substr($component, 4) . '-group-' .
            ($isNew ? 'add' : 'edit')
        );

        // For new records, check the create permission.
        if ($isNew) {
            $toolbar->apply('group.apply');
            $saveGroup = $toolbar->dropdownButton('save-group');

            $saveGroup->configure(
                function (Toolbar $childBar) {
                    $childBar->save('group.save');
                    $childBar->save2new('group.save2new');
                }
            );

            $toolbar->cancel('group.cancel', 'JTOOLBAR_CANCEL');
        } else {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

            // Can't save the record if it's checked out and editable
            if (!$checkedOut && $itemEditable) {
                $toolbar->apply('group.apply');
            }

            $saveGroup = $toolbar->dropdownButton('save-group');
            $saveGroup->configure(
                function (Toolbar $childBar) use ($checkedOut, $itemEditable, $canDo) {
                    if (!$checkedOut && $itemEditable) {
                        $childBar->save('group.save');

                        // We can save this record, but check the create permission to see if we can return to make a new one.
                        if ($canDo->get('core.create')) {
                            $childBar->save2new('group.save2new');
                        }
                    }

                    // If an existing item, can save to a copy.
                    if ($canDo->get('core.create')) {
                        $childBar->save2copy('group.save2copy');
                    }
                }
            );

            $toolbar->cancel('group.cancel');
        }

        $toolbar->help('Field_Groups:_Edit');
    }
}
