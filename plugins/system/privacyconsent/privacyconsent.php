<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.privacyconsent
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;
use Joomla\Component\Messages\Administrator\Model\MessageModel;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;
use PHPMailer\PHPMailer\Exception as phpmailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * An example custom privacyconsent plugin.
 *
 * @since  3.9.0
 */
class PlgSystemPrivacyconsent extends CMSPlugin
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.9.0
     */
    protected $autoloadLanguage = true;

    /**
     * Application object.
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  3.9.0
     */
    protected $app;

    /**
     * Database object.
     *
     * @var    \Joomla\Database\DatabaseDriver
     * @since  3.9.0
     */
    protected $db;

    /**
     * Adds additional fields to the user editing form
     *
     * @param   Form   $form  The form to be altered.
     * @param   mixed  $data  The associated data for the form.
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function onContentPrepareForm(Form $form, $data)
    {
        // Check we are manipulating a valid form - we only display this on user registration form and user profile form.
        $name = $form->getName();

        if (!in_array($name, ['com_users.profile', 'com_users.registration'])) {
            return true;
        }

        // We only display this if user has not consented before
        if (is_object($data)) {
            $userId = $data->id ?? 0;

            if ($userId > 0 && $this->isUserConsented($userId)) {
                return true;
            }
        }

        // Add the privacy policy fields to the form.
        FormHelper::addFieldPrefix('Joomla\\Plugin\\System\\PrivacyConsent\\Field');
        FormHelper::addFormPath(__DIR__ . '/forms');
        $form->loadFile('privacyconsent');

        $privacyType = $this->params->get('privacy_type', 'article');
        $privacyId   = ($privacyType == 'menu_item') ? $this->getPrivacyItemId() : $this->getPrivacyArticleId();
        $privacynote = $this->params->get('privacy_note');

        // Push the privacy article ID into the privacy field.
        $form->setFieldAttribute('privacy', $privacyType, $privacyId, 'privacyconsent');
        $form->setFieldAttribute('privacy', 'note', $privacynote, 'privacyconsent');
    }

    /**
     * Method is called before user data is stored in the database
     *
     * @param   array    $user   Holds the old user data.
     * @param   boolean  $isNew  True if a new user is stored.
     * @param   array    $data   Holds the new user data.
     *
     * @return  boolean
     *
     * @since   3.9.0
     * @throws  InvalidArgumentException on missing required data.
     */
    public function onUserBeforeSave($user, $isNew, $data)
    {
        // // Only check for front-end user creation/update profile
        if ($this->app->isClient('administrator')) {
            return true;
        }

        $userId = ArrayHelper::getValue($user, 'id', 0, 'int');

        // User already consented before, no need to check it further
        if ($userId > 0 && $this->isUserConsented($userId)) {
            return true;
        }

        // Check that the privacy is checked if required ie only in registration from frontend.
        $input  = $this->app->getInput();
        $option = $input->get('option');
        $task   = $input->post->get('task');
        $form   = $input->post->get('jform', [], 'array');

        if (
            $option == 'com_users' && in_array($task, ['registration.register', 'profile.save'])
            && empty($form['privacyconsent']['privacy'])
        ) {
            throw new InvalidArgumentException(Text::_('PLG_SYSTEM_PRIVACYCONSENT_FIELD_ERROR'));
        }

        return true;
    }

    /**
     * Saves user privacy confirmation
     *
     * @param   array    $data    entered user data
     * @param   boolean  $isNew   true if this is a new user
     * @param   boolean  $result  true if saving the user worked
     * @param   string   $error   error message
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterSave($data, $isNew, $result, $error): void
    {
        // Only create an entry on front-end user creation/update profile
        if ($this->app->isClient('administrator')) {
            return;
        }

        // Get the user's ID
        $userId = ArrayHelper::getValue($data, 'id', 0, 'int');

        // If user already consented before, no need to check it further
        if ($userId > 0 && $this->isUserConsented($userId)) {
            return;
        }

        $input  = $this->app->getInput();
        $option = $input->get('option');
        $task   = $input->post->get('task');
        $form   = $input->post->get('jform', [], 'array');

        if (
            $option == 'com_users'
            && in_array($task, ['registration.register', 'profile.save'])
            && !empty($form['privacyconsent']['privacy'])
        ) {
            $userId = ArrayHelper::getValue($data, 'id', 0, 'int');

            // Get the user's IP address
            $ip = $input->server->get('REMOTE_ADDR', '', 'string');

            // Get the user agent string
            $userAgent = $input->server->get('HTTP_USER_AGENT', '', 'string');

            // Create the user note
            $userNote = (object) [
                'user_id' => $userId,
                'subject' => 'PLG_SYSTEM_PRIVACYCONSENT_SUBJECT',
                'body'    => Text::sprintf('PLG_SYSTEM_PRIVACYCONSENT_BODY', $ip, $userAgent),
                'created' => Factory::getDate()->toSql(),
            ];

            try {
                $this->db->insertObject('#__privacy_consents', $userNote);
            } catch (Exception $e) {
                // Do nothing if the save fails
            }

            $userId = ArrayHelper::getValue($data, 'id', 0, 'int');

            $message = [
                'action'      => 'consent',
                'id'          => $userId,
                'title'       => $data['name'],
                'itemlink'    => 'index.php?option=com_users&task=user.edit&id=' . $userId,
                'userid'      => $userId,
                'username'    => $data['username'],
                'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $userId,
            ];

            /** @var ActionlogModel $model */
            $model = $this->app->bootComponent('com_actionlogs')->getMVCFactory()->createModel('Actionlog', 'Administrator');
            $model->addLog([$message], 'PLG_SYSTEM_PRIVACYCONSENT_CONSENT', 'plg_system_privacyconsent', $userId);
        }
    }

    /**
     * Remove all user privacy consent information for the given user ID
     *
     * Method is called after user data is deleted from the database
     *
     * @param   array    $user     Holds the user data
     * @param   boolean  $success  True if user was successfully stored in the database
     * @param   string   $msg      Message
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onUserAfterDelete($user, $success, $msg): void
    {
        if (!$success) {
            return;
        }

        $userId = ArrayHelper::getValue($user, 'id', 0, 'int');

        if ($userId) {
            // Remove user's consent
            try {
                $query = $this->db->getQuery(true)
                    ->delete($this->db->quoteName('#__privacy_consents'))
                    ->where($this->db->quoteName('user_id') . ' = :userid')
                    ->bind(':userid', $userId, ParameterType::INTEGER);
                $this->db->setQuery($query);
                $this->db->execute();
            } catch (Exception $e) {
                $this->_subject->setError($e->getMessage());
            }
        }
    }

    /**
     * If logged in users haven't agreed to privacy consent, redirect them to profile edit page, ask them to agree to
     * privacy consent before allowing access to any other pages
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onAfterRoute()
    {
        // Run this in frontend only
        if ($this->app->isClient('administrator')) {
            return;
        }

        $userId = Factory::getUser()->id;

        // Check to see whether user already consented, if not, redirect to user profile page
        if ($userId > 0) {
            // If user consented before, no need to check it further
            if ($this->isUserConsented($userId)) {
                return;
            }

            $input  = $this->app->getInput();
            $option = $input->getCmd('option');
            $task   = $input->get('task', '');
            $view   = $input->getString('view', '');
            $layout = $input->getString('layout', '');
            $id     = $input->getInt('id');

            $privacyArticleId = $this->getPrivacyArticleId();

            /*
             * If user is already on edit profile screen or view privacy article
             * or press update/apply button, or logout, do nothing to avoid infinite redirect
             */
            $allowedUserTasks = [
                'profile.save', 'profile.apply', 'user.logout', 'user.menulogout',
                'method', 'methods', 'captive', 'callback',
            ];
            $isAllowedUserTask = in_array($task, $allowedUserTasks)
                || substr($task, 0, 8) === 'captive.'
                || substr($task, 0, 8) === 'methods.'
                || substr($task, 0, 7) === 'method.'
                || substr($task, 0, 9) === 'callback.';

            if (
                ($option == 'com_users' && $isAllowedUserTask)
                || ($option == 'com_content' && $view == 'article' && $id == $privacyArticleId)
                || ($option == 'com_users' && $view == 'profile' && $layout == 'edit')
            ) {
                return;
            }

            // Redirect to com_users profile edit
            $this->app->enqueueMessage($this->getRedirectMessage(), 'notice');
            $link = 'index.php?option=com_users&view=profile&layout=edit';
            $this->app->redirect(Route::_($link, false));
        }
    }

    /**
     * Event to specify whether a privacy policy has been published.
     *
     * @param   array  &$policy  The privacy policy status data, passed by reference, with keys "published", "editLink" and "articlePublished".
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onPrivacyCheckPrivacyPolicyPublished(&$policy)
    {
        // If another plugin has already indicated a policy is published, we won't change anything here
        if ($policy['published']) {
            return;
        }

        $articleId = (int) $this->params->get('privacy_article');

        if (!$articleId) {
            return;
        }

        // Check if the article exists in database and is published
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(['id', 'state']))
            ->from($this->db->quoteName('#__content'))
            ->where($this->db->quoteName('id') . ' = :id')
            ->bind(':id', $articleId, ParameterType::INTEGER);
        $this->db->setQuery($query);

        $article = $this->db->loadObject();

        // Check if the article exists
        if (!$article) {
            return;
        }

        // Check if the article is published
        if ($article->state == 1) {
            $policy['articlePublished'] = true;
        }

        $policy['published'] = true;
        $policy['editLink']  = Route::_('index.php?option=com_content&task=article.edit&id=' . $articleId);
    }

    /**
     * Returns the configured redirect message and falls back to the default version.
     *
     * @return  string  redirect message
     *
     * @since   3.9.0
     */
    private function getRedirectMessage()
    {
        $messageOnRedirect = trim($this->params->get('messageOnRedirect', ''));

        if (empty($messageOnRedirect)) {
            return Text::_('PLG_SYSTEM_PRIVACYCONSENT_REDIRECT_MESSAGE_DEFAULT');
        }

        return $messageOnRedirect;
    }

    /**
     * Method to check if the given user has consented yet
     *
     * @param   integer  $userId  ID of uer to check
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    private function isUserConsented($userId)
    {
        $userId = (int) $userId;
        $db     = $this->db;
        $query  = $db->getQuery(true);

        $query->select('COUNT(*)')
            ->from($db->quoteName('#__privacy_consents'))
            ->where($db->quoteName('user_id') . ' = :userid')
            ->where($db->quoteName('subject') . ' = ' . $db->quote('PLG_SYSTEM_PRIVACYCONSENT_SUBJECT'))
            ->where($db->quoteName('state') . ' = 1')
            ->bind(':userid', $userId, ParameterType::INTEGER);
        $db->setQuery($query);

        return (int) $db->loadResult() > 0;
    }

    /**
     * Get privacy article ID. If the site is a multilingual website and there is associated article for the
     * current language, ID of the associated article will be returned
     *
     * @return  integer
     *
     * @since   3.9.0
     */
    private function getPrivacyArticleId()
    {
        $privacyArticleId = $this->params->get('privacy_article');

        if ($privacyArticleId > 0 && Associations::isEnabled()) {
            $privacyAssociated = Associations::getAssociations('com_content', '#__content', 'com_content.item', $privacyArticleId);
            $currentLang       = Factory::getLanguage()->getTag();

            if (isset($privacyAssociated[$currentLang])) {
                $privacyArticleId = $privacyAssociated[$currentLang]->id;
            }
        }

        return $privacyArticleId;
    }

    /**
     * Get privacy menu item ID. If the site is a multilingual website and there is associated menu item for the
     * current language, ID of the associated menu item will be returned.
     *
     * @return  integer
     *
     * @since   4.0.0
     */
    private function getPrivacyItemId()
    {
        $itemId = $this->params->get('privacy_menu_item');

        if ($itemId > 0 && Associations::isEnabled()) {
            $privacyAssociated = Associations::getAssociations('com_menus', '#__menu', 'com_menus.item', $itemId, 'id', '', '');
            $currentLang       = Factory::getLanguage()->getTag();

            if (isset($privacyAssociated[$currentLang])) {
                $itemId = $privacyAssociated[$currentLang]->id;
            }
        }

        return $itemId;
    }

    /**
     * The privacy consent expiration check code is triggered after the page has fully rendered.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onAfterRender()
    {
        if (!$this->params->get('enabled', 0)) {
            return;
        }

        $cacheTimeout = (int) $this->params->get('cachetimeout', 30);
        $cacheTimeout = 24 * 3600 * $cacheTimeout;

        // Do we need to run? Compare the last run timestamp stored in the plugin's options with the current
        // timestamp. If the difference is greater than the cache timeout we shall not execute again.
        $now  = time();
        $last = (int) $this->params->get('lastrun', 0);

        if ((abs($now - $last) < $cacheTimeout)) {
            return;
        }

        // Update last run status
        $this->params->set('lastrun', $now);

        $paramsJson = $this->params->toString('JSON');
        $db         = $this->db;
        $query      = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = :params')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('privacyconsent'))
            ->bind(':params', $paramsJson);

        try {
            // Lock the tables to prevent multiple plugin executions causing a race condition
            $db->lockTable('#__extensions');
        } catch (Exception $e) {
            // If we can't lock the tables it's too risky to continue execution
            return;
        }

        try {
            // Update the plugin parameters
            $result = $db->setQuery($query)->execute();
            $this->clearCacheGroups(['com_plugins'], [0, 1]);
        } catch (Exception $exc) {
            // If we failed to execute
            $db->unlockTables();
            $result = false;
        }

        try {
            // Unlock the tables after writing
            $db->unlockTables();
        } catch (Exception $e) {
            // If we can't lock the tables assume we have somehow failed
            $result = false;
        }

        // Abort on failure
        if (!$result) {
            return;
        }

        // Delete the expired privacy consents
        $this->invalidateExpiredConsents();

        // Remind for privacy consents near to expire
        $this->remindExpiringConsents();
    }

    /**
     * Method to send the remind for privacy consents renew
     *
     * @return  integer
     *
     * @since   3.9.0
     */
    private function remindExpiringConsents()
    {
        // Load the parameters.
        $expire   = (int) $this->params->get('consentexpiration', 365);
        $remind   = (int) $this->params->get('remind', 30);
        $now      = Factory::getDate()->toSql();
        $period   = '-' . ($expire - $remind);
        $db       = $this->db;
        $query    = $db->getQuery(true);

        $query->select($db->quoteName(['r.id', 'r.user_id', 'u.email']))
            ->from($db->quoteName('#__privacy_consents', 'r'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('r.user_id'))
            ->where($db->quoteName('subject') . ' = ' . $db->quote('PLG_SYSTEM_PRIVACYCONSENT_SUBJECT'))
            ->where($db->quoteName('remind') . ' = 0')
            ->where($query->dateAdd($db->quote($now), $period, 'DAY') . ' > ' . $db->quoteName('created'));

        try {
            $users = $db->setQuery($query)->loadObjectList();
        } catch (ExecutionFailureException $exception) {
            return false;
        }

        $app      = Factory::getApplication();
        $linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

        foreach ($users as $user) {
            $token       = ApplicationHelper::getHash(UserHelper::genRandomPassword());
            $hashedToken = UserHelper::hashPassword($token);

            // The mail
            try {
                $templateData = [
                    'sitename' => $app->get('sitename'),
                    'url'      => Uri::root(),
                    'tokenurl' => Route::link('site', 'index.php?option=com_privacy&view=remind&remind_token=' . $token, false, $linkMode, true),
                    'formurl'  => Route::link('site', 'index.php?option=com_privacy&view=remind', false, $linkMode, true),
                    'token'    => $token,
                ];

                $mailer = new MailTemplate('plg_system_privacyconsent.request.reminder', $app->getLanguage()->getTag());
                $mailer->addTemplateData($templateData);
                $mailer->addRecipient($user->email);

                $mailResult = $mailer->send();

                if ($mailResult === false) {
                    return false;
                }

                $userId = (int) $user->id;

                // Update the privacy_consents item to not send the reminder again
                $query->clear()
                    ->update($db->quoteName('#__privacy_consents'))
                    ->set($db->quoteName('remind') . ' = 1')
                    ->set($db->quoteName('token') . ' = :token')
                    ->where($db->quoteName('id') . ' = :userid')
                    ->bind(':token', $hashedToken)
                    ->bind(':userid', $userId, ParameterType::INTEGER);
                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (RuntimeException $e) {
                    return false;
                }
            } catch (MailDisabledException | phpmailerException $exception) {
                return false;
            }
        }
    }

    /**
     * Method to delete the expired privacy consents
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    private function invalidateExpiredConsents()
    {
        // Load the parameters.
        $expire = (int) $this->params->get('consentexpiration', 365);
        $now    = Factory::getDate()->toSql();
        $period = '-' . $expire;
        $db     = $this->db;
        $query  = $db->getQuery(true);

        $query->select($db->quoteName(['id', 'user_id']))
            ->from($db->quoteName('#__privacy_consents'))
            ->where($query->dateAdd($db->quote($now), $period, 'DAY') . ' > ' . $db->quoteName('created'))
            ->where($db->quoteName('subject') . ' = ' . $db->quote('PLG_SYSTEM_PRIVACYCONSENT_SUBJECT'))
            ->where($db->quoteName('state') . ' = 1');

        $db->setQuery($query);

        try {
            $users = $db->loadObjectList();
        } catch (RuntimeException $e) {
            return false;
        }

        // Do not process further if no expired consents found
        if (empty($users)) {
            return true;
        }

        // Push a notification to the site's super users
        /** @var MessageModel $messageModel */
        $messageModel = $this->app->bootComponent('com_messages')->getMVCFactory()->createModel('Message', 'Administrator');

        foreach ($users as $user) {
            $userId = (int) $user->id;
            $query  = $db->getQuery(true)
                ->update($db->quoteName('#__privacy_consents'))
                ->set($db->quoteName('state') . ' = 0')
                ->where($db->quoteName('id') . ' = :userid')
                ->bind(':userid', $userId, ParameterType::INTEGER);
            $db->setQuery($query);

            try {
                $db->execute();
            } catch (RuntimeException $e) {
                return false;
            }

            $messageModel->notifySuperUsers(
                Text::_('PLG_SYSTEM_PRIVACYCONSENT_NOTIFICATION_USER_PRIVACY_EXPIRED_SUBJECT'),
                Text::sprintf('PLG_SYSTEM_PRIVACYCONSENT_NOTIFICATION_USER_PRIVACY_EXPIRED_MESSAGE', Factory::getUser($user->user_id)->username)
            );
        }

        return true;
    }
    /**
     * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
     *
     * @param   array  $clearGroups   The cache groups to clean
     * @param   array  $cacheClients  The cache clients (site, admin) to clean
     *
     * @return  void
     *
     * @since    3.9.0
     */
    private function clearCacheGroups(array $clearGroups, array $cacheClients = [0, 1])
    {
        foreach ($clearGroups as $group) {
            foreach ($cacheClients as $client_id) {
                try {
                    $options = [
                        'defaultgroup' => $group,
                        'cachebase'    => $client_id ? JPATH_ADMINISTRATOR . '/cache' :
                            Factory::getApplication()->get('cache_path', JPATH_SITE . '/cache'),
                    ];

                    $cache = Cache::getInstance('callback', $options);
                    $cache->clean();
                } catch (Exception $e) {
                    // Ignore it
                }
            }
        }
    }
}
