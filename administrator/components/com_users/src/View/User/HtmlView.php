<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\View\User;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\Helper\Mfa;

/**
 * User view class.
 *
 * @since  1.5
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
     * Gets the available groups
     *
     * @var  array
     */
    protected $grouplist;

    /**
     * The groups this user is assigned to
     *
     * @var     array
     * @since   1.6
     */
    protected $groups;

    /**
     * The model state
     *
     * @var  CMSObject
     */
    protected $state;

    /**
     * The Multi-factor Authentication configuration interface for the user.
     *
     * @var   string|null
     * @since 4.2.0
     */
    protected $mfaConfigurationUI;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   1.5
     */
    public function display($tpl = null)
    {
        // If no item found, dont show the edit screen, redirect with message
        if (false === $this->item = $this->get('Item')) {
            $app = Factory::getApplication();
            $app->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_NOT_EXIST'), 'error');
            $app->redirect('index.php?option=com_users&view=users');
        }

        $this->form  = $this->get('Form');
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Prevent user from modifying own group(s)
        $user = Factory::getApplication()->getIdentity();

        if ((int) $user->id != (int) $this->item->id || $user->authorise('core.admin')) {
            $this->grouplist = $this->get('Groups');
            $this->groups    = $this->get('AssignedGroups');
        }

        $this->form->setValue('password', null);
        $this->form->setValue('password2', null);

        /** @var User $userBeingEdited */
        $userBeingEdited = Factory::getContainer()
            ->get(UserFactoryInterface::class)
            ->loadUserById($this->item->id);

        if ($this->item->id > 0 && (int) $userBeingEdited->id == (int) $this->item->id) {
            try {
                $this->mfaConfigurationUI = Mfa::canShowConfigurationInterface($userBeingEdited)
                    ? Mfa::getConfigurationInterface($userBeingEdited)
                    : '';
            } catch (\Exception $e) {
                // In case something goes really wrong with the plugins; prevents hard breaks.
                $this->mfaConfigurationUI = null;
            }
        }

        parent::display($tpl);

        $this->addToolbar();
    }

    /**
     * Add the page title and toolbar.
     *
     * @return void
     *
     * @since   1.6
     * @throws  \Exception
     */
    protected function addToolbar()
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $user      = Factory::getApplication()->getIdentity();
        $canDo     = ContentHelper::getActions('com_users');
        $isNew     = ($this->item->id == 0);
        $isProfile = $this->item->id == $user->id;

        ToolbarHelper::title(
            Text::_(
                $isNew ? 'COM_USERS_VIEW_NEW_USER_TITLE' : ($isProfile ? 'COM_USERS_VIEW_EDIT_PROFILE_TITLE' : 'COM_USERS_VIEW_EDIT_USER_TITLE')
            ),
            'user ' . ($isNew ? 'user-add' : ($isProfile ? 'user-profile' : 'user-edit'))
        );

        $toolbarButtons = [];

        if ($canDo->get('core.edit') || $canDo->get('core.create') || $isProfile) {
            ToolbarHelper::apply('user.apply');
            $toolbarButtons[] = ['save', 'user.save'];
        }

        if ($canDo->get('core.create') && $canDo->get('core.manage')) {
            $toolbarButtons[] = ['save2new', 'user.save2new'];
        }

        ToolbarHelper::saveGroup(
            $toolbarButtons,
            'btn-success'
        );

        if (empty($this->item->id)) {
            ToolbarHelper::cancel('user.cancel');
        } else {
            ToolbarHelper::cancel('user.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('Users:_Edit_Profile');
    }
}
