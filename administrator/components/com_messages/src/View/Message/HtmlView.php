<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\View\Message;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\User\UserFactoryAwareInterface;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Component\Messages\Administrator\Model\MessageModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Messages component
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView implements UserFactoryAwareInterface
{
    use UserFactoryAwareTrait;

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
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        /** @var MessageModel $model */
        $model = $this->getModel();

        $this->form  = $model->getForm();
        $this->item  = $model->getItem();
        $this->state = $model->getState();

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        if ($this->getLayout() !== 'edit' && empty($this->item->message_id)) {
            throw new GenericDataException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

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
        $app     = Factory::getApplication();
        $toolbar = $this->getDocument()->getToolbar();

        if ($this->getLayout() == 'edit') {
            $app->getInput()->set('hidemainmenu', true);
            ToolbarHelper::title(Text::_('COM_MESSAGES_WRITE_PRIVATE_MESSAGE'), 'envelope-open-text new-privatemessage');
            $toolbar->standardButton('save', 'COM_MESSAGES_TOOLBAR_SEND', 'message.save')
                ->icon('icon-envelope')
                ->listCheck(false);
            $toolbar->cancel('message.cancel');
            $toolbar->help('Private_Messages:_Write');
        } else {
            ToolbarHelper::title(Text::_('COM_MESSAGES_VIEW_PRIVATE_MESSAGE'), 'envelope inbox');
            $sender = $this->getUserFactory()->loadUserById($this->item->user_id_from);

            if (
                $sender->id !== $app->getIdentity()->id && ($sender->authorise('core.admin')
                || $sender->authorise('core.manage', 'com_messages') && $sender->authorise('core.login.admin'))
                && $app->getIdentity()->authorise('core.manage', 'com_users')
            ) {
                $toolbar->standardButton('reply', 'COM_MESSAGES_TOOLBAR_REPLY', 'message.reply')
                    ->icon('icon-redo')
                    ->listCheck(false);
            }

            $toolbar->cancel('message.cancel');
            $toolbar->help('Private_Messages:_Read');
        }
    }
}
