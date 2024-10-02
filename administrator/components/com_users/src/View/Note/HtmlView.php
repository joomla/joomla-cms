<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\View\Note;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Users\Administrator\Model\NoteModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * User note edit view
 *
 * @since  2.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The edit form.
     *
     * @var    \Joomla\CMS\Form\Form
     *
     * @since  2.5
     */
    protected $form;

    /**
     * The item data.
     *
     * @var    object
     * @since  2.5
     */
    protected $item;

    /**
     * The model state.
     *
     * @var    \Joomla\Registry\Registry
     * @since  2.5
     */
    protected $state;

    /**
     * Override the display method for the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception
     */
    public function display($tpl = null)
    {
        /** @var NoteModel $model */
        $model = $this->getModel();

        // Initialise view variables.
        $this->state = $model->getState();
        $this->item  = $model->getItem();
        $this->form  = $model->getForm();

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        parent::display($tpl);
        $this->addToolbar();
    }

    /**
     * Display the toolbar.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception
     */
    protected function addToolbar()
    {
        $input = Factory::getApplication()->getInput();
        $input->set('hidemainmenu', 1);

        $user       = $this->getCurrentUser();
        $isNew      = ($this->item->id == 0);
        $checkedOut = !(\is_null($this->item->checked_out) || $this->item->checked_out == $user->id);
        $toolbar    = $this->getDocument()->getToolbar();

        // Since we don't track these assets at the item level, use the category id.
        $canDo = ContentHelper::getActions('com_users', 'category', $this->item->catid);

        ToolbarHelper::title(Text::_('COM_USERS_NOTES'), 'users user');

        // If not checked out, can save the item.
        if (!$checkedOut && ($canDo->get('core.edit') || \count($user->getAuthorisedCategories('com_users', 'core.create')))) {
            $toolbar->apply('note.apply');
        }

        $saveGroup = $toolbar->dropdownButton('save-group');

        $saveGroup->configure(
            function (Toolbar $childBar) use ($checkedOut, $canDo, $user, $isNew) {
                // If not checked out, can save the item.
                if (!$checkedOut && ($canDo->get('core.edit') || \count($user->getAuthorisedCategories('com_users', 'core.create')))) {
                    $childBar->save('note.save');
                }

                if (!$checkedOut && \count($user->getAuthorisedCategories('com_users', 'core.create'))) {
                    $childBar->save2new('note.save2new');
                }

                // If an existing item, can save to a copy.
                if (!$isNew && (\count($user->getAuthorisedCategories('com_users', 'core.create')) > 0)) {
                    $childBar->save2copy('note.save2copy');
                }
            }
        );

        if (empty($this->item->id)) {
            $toolbar->cancel('note.cancel', 'JTOOLBAR_CANCEL');
        } else {
            $toolbar->cancel('note.cancel');

            if (ComponentHelper::isEnabled('com_contenthistory') && $this->state->params->get('save_history', 0) && $canDo->get('core.edit')) {
                $toolbar->versions('com_users.note', $this->item->id);
            }
        }

        $toolbar->divider();
        $toolbar->help('User_Notes:_New_or_Edit');
    }
}
