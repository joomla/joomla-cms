<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Controller;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Event\Model;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Profile controller class for Users.
 *
 * @since  1.6
 */
class ProfileController extends BaseController
{
    /**
     * Method to check out a user for editing and redirect to the edit form.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public function edit()
    {
        $app         = $this->app;
        $user        = $this->app->getIdentity();
        $loginUserId = (int) $user->id;

        // Get the current user id.
        $userId     = $this->input->getInt('user_id');

        // Check if the user is trying to edit another users profile.
        if ($userId != $loginUserId) {
            $app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            $app->setHeader('status', 403, true);

            return false;
        }

        $cookieLogin = $user->get('cookieLogin');

        // Check if the user logged in with a cookie
        if (!empty($cookieLogin)) {
            // If so, the user must login to edit the password and other data.
            $app->enqueueMessage(Text::_('JGLOBAL_REMEMBER_MUST_LOGIN'), 'message');
            $this->setRedirect(Route::_('index.php?option=com_users&view=login', false));

            return false;
        }

        // Set the user id for the user to edit in the session.
        $app->setUserState('com_users.edit.profile.id', $userId);

        // Redirect to the edit screen.
        $this->setRedirect(Route::_('index.php?option=com_users&view=profile&layout=edit', false));

        return true;
    }

    /**
     * Method to save a user's profile data.
     *
     * @return  void|boolean
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function save()
    {
        // Check for request forgeries.
        $this->checkToken();

        $app    = $this->app;

        /** @var \Joomla\Component\Users\Site\Model\ProfileModel $model */
        $model  = $this->getModel('Profile', 'Site');
        $user   = $this->app->getIdentity();
        $userId = (int) $user->id;

        // Get the user data.
        $requestData = $app->getInput()->post->get('jform', [], 'array');

        // Force the ID to this user.
        $requestData['id'] = $userId;

        // Validate the posted data.
        $form = $model->getForm();

        if (!$form) {
            throw new \Exception($model->getError(), 500);
        }

        // Send an object which can be modified through the plugin event
        $objData = (object) $requestData;
        $this->getDispatcher()->dispatch(
            'onContentNormaliseRequestData',
            new Model\NormaliseRequestDataEvent('onContentNormaliseRequestData', [
                'context' => 'com_users.user',
                'data'    => $objData,
                'subject' => $form,
            ])
        );
        $requestData = (array) $objData;

        // Validate the posted data.
        $data = $model->validate($form, $requestData);

        // Check for errors.
        if ($data === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = \count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof \Exception) {
                    $app->enqueueMessage($errors[$i]->getMessage(), CMSWebApplicationInterface::MSG_ERROR);
                } else {
                    $app->enqueueMessage($errors[$i], CMSWebApplicationInterface::MSG_ERROR);
                }
            }

            // Unset the passwords.
            unset($requestData['password1'], $requestData['password2']);

            // Save the data in the session.
            $app->setUserState('com_users.edit.profile.data', $requestData);

            // Redirect back to the edit screen.
            $userId = (int) $app->getUserState('com_users.edit.profile.id');
            $this->setRedirect(Route::_('index.php?option=com_users&view=profile&layout=edit&user_id=' . $userId, false));

            return false;
        }

        // Attempt to save the data.
        $return = $model->save($data);

        // Check for errors.
        if ($return === false) {
            // Save the data in the session.
            $app->setUserState('com_users.edit.profile.data', $data);

            // Redirect back to the edit screen.
            $userId = (int) $app->getUserState('com_users.edit.profile.id');
            $this->setMessage(Text::sprintf('COM_USERS_PROFILE_SAVE_FAILED', $model->getError()), 'warning');
            $this->setRedirect(Route::_('index.php?option=com_users&view=profile&layout=edit&user_id=' . $userId, false));

            return false;
        }

        // Redirect the user and adjust session state based on the chosen task.
        switch ($this->getTask()) {
            case 'apply':
                // Check out the profile.
                $app->setUserState('com_users.edit.profile.id', $return);

                // Redirect back to the edit screen.
                $this->setMessage(Text::_('COM_USERS_PROFILE_SAVE_SUCCESS'));

                $redirect = $app->getUserState('com_users.edit.profile.redirect', '');

                // Don't redirect to an external URL.
                if (!Uri::isInternal($redirect)) {
                    $redirect = null;
                }

                if (!$redirect) {
                    $redirect = 'index.php?option=com_users&view=profile&layout=edit&hidemainmenu=1';
                }

                $this->setRedirect(Route::_($redirect, false));
                break;

            default:
                // Clear the profile id from the session.
                $app->setUserState('com_users.edit.profile.id', null);

                $redirect = $app->getUserState('com_users.edit.profile.redirect', '');

                // Don't redirect to an external URL.
                if (!Uri::isInternal($redirect)) {
                    $redirect = null;
                }

                if (!$redirect) {
                    $redirect = 'index.php?option=com_users&view=profile&user_id=' . $return;
                }

                // Redirect to the list screen.
                $this->setMessage(Text::_('COM_USERS_PROFILE_SAVE_SUCCESS'));
                $this->setRedirect(Route::_($redirect, false));
                break;
        }

        // Flush the data from the session.
        $app->setUserState('com_users.edit.profile.data', null);
    }

    /**
     * Method to cancel an edit.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function cancel()
    {
        // Check for request forgeries.
        $this->checkToken();

        // Flush the data from the session.
        $this->app->setUserState('com_users.edit.profile', null);

        // Redirect to user profile.
        $this->setRedirect(Route::_('index.php?option=com_users&view=profile', false));
    }
}
