<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\Model;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Access\Rule;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryAwareInterface;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Database\ParameterType;
use PHPMailer\PHPMailer\Exception as phpMailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Private Message model.
 *
 * @since  1.6
 */
class MessageModel extends AdminModel implements UserFactoryAwareInterface
{
    use UserFactoryAwareTrait;

    /**
     * Message
     *
     * @var    \stdClass
     */
    protected $item;

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState()
    {
        parent::populateState();

        $input = Factory::getApplication()->getInput();

        $user  = $this->getCurrentUser();
        $this->setState('user.id', $user->id);

        $messageId = (int) $input->getInt('message_id');
        $this->setState('message.id', $messageId);

        $replyId = (int) $input->getInt('reply_id');
        $this->setState('reply.id', $replyId);
    }

    /**
     * Check that recipient user is the one trying to delete and then call parent delete method
     *
     * @param   array  &$pks  An array of record primary keys.
     *
     * @return  boolean  True if successful, false if an error occurs.
     *
     * @since  3.1
     */
    public function delete(&$pks)
    {
        $pks   = (array) $pks;
        $table = $this->getTable();
        $user  = $this->getCurrentUser();

        // Iterate the items to delete each one.
        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                if ($table->user_id_to != $user->id) {
                    // Prune items that you can't change.
                    unset($pks[$i]);

                    try {
                        Log::add(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), Log::WARNING, 'jerror');
                    } catch (\RuntimeException) {
                        Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'), 'warning');
                    }

                    return false;
                }
            } else {
                $this->setError($table->getError());

                return false;
            }
        }

        return parent::delete($pks);
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed    Object on success, false on failure.
     *
     * @since   1.6
     */
    public function getItem($pk = null)
    {
        if (!isset($this->item)) {
            if ($this->item = parent::getItem($pk)) {
                // Invalid message_id returns 0
                if ($this->item->user_id_to === '0') {
                    $this->setError(Text::_('JERROR_ALERTNOAUTHOR'));

                    return false;
                }

                // Prime required properties.
                if (empty($this->item->message_id)) {
                    // Prepare data for a new record.
                    if ($replyId = (int) $this->getState('reply.id')) {
                        // If replying to a message, preload some data.
                        $db    = $this->getDatabase();
                        $query = $db->getQuery(true)
                            ->select($db->quoteName(['subject', 'user_id_from', 'user_id_to']))
                            ->from($db->quoteName('#__messages'))
                            ->where($db->quoteName('message_id') . ' = :messageid')
                            ->bind(':messageid', $replyId, ParameterType::INTEGER);

                        try {
                            $message = $db->setQuery($query)->loadObject();
                        } catch (\RuntimeException $e) {
                            $this->setError($e->getMessage());

                            return false;
                        }

                        if (!$message || $message->user_id_to != $this->getCurrentUser()->id) {
                            $this->setError(Text::_('JERROR_ALERTNOAUTHOR'));

                            return false;
                        }

                        $this->item->set('user_id_to', $message->user_id_from);
                        $re = Text::_('COM_MESSAGES_RE');

                        if (stripos($message->subject, $re) !== 0) {
                            $this->item->set('subject', $re . ' ' . $message->subject);
                        }
                    }
                } elseif ($this->item->user_id_to != $this->getCurrentUser()->id) {
                    $this->setError(Text::_('JERROR_ALERTNOAUTHOR'));

                    return false;
                } else {
                    // Mark message read
                    $db    = $this->getDatabase();
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__messages'))
                        ->set($db->quoteName('state') . ' = 1')
                        ->where($db->quoteName('message_id') . ' = :messageid')
                        ->bind(':messageid', $this->item->message_id, ParameterType::INTEGER);
                    $db->setQuery($query)->execute();
                }
            }

            // Get the user name for an existing message.
            if ($this->item->user_id_from && $fromUser = new User($this->item->user_id_from)) {
                $this->item->set('from_user_name', $fromUser->name);
            }
        }

        return $this->item;
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  \Joomla\CMS\Form\Form|bool  A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_messages.message', 'message', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_messages.edit.message.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_messages.message', $data);

        return $data;
    }

    /**
     * Checks that the current user matches the message recipient and calls the parent publish method
     *
     * @param   array    &$pks   A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     *
     * @since   3.1
     */
    public function publish(&$pks, $value = 1)
    {
        $user  = $this->getCurrentUser();
        $table = $this->getTable();
        $pks   = (array) $pks;

        // Check that the recipient matches the current user
        foreach ($pks as $i => $pk) {
            $table->reset();

            if ($table->load($pk)) {
                if ($table->user_id_to != $user->id) {
                    // Prune items that you can't change.
                    unset($pks[$i]);

                    try {
                        Log::add(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), Log::WARNING, 'jerror');
                    } catch (\RuntimeException) {
                        Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 'warning');
                    }

                    return false;
                }
            }
        }

        return parent::publish($pks, $value);
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function save($data)
    {
        $table = $this->getTable();

        // Bind the data.
        if (!$table->bind($data)) {
            $this->setError($table->getError());

            return false;
        }

        // Assign empty values.
        if (empty($table->user_id_from)) {
            $table->user_id_from = $this->getCurrentUser()->id;
        }

        if ((int) $table->date_time == 0) {
            $table->date_time = Factory::getDate()->toSql();
        }

        // Check the data.
        if (!$table->check()) {
            $this->setError($table->getError());

            return false;
        }

        // Load the user details (already valid from table check).
        $toUser = $this->getUserFactory()->loadUserById($table->user_id_to);

        // Check if recipient can access com_messages.
        if (!$toUser->authorise('core.login.admin') || !$toUser->authorise('core.manage', 'com_messages')) {
            $this->setError(Text::_('COM_MESSAGES_ERROR_RECIPIENT_NOT_AUTHORISED'));

            return false;
        }

        // Load the recipient user configuration.
        $model  = $this->bootComponent('com_messages')
            ->getMVCFactory()->createModel('Config', 'Administrator', ['ignore_request' => true]);
        $model->setState('user.id', $table->user_id_to);
        $config = $model->getItem();

        if (empty($config)) {
            $this->setError($model->getError());

            return false;
        }

        if ($config->get('lock', false)) {
            $this->setError(Text::_('COM_MESSAGES_ERR_SEND_FAILED'));

            return false;
        }

        // Store the data.
        if (!$table->store()) {
            $this->setError($table->getError());

            return false;
        }

        $key = $table->getKeyName();

        if (isset($table->$key)) {
            $this->setState($this->getName() . '.id', $table->$key);
        }

        if ($config->get('mail_on_new', true)) {
            $fromUser         = $this->getUserFactory()->loadUserById($table->user_id_from);
            $debug            = Factory::getApplication()->get('debug_lang');
            $default_language = ComponentHelper::getParams('com_languages')->get('administrator');
            $lang             = Language::getInstance($toUser->getParam('admin_language', $default_language), $debug);
            $lang->load('com_messages', JPATH_ADMINISTRATOR);

            // Build the email subject and message
            $app      = Factory::getApplication();
            $linkMode = $app->get('force_ssl', 0) >= 1 ? Route::TLS_FORCE : Route::TLS_IGNORE;
            $sitename = $app->get('sitename');
            $fromName = $fromUser->name;
            $siteURL  = Route::link(
                'administrator',
                'index.php?option=com_messages&view=message&message_id=' . $table->message_id,
                false,
                $linkMode,
                true
            );
            $subject  = html_entity_decode($table->subject, ENT_COMPAT, 'UTF-8');
            $message  = strip_tags(html_entity_decode($table->message, ENT_COMPAT, 'UTF-8'));

            // Send the email
            $mailer = new MailTemplate('com_messages.new_message', $lang->getTag());
            $data   = [
                'subject'   => $subject,
                'message'   => $message,
                'fromname'  => $fromName,
                'sitename'  => $sitename,
                'siteurl'   => $siteURL,
                'fromemail' => $fromUser->email,
                'toname'    => $toUser->name,
                'toemail'   => $toUser->email,
            ];
            $mailer->addTemplateData($data);
            $mailer->setReplyTo($fromUser->email, $fromUser->name);
            $mailer->addRecipient($toUser->email, $toUser->name);

            try {
                $mailer->send();
            } catch (MailDisabledException | phpMailerException $exception) {
                try {
                    Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');

                    $this->setError(Text::_('COM_MESSAGES_ERROR_MAIL_FAILED'));

                    return false;
                } catch (\RuntimeException $exception) {
                    Factory::getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');

                    $this->setError(Text::_('COM_MESSAGES_ERROR_MAIL_FAILED'));

                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Sends a message to the site's super users
     *
     * @param   string  $subject  The message subject
     * @param   string  $message  The message
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function notifySuperUsers($subject, $message, $fromUser = null)
    {
        $db = $this->getDatabase();

        try {
            $table  = new Asset($db);
            $rootId = $table->getRootId();

            /** @var Rule[] $rules */
            $rules     = Access::getAssetRules($rootId)->getData();
            $rawGroups = $rules['core.admin']->getData();

            if (empty($rawGroups)) {
                $this->setError(Text::_('COM_MESSAGES_ERROR_MISSING_ROOT_ASSET_GROUPS'));

                return false;
            }

            $groups = [];

            foreach ($rawGroups as $g => $enabled) {
                if ($enabled) {
                    $groups[] = $g;
                }
            }

            if (empty($groups)) {
                $this->setError(Text::_('COM_MESSAGES_ERROR_NO_GROUPS_SET_AS_SUPER_USER'));

                return false;
            }

            $query = $db->getQuery(true)
                ->select($db->quoteName('map.user_id'))
                ->from($db->quoteName('#__user_usergroup_map', 'map'))
                ->join(
                    'LEFT',
                    $db->quoteName('#__users', 'u'),
                    $db->quoteName('u.id') . ' = ' . $db->quoteName('map.user_id')
                )
                ->whereIn($db->quoteName('map.group_id'), $groups)
                ->where($db->quoteName('u.block') . ' = 0')
                ->where($db->quoteName('u.sendEmail') . ' = 1');

            $userIDs = $db->setQuery($query)->loadColumn(0);

            if (empty($userIDs)) {
                $this->setError(Text::_('COM_MESSAGES_ERROR_NO_USERS_SET_AS_SUPER_USER'));

                return false;
            }

            foreach ($userIDs as $id) {
                /*
                 * All messages must have a valid from user, we have use cases where an unauthenticated user may trigger this
                 * so we will set the from user as the to user
                 */
                $data = [
                    'user_id_from' => $id,
                    'user_id_to'   => $id,
                    'subject'      => $subject,
                    'message'      => $message,
                ];

                if (!$this->save($data)) {
                    return false;
                }
            }

            return true;
        } catch (\Exception $exception) {
            $this->setError($exception->getMessage());

            return false;
        }
    }
}
