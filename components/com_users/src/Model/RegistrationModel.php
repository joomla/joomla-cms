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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryAwareInterface;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\CMS\User\UserHelper;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Registration model class for Users.
 *
 * @since  1.6
 */
class RegistrationModel extends FormModel implements UserFactoryAwareInterface
{
    use UserFactoryAwareTrait;

    /**
     * @var    object  The user registration data.
     * @since  1.6
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param   array                  $config       An array of configuration options (name, state, dbo, table_path, ignore_request).
     * @param   ?MVCFactoryInterface   $factory      The factory.
     * @param   ?FormFactoryInterface  $formFactory  The form factory.
     *
     * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.2
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, ?FormFactoryInterface $formFactory = null)
    {
        $config = array_merge(
            [
                'events_map' => ['validate' => 'user'],
            ],
            $config
        );

        parent::__construct($config, $factory, $formFactory);
    }

    /**
     * Method to get the user ID from the given token
     *
     * @param   string  $token  The activation token.
     *
     * @return  mixed   False on failure, id of the user on success
     *
     * @since   3.8.13
     */
    public function getUserIdFromToken($token)
    {
        $db       = $this->getDatabase();

        // Get the user id based on the token.
        $query = $db->getQuery(true);
        $query->select($db->quoteName('id'))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('activation') . ' = :activation')
            ->where($db->quoteName('block') . ' = 1')
            ->where($db->quoteName('lastvisitDate') . ' IS NULL')
            ->bind(':activation', $token);
        $db->setQuery($query);

        try {
            return (int) $db->loadResult();
        } catch (\RuntimeException $e) {
            $this->setError(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()));

