<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\Database\ParameterType;
use PHPMailer\PHPMailer\Exception as phpMailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Users mail model.
 *
 * @since  1.6
 */
class MailModel extends AdminModel
{
    /**
     * Method to get the row form.
     *
     * @param   array    $data      An optional array of data for the form to interrogate.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form    A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_users.mail', 'mail', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  object  The data for the form.
     *
     * @since   1.6
     * @throws  \Exception
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_users.display.mail.data', new \stdClass());

        $this->preprocessData('com_users.mail', $data);

        return $data;
    }

    /**
     * Method to preprocess the form
     *
     * @param   Form    $form   A form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @since   1.6
     * @throws  \Exception if there is an error loading the form.
     */
    protected function preprocessForm(Form $form, $data, $group = 'user')
    {
        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Send the email
     *
     * @return  boolean
     *
     * @throws  \Exception
     */
    public function send()
    {
        $app      = Factory::getApplication();
        $data     = $app->getInput()->post->get('jform', [], 'array');
        $user     = $this->getCurrentUser();
        $db       = $this->getDatabase();
        $language = Factory::getLanguage();

        $mode         = \array_key_exists('mode', $data) ? (int) $data['mode'] : 0;
        $subject      = \array_key_exists('subject', $data) ? $data['subject'] : '';
        $grp          = \array_key_exists('group', $data) ? (int) $data['group'] : 0;
        $recurse      = \array_key_exists('recurse', $data) ? (int) $data['recurse'] : 0;
        $bcc          = \array_key_exists('bcc', $data) ? (int) $data['bcc'] : 0;
        $disabled     = \array_key_exists('disabled', $data) ? (int) $data['disabled'] : 0;
        $message_body = \array_key_exists('message', $data) ? $data['message'] : '';

        // Automatically removes html formatting
        if (!$mode) {
            $message_body = InputFilter::getInstance()->clean($message_body, 'string');
        }

        // Check for a message body and subject
        if (!$message_body || !$subject) {
            $app->setUserState('com_users.display.mail.data', $data);
            $this->setError(Text::_('COM_USERS_MAIL_PLEASE_FILL_IN_THE_FORM_CORRECTLY'));

            return false;
        }

        // Get users in the group out of the ACL, if group is provided.
        $to = $grp !== 0 ? Access::getUsersByGroup($grp, $recurse) : [];

        // When group is provided but no users are found in the group.
        if ($grp !== 0 && !$to) {
            $rows = [];
        } else {
            // Get all users email and group except for senders
            $uid   = (int) $user->id;
            $query = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('email'),
                        $db->quoteName('name'),
                    ]
                )
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('id') . ' != :id')
                ->bind(':id', $uid, ParameterType::INTEGER);

            if ($grp !== 0) {
                $query->whereIn($db->quoteName('id'), $to);
            }

            if ($disabled === 0) {
                $query->where($db->quoteName('block') . ' = 0');
            }

            $db->setQuery($query);
            $rows = $db->loadObjectList();
        }

        // Check to see if there are any users in this group before we continue
        if (!$rows) {
            $app->setUserState('com_users.display.mail.data', $data);

            if (\in_array($user->id, $to)) {
                $this->setError(Text::_('COM_USERS_MAIL_ONLY_YOU_COULD_BE_FOUND_IN_THIS_GROUP'));
            } else {
                $this->setError(Text::_('COM_USERS_MAIL_NO_USERS_COULD_BE_FOUND_IN_THIS_GROUP'));
            }

            return false;
        }

        // Get the Mailer
        $mailer = new MailTemplate('com_users.massmail.mail', $language->getTag());
        $params = ComponentHelper::getParams('com_users');

        try {
            // Build email message format.
            $data = [
                'subject'       => stripslashes($subject),
                'body'          => $message_body,
                'subjectprefix' => $params->get('mailSubjectPrefix', ''),
                'bodysuffix'    => $params->get('mailBodySuffix', ''),
            ];
            $mailer->addTemplateData($data);

            $recipientType = $bcc ? 'bcc' : 'to';

            // Add recipients
            foreach ($rows as $row) {
                $mailer->addRecipient($row->email, $row->name, $recipientType);
            }

            if ($bcc) {
                $mailer->addRecipient($app->get('mailfrom'), $app->get('fromname'));
            }

            // Send the Mail
            $rs = $mailer->send();
        } catch (MailDisabledException | phpMailerException $exception) {
            try {
                Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');

                $rs = false;
            } catch (\RuntimeException $exception) {
                Factory::getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');

                $rs = false;
            }
        }

        // Check for an error
        if ($rs !== true) {
            $app->setUserState('com_users.display.mail.data', $data);
            $this->setError($mailer->ErrorInfo);

            return false;
        }

        if (empty($rs)) {
            $app->setUserState('com_users.display.mail.data', $data);
            $this->setError(Text::_('COM_USERS_MAIL_THE_MAIL_COULD_NOT_BE_SENT'));

            return false;
        }

        /**
         * Fill the data (specially for the 'mode', 'group' and 'bcc': they could not exist in the array
         * when the box is not checked and in this case, the default value would be used instead of the '0'
         * one)
         */
        $data['mode']    = $mode;
        $data['subject'] = $subject;
        $data['group']   = $grp;
        $data['recurse'] = $recurse;
        $data['bcc']     = $bcc;
        $data['message'] = $message_body;
        $app->setUserState('com_users.display.mail.data', []);
        $app->enqueueMessage(Text::plural('COM_USERS_MAIL_EMAIL_SENT_TO_N_USERS', \count($rows)), 'message');

        return true;
    }
}
