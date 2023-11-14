<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Model;

use Joomla\CMS\Event\User\AfterRemindEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Remind model class for Users.
 *
 * @since  1.5
 */
class RemindModel extends FormModel
{
    /**
     * Method to get the username remind request form.
     *
     * @param   array    $data      An optional array of data for the form to interrogate.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|bool A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_users.remind', 'remind', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
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
        parent::preprocessForm($form, $data, 'user');
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     *
     * @throws  \Exception
     */
    protected function populateState()
    {
        // Get the application object.
        $app    = Factory::getApplication();
        $params = $app->getParams('com_users');

        // Load the parameters.
        $this->setState('params', $params);
    }

    /**
     * Send the remind username email
     *
     * @param   array  $data  Array with the data received from the form
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public function processRemindRequest($data)
    {
        // Get the form.
        $form          = $this->getForm();
        $data['email'] = PunycodeHelper::emailToPunycode($data['email']);

        // Check for an error.
        if (empty($form)) {
            return false;
        }

        // Validate the data.
        $data = $this->validate($form, $data);

        // Check for an error.
        if ($data instanceof \Exception) {
            return false;
        }

        // Check the validation results.
        if ($data === false) {
            // Get the validation messages from the form.
            foreach ($form->getErrors() as $formError) {
                $this->setError($formError->getMessage());
            }

            return false;
        }

        // Find the user id for the given email address.
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select('*')
            ->from($db->quoteName('#__users'))
            ->where('LOWER(' . $db->quoteName('email') . ') = LOWER(:email)')
            ->bind(':email', $data['email']);

        // Get the user id.
        $db->setQuery($query);

        try {
            $user = $db->loadObject();
        } catch (\RuntimeException $e) {
            $this->setError(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()));

            return false;
        }

        // Check for a user.
        if (empty($user)) {
            $this->setError(Text::_('COM_USERS_USER_NOT_FOUND'));

            return false;
        }

        // Make sure the user isn't blocked.
        if ($user->block) {
            $this->setError(Text::_('COM_USERS_USER_BLOCKED'));

            return false;
        }

        $app = Factory::getApplication();

        // Assemble the login link.
        $link = 'index.php?option=com_users&view=login';
        $mode = $app->get('force_ssl', 0) == 2 ? 1 : (-1);

        // Put together the email template data.
        $data              = ArrayHelper::fromObject($user);
        $data['sitename']  = $app->get('sitename');
        $data['link_text'] = Route::_($link, false, $mode);
        $data['link_html'] = Route::_($link, true, $mode);

        $mailer = new MailTemplate('com_users.reminder', $app->getLanguage()->getTag());
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
                Factory::getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');

                $return = false;
            }
        }

        // Check for an error.
        if ($return !== true) {
            $this->setError(Text::_('COM_USERS_MAIL_FAILED'));

            return false;
        }

        $this->getDispatcher()->dispatch('onUserAfterRemind', new AfterRemindEvent('onUserAfterRemind', [
            'subject' => $user,
        ]));

        return true;
    }
}