            return false;
        }
    }

    /**
     * Method to activate a user account.
     *
     * @param   string  $token  The activation token.
     *
     * @return  mixed    False on failure, user object on success.
     *
     * @since   1.6
     */
    public function activate($token)
    {
        $app        = Factory::getApplication();
        $userParams = ComponentHelper::getParams('com_users');
        $userId     = $this->getUserIdFromToken($token);

        // Check for a valid user id.
        if (!$userId) {
            $this->setError(Text::_('COM_USERS_ACTIVATION_TOKEN_NOT_FOUND'));

            return false;
        }

        // Load the users plugin group.
        PluginHelper::importPlugin('user');

        // Activate the user.
        $user = $this->getUserFactory()->loadUserById($userId);

        // Admin activation is on and user is verifying their email
        if (($userParams->get('useractivation') == 2) && !$user->getParam('activate', 0)) {
            $linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

            // Compile the admin notification mail values.
            $data               = $user->getProperties();
            $data['activation'] = ApplicationHelper::getHash(UserHelper::genRandomPassword());
            $user->activation   = $data['activation'];
            $data['siteurl']    = Uri::base();
            $data['activate']   = Route::link(
                'site',
                'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
                false,
                $linkMode,
                true
            );

            $data['fromname'] = $app->get('fromname');
            $data['mailfrom'] = $app->get('mailfrom');
            $data['sitename'] = $app->get('sitename');
            $user->setParam('activate', 1);

            // Get all admin users
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName(['name', 'email', 'sendEmail', 'id']))
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('sendEmail') . ' = 1')
                ->where($db->quoteName('block') . ' = 0');

            $db->setQuery($query);

            try {
                $rows = $db->loadObjectList();
            } catch (\RuntimeException $e) {
                $this->setError(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()));

                return false;
            }

            // Send mail to all users with users creating permissions and receiving system emails
            foreach ($rows as $row) {
                $usercreator = $this->getUserFactory()->loadUserById($row->id);

                if ($usercreator->authorise('core.create', 'com_users') && $usercreator->authorise('core.manage', 'com_users')) {
                    try {
                        $mailer = new MailTemplate('com_users.registration.admin.verification_request', $app->getLanguage()->getTag());
                        $mailer->addTemplateData($data);
                        $mailer->addRecipient($row->email);
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
                        $this->setError(Text::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));

                        return false;
                    }
                }
            }
        } elseif (($userParams->get('useractivation') == 2) && $user->getParam('activate', 0)) {
            // Admin activation is on and admin is activating the account
            $user->activation = '';
            $user->block      = '0';

            // Compile the user activated notification mail values.
            $data = $user->getProperties();
            $user->setParam('activate', 0);
            $data['fromname'] = $app->get('fromname');
            $data['mailfrom'] = $app->get('mailfrom');
            $data['sitename'] = $app->get('sitename');
            $data['siteurl']  = Uri::base();
            $mailer           = new MailTemplate('com_users.registration.user.admin_activated', $app->getLanguage()->getTag());
            $mailer->addTemplateData($data);
            $mailer->addRecipient($data['email']);

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
                $this->setError(Text::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));

                return false;
            }
        } else {
            $user->activation = '';
            $user->block      = '0';
        }

        // Store the user object.
        if (!$user->save()) {
            $this->setError(Text::sprintf('COM_USERS_REGISTRATION_ACTIVATION_SAVE_FAILED', $user->getError()));

            return false;
        }

        return $user;
    }

    /**
     * Method to get the registration form data.
     *
     * The base form data is loaded and then an event is fired
     * for users plugins to extend the data.
     *
     * @return  object|boolean  Data object on success, false on failure.
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function getData()
    {
        if ($this->data === null) {
            $this->data = new \stdClass();
            $app        = Factory::getApplication();
            $params     = ComponentHelper::getParams('com_users');

            // Override the base user data with any data in the session.
            $temp = (array) $app->getUserState('com_users.registration.data', []);

            // Don't load the data in this getForm call, or we'll call ourself
            $form = $this->getForm([], false);

            foreach ($temp as $k => $v) {
                // Here we could have a grouped field, let's check it
                if (\is_array($v)) {
                    $this->data->$k = new \stdClass();

                    foreach ($v as $key => $val) {
                        if ($form->getField($key, $k) !== false) {
                            $this->data->$k->$key = $val;
                        }
                    }
                } elseif ($form->getField($k) !== false) {
                    // Only merge the field if it exists in the form.
                    $this->data->$k = $v;
                }
            }

            // Get the groups the user should be added to after registration.
            $this->data->groups = [];

            // Get the default new user group, guest or public group if not specified.
            $system = $params->get('new_usertype', $params->get('guest_usergroup', 1));

            $this->data->groups[] = $system;

            // Unset the passwords.
            unset($this->data->password1, $this->data->password2);

            // Get the dispatcher and load the users plugins.
            PluginHelper::importPlugin('user');

            // Trigger the data preparation event.
            Factory::getApplication()->triggerEvent('onContentPrepareData', ['com_users.registration', $this->data]);
        }

        return $this->data;
    }

    /**
     * Method to get the registration form.
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
        $form = $this->loadForm('com_users.registration', 'registration', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        // When multilanguage is set, a user's default site language should also be a Content Language
        if (Multilanguage::isEnabled()) {
            $form->setFieldAttribute('language', 'type', 'frontendlanguage', 'params');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  object  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        $data = $this->getData();

        if (Multilanguage::isEnabled() && empty($data->language)) {
            $data->language = Factory::getLanguage()->getTag();
        }

        $this->preprocessData('com_users.registration', $data);

        return $data;
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
     * @since   1.6
     * @throws  \Exception if there is an error in the form event.
     */
    protected function preprocessForm(Form $form, $data, $group = 'user')
    {
        $userParams = ComponentHelper::getParams('com_users');

        // Add the choice for site language at registration time
        if ($userParams->get('site_language') == 1 && $userParams->get('frontend_userparams') == 1) {
            $form->loadFile('sitelang', false);
        }

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
        $app    = Factory::getApplication();
        $params = $app->getParams('com_users');

        // Load the parameters.
        $this->setState('params', $params);
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $temp  The form data.
     *
     * @return  mixed  The user id on success, false on failure.
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function register($temp)
    {
        $params = ComponentHelper::getParams('com_users');

        // Initialise the table with Joomla\CMS\User\User.
        $user = new User();
        $data = (array) $this->getData();

        // Merge in the registration data.
        foreach ($temp as $k => $v) {
            $data[$k] = $v;
        }

        // Prepare the data for the user object.
        $data['email']    = PunycodeHelper::emailToPunycode($data['email1']);
        $data['password'] = $data['password1'];
        $useractivation   = $params->get('useractivation');
        $sendpassword     = $params->get('sendpassword', 1);

        // Check if the user needs to activate their account.
        if (($useractivation == 1) || ($useractivation == 2)) {
            $data['activation'] = ApplicationHelper::getHash(UserHelper::genRandomPassword());
            $data['block']      = 1;
        }

        // Bind the data.
        if (!$user->bind($data)) {
            $this->setError($user->getError());

            return false;
        }

        // Load the users plugin group.
        PluginHelper::importPlugin('user');

        // Store the data.
        if (!$user->save()) {
            $this->setError(Text::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $user->getError()));

            return false;
        }

        $app   = Factory::getApplication();
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Compile the notification mail values.
        $data             = $user->getProperties();
        $data['fromname'] = $app->get('fromname');
        $data['mailfrom'] = $app->get('mailfrom');
        $data['sitename'] = $app->get('sitename');
        $data['siteurl']  = Uri::root();

        // Handle account activation/confirmation emails.
        if ($useractivation == 2) {
            // Set the link to confirm the user email.
            $linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

            $data['activate'] = Route::link(
                'site',
                'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
                false,
                $linkMode,
                true
            );

            $mailtemplate = 'com_users.registration.user.admin_activation';
        } elseif ($useractivation == 1) {
            // Set the link to activate the user account.
            $linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

            $data['activate'] = Route::link(
                'site',
                'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
                false,
                $linkMode,
                true
            );

            $mailtemplate = 'com_users.registration.user.self_activation';
        } else {
            $mailtemplate = 'com_users.registration.user.registration_mail';
        }

        if ($sendpassword) {
            $mailtemplate .= '_w_pw';
        }

        // Try to send the registration email.
        try {
            $mailer = new MailTemplate($mailtemplate, $app->getLanguage()->getTag());
            $mailer->addTemplateData($data);
            $mailer->addRecipient($data['email']);
            $mailer->addUnsafeTags(['username', 'password_clear', 'name']);
            $return = $mailer->send();
        } catch (\Exception $exception) {
            try {
                Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');

                $return = false;
            } catch (\RuntimeException $exception) {
                Factory::getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');

                $this->setError(Text::_('COM_MESSAGES_ERROR_MAIL_FAILED'));

                $return = false;
            }
        }

        // Send mail to all users with user creating permissions and receiving system emails
        if (($params->get('useractivation') < 2) && ($params->get('mail_to_admin') == 1)) {
            // Get all admin users
            $query->clear()
                ->select($db->quoteName(['name', 'email', 'sendEmail', 'id']))
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('sendEmail') . ' = 1')
                ->where($db->quoteName('block') . ' = 0');

            $db->setQuery($query);

            try {
                $rows = $db->loadObjectList();
            } catch (\RuntimeException $e) {
                $this->setError(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()));

                return false;
            }

            // Send mail to all superadministrators id
            foreach ($rows as $row) {
                $usercreator = $this->getUserFactory()->loadUserById($row->id);

                if (!$usercreator->authorise('core.create', 'com_users') || !$usercreator->authorise('core.manage', 'com_users')) {
                    continue;
                }

                try {
                    $mailer = new MailTemplate('com_users.registration.admin.new_notification', $app->getLanguage()->getTag());
                    $mailer->addTemplateData($data);
                    $mailer->addRecipient($row->email);
                    $mailer->addUnsafeTags(['username', 'name']);
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
                    $this->setError(Text::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));

                    return false;
                }
            }
        }

        // Check for an error.
        if ($return !== true) {
            $this->setError(Text::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));

            // Send a system message to administrators receiving system mails
            $db = $this->getDatabase();
            $query->clear()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__users'))
                ->where($db->quoteName('block') . ' = 0')
                ->where($db->quoteName('sendEmail') . ' = 1');
            $db->setQuery($query);

            try {
                $userids = $db->loadColumn();
            } catch (\RuntimeException $e) {
                $this->setError(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()));

                return false;
            }

            if (\count($userids) > 0) {
                $jdate     = new Date();
                $dateToSql = $jdate->toSql();
                $subject   = Text::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT');
                $message   = Text::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $data['username']);

                // Build the query to add the messages
                foreach ($userids as $userid) {
                    $values = [
                        ':user_id_from',
                        ':user_id_to',
                        ':date_time',
                        ':subject',
                        ':message',
                    ];
                    $query->clear()
                        ->insert($db->quoteName('#__messages'))
                        ->columns($db->quoteName(['user_id_from', 'user_id_to', 'date_time', 'subject', 'message']))
                        ->values(implode(',', $values));
                    $query->bind(':user_id_from', $userid, ParameterType::INTEGER)
                        ->bind(':user_id_to', $userid, ParameterType::INTEGER)
                        ->bind(':date_time', $dateToSql)
                        ->bind(':subject', $subject)
                        ->bind(':message', $message);

                    $db->setQuery($query);

                    try {
                        $db->execute();
                    } catch (\RuntimeException $e) {
                        $this->setError(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()));

                        return false;
                    }
                }
            }

            return false;
        }

        if ($useractivation == 1) {
            return 'useractivate';
        }

        if ($useractivation == 2) {
            return 'adminactivate';
        }

        return $user->id;
    }
}
