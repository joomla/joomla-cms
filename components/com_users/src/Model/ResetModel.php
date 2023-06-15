<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Model;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Reset model class for Users.
 *
 * @since  1.5
 */
class ResetModel extends FormModel
{
    /**
     * Method to get the password reset request form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param   array    $data      An optional array of data for the form to interrogate.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form  A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_users.reset_request', 'reset_request', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the password reset complete form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form    A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getResetCompleteForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_users.reset_complete', 'reset_complete', $options = ['control' => 'jform']);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the password reset confirm form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form  A Form object on success, false on failure
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function getResetConfirmForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_users.reset_confirm', 'reset_confirm', $options = ['control' => 'jform']);

        if (empty($form)) {
            return false;
        } else {
            $form->setValue('token', '', Factory::getApplication()->getInput()->get('token'));
        }

        return $form;
    }

    /**
     * Override preprocessForm to load the user plugin group instead of content.
     *
     * @param   Form    $form   A Form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @throws  \Exception if there is an error in the form event.
     *
     * @since   1.6
     */
    protected function preprocessForm(Form $form, $data, $group = 'user')
    {
        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     * @throws  \Exception
     */
    protected function populateState()
    {
        // Get the application object.
        $params = Factory::getApplication()->getParams('com_users');

        // Load the parameters.
        $this->setState('params', $params);
    }

    /**
     * Save the new password after reset is done
     *
     * @param   array  $data  The data expected for the form.
     *
     * @return  mixed  \Exception | boolean
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function processResetComplete($data)
    {
        // Get the form.
        $form = $this->getResetCompleteForm();

        // Check for an error.
        if ($form instanceof \Exception) {
            return $form;
        }

        // Filter and validate the form data.
        $data   = $form->filter($data);
        $return = $form->validate($data);

        // Check for an error.
        if ($return instanceof \Exception) {
            return $return;
        }

        // Check the validation results.
        if ($return === false) {
            // Get the validation messages from the form.
            foreach ($form->getErrors() as $formError) {
                $this->setError($formError->getMessage());
            }

            return false;
        }

        // Get the token and user id from the confirmation process.
        $app    = Factory::getApplication();
        $token  = $app->getUserState('com_users.reset.token', null);
        $userId = $app->getUserState('com_users.reset.user', null);

        // Check the token and user id.
        if (empty($token) || empty($userId)) {
            return new \Exception(Text::_('COM_USERS_RESET_COMPLETE_TOKENS_MISSING'), 403);
        }

        // Get the user object.
        $user = User::getInstance($userId);

        $event = AbstractEvent::create(
            'onUserBeforeResetComplete',
            [
                'subject' => $user,
            ]
        );
        $app->getDispatcher()->dispatch($event->getName(), $event);

        // Check for a user and that the tokens match.
        if (empty($user) || $user->activation !== $token) {
            $this->setError(Text::_('COM_USERS_USER_NOT_FOUND'));

            return false;
        }

        // Make sure the user isn't blocked.
        if ($user->block) {
            $this->setError(Text::_('COM_USERS_USER_BLOCKED'));

            return false;
        }

        // Check if the user is reusing the current password if required to reset their password
        if ($user->requireReset == 1 && UserHelper::verifyPassword($data['password1'], $user->password)) {
            $this->setError(Text::_('JLIB_USER_ERROR_CANNOT_REUSE_PASSWORD'));

            return false;
        }

        // Prepare user data.
        $data['password']   = $data['password1'];
        $data['activation'] = '';

        // Update the user object.
        if (!$user->bind($data)) {
            return new \Exception($user->getError(), 500);
        }

        // Save the user to the database.
        if (!$user->save(true)) {
            return new \Exception(Text::sprintf('COM_USERS_USER_SAVE_FAILED', $user->getError()), 500);
        }

        // Destroy all active sessions for the user
        UserHelper::destroyUserSessions($user->id);

        // Flush the user data from the session.
        $app->setUserState('com_users.reset.token', null);
        $app->setUserState('com_users.reset.user', null);

        $event = AbstractEvent::create(
            'onUserAfterResetComplete',
            [
                'subject' => $user,
            ]
        );
        $app->getDispatcher()->dispatch($event->getName(), $event);

        return true;
    }

    /**
     * Receive the reset password request
     *
     * @param   array  $data  The data expected for the form.
     *
     * @return  mixed  \Exception | boolean
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function processResetConfirm($data)
    {
        // Get the form.
        $form = $this->getResetConfirmForm();

        // Check for an error.
        if ($form instanceof \Exception) {
            return $form;
        }

        // Filter and validate the form data.
        $data   = $form->filter($data);
        $return = $form->validate($data);

        // Check for an error.
        if ($return instanceof \Exception) {
            return $return;
        }

        // Check the validation results.
        if ($return === false) {
            // Get the validation messages from the form.
            foreach ($form->getErrors() as $formError) {
                $this->setError($formError->getMessage());
            }

            return false;
        }

        // Find the user id for the given token.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['activation', 'id', 'block']))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('username') . ' = :username')
            ->bind(':username', $data['username']);

        // Get the user id.
        $db->setQuery($query);

        try {
            $user = $db->loadObject();
        } catch (\RuntimeException $e) {
            return new \Exception(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 500);
        }

        // Check for a user.
        if (empty($user)) {
            $this->setError(Text::_('COM_USERS_USER_NOT_FOUND'));

            return false;
        }

        if (!$user->activation) {
            $this->setError(Text::_('COM_USERS_USER_NOT_FOUND'));

            return false;
        }

        // Verify the token
        if (!UserHelper::verifyPassword($data['token'], $user->activation)) {
            $this->setError(Text::_('COM_USERS_USER_NOT_FOUND'));

            return false;
        }

        // Make sure the user isn't blocked.
        if ($user->block) {
            $this->setError(Text::_('COM_USERS_USER_BLOCKED'));

            return false;
        }

        // Push the user data into the session.
        $app = Factory::getApplication();
        $app->setUserState('com_users.reset.token', $user->activation);
        $app->setUserState('com_users.reset.user', $user->id);

        return true;
    }

    /**
     * Method to start the password reset process.
     *
     * @param   array  $data  The data expected for the form.
     *
     * @return  mixed  \Exception | boolean
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function processResetRequest($data)
    {
        $app = Factory::getApplication();

        // Get the form.
        $form = $this->getForm();

        $data['email'] = PunycodeHelper::emailToPunycode($data['email']);

        // Check for an error.
        if ($form instanceof \Exception) {
            return $form;
        }

        // Filter and validate the form data.
        $data   = $form->filter($data);
        $return = $form->validate($data);

        // Check for an error.
        if ($return instanceof \Exception) {
            return $return;
        }

        // Check the validation results.
        if ($return === false) {
            // Get the validation messages from the form.
            foreach ($form->getErrors() as $formError) {
                $this->setError($formError->getMessage());
            }

            return false;
        }

        // Find the user id for the given email address.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__users'))
            ->where('LOWER(' . $db->quoteName('email') . ') = LOWER(:email)')
            ->bind(':email', $data['email']);

        // Get the user object.
        $db->setQuery($query);

        try {
            $userId = $db->loadResult();
        } catch (\RuntimeException $e) {
            $this->setError(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()));

            return false;
        }

        // Check for a user.
        if (empty($userId)) {
            $this->setError(Text::_('COM_USERS_INVALID_EMAIL'));

            return false;
        }

        // Get the user object.
        $user = User::getInstance($userId);

        // Make sure the user isn't blocked.
        if ($user->block) {
            $this->setError(Text::_('COM_USERS_USER_BLOCKED'));

            return false;
        }

        // Make sure the user isn't a Super Admin.
        if ($user->authorise('core.admin')) {
            $this->setError(Text::_('COM_USERS_REMIND_SUPERADMIN_ERROR'));

            return false;
        }

        // Make sure the user has not exceeded the reset limit
        if (!$this->checkResetLimit($user)) {
            $resetLimit = (int) Factory::getApplication()->getParams()->get('reset_time');
            $this->setError(Text::plural('COM_USERS_REMIND_LIMIT_ERROR_N_HOURS', $resetLimit));

            return false;
        }

        // Set the confirmation token.
        $token       = ApplicationHelper::getHash(UserHelper::genRandomPassword());
        $hashedToken = UserHelper::hashPassword($token);

        $user->activation = $hashedToken;

        $event = AbstractEvent::create(
            'onUserBeforeResetRequest',
            [
                'subject' => $user,
            ]
        );
        $app->getDispatcher()->dispatch($event->getName(), $event);

        // Save the user to the database.
        if (!$user->save(true)) {
            return new \Exception(Text::sprintf('COM_USERS_USER_SAVE_FAILED', $user->getError()), 500);
        }

        // Assemble the password reset confirmation link.
        $mode = $app->get('force_ssl', 0) == 2 ? 1 : (-1);
        $link = 'index.php?option=com_users&view=reset&layout=confirm&token=' . $token;

        // Put together the email template data.
        $data              = $user->getProperties();
        $data['sitename']  = $app->get('sitename');
        $data['link_text'] = Route::_($link, false, $mode);
        $data['link_html'] = Route::_($link, true, $mode);
        $data['token']     = $token;

        $mailer = new MailTemplate('com_users.password_reset', $app->getLanguage()->getTag());
        $mailer->addTemplateData($data);
        $mailer->addRecipient($user->email, $user->name);

        // Try to send the password reset request email.
        try {
            $return = $mailer->send();
        } catch (\Exception $exception) {
            try {
                Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');

                $return = false;
            } catch (\RuntimeException $exception) {
                $app->enqueueMessage(Text::_($exception->errorMessage()), 'warning');

                $return = false;
            }
        }

        // Check for an error.
        if ($return !== true) {
            return new \Exception(Text::_('COM_USERS_MAIL_FAILED'), 500);
        }

        $event = AbstractEvent::create(
            'onUserAfterResetRequest',
            [
                'subject' => $user,
            ]
        );
        $app->getDispatcher()->dispatch($event->getName(), $event);

        return true;
    }

    /**
     * Method to check if user reset limit has been exceeded within the allowed time period.
     *
     * @param   User  $user  User doing the password reset
     *
     * @return  boolean true if user can do the reset, false if limit exceeded
     *
     * @since    2.5
     * @throws  \Exception
     */
    public function checkResetLimit($user)
    {
        $params     = Factory::getApplication()->getParams();
        $maxCount   = (int) $params->get('reset_count');
        $resetHours = (int) $params->get('reset_time');
        $result     = true;

        $lastResetTime       = strtotime($user->lastResetTime) ?: 0;
        $hoursSinceLastReset = (strtotime(Factory::getDate()->toSql()) - $lastResetTime) / 3600;

        if ($hoursSinceLastReset > $resetHours) {
            // If it's been long enough, start a new reset count
            $user->lastResetTime = Factory::getDate()->toSql();
            $user->resetCount    = 1;
        } elseif ($user->resetCount < $maxCount) {
            // If we are under the max count, just increment the counter
            ++$user->resetCount;
        } else {
            // At this point, we know we have exceeded the maximum resets for the time period
            $result = false;
        }

        return $result;
    }
}
