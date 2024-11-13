<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.privacyconsent
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\PrivacyConsent\Extension;

use Joomla\CMS\Event\Privacy\CheckPrivacyPolicyPublishedEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * An example custom privacyconsent plugin.
 *
 * @since  3.9.0
 */
final class PrivacyConsent extends CMSPlugin
{
    use DatabaseAwareTrait;

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

        if (!\in_array($name, ['com_users.profile', 'com_users.registration'])) {
            return true;
        }

        // Load plugin language files
        $this->loadLanguage();

        // We only display this if user has not consented before
        if (\is_object($data)) {
            $userId = $data->id ?? 0;

            if ($userId > 0 && $this->isUserConsented($userId)) {
                return true;
            }
        }

        // Add the privacy policy fields to the form.
        FormHelper::addFieldPrefix('Joomla\\Plugin\\System\\PrivacyConsent\\Field');
        FormHelper::addFormPath(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms');
        $form->loadFile('privacyconsent');

        $privacyType = $this->params->get('privacy_type', 'article');
        $privacyId   = ($privacyType == 'menu_item') ? $this->getPrivacyItemId() : $this->getPrivacyArticleId();
        $privacynote = $this->params->get('privacy_note');

        // Push the privacy article ID into the privacy field.
        $form->setFieldAttribute('privacy', $privacyType, $privacyId, 'privacyconsent');
        $form->setFieldAttribute('privacy', 'note', $privacynote, 'privacyconsent');

        return true;
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
     * @throws  \InvalidArgumentException on missing required data.
     */
    public function onUserBeforeSave($user, $isNew, $data)
    {
        // // Only check for front-end user creation/update profile
        if ($this->getApplication()->isClient('administrator')) {
            return true;
        }

        $userId = ArrayHelper::getValue($user, 'id', 0, 'int');

        // Load plugin language files
        $this->loadLanguage();

        // User already consented before, no need to check it further
        if ($userId > 0 && $this->isUserConsented($userId)) {
            return true;
        }

        // Check that the privacy is checked if required ie only in registration from frontend.
        $input  = $this->getApplication()->getInput();
        $option = $input->get('option');
        $task   = $input->post->get('task');
        $form   = $input->post->get('jform', [], 'array');

        if (
            $option == 'com_users' && \in_array($task, ['registration.register', 'profile.save'])
            && empty($form['privacyconsent']['privacy'])
        ) {
            throw new \InvalidArgumentException($this->getApplication()->getLanguage()->_('PLG_SYSTEM_PRIVACYCONSENT_FIELD_ERROR'));
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
        if ($this->getApplication()->isClient('administrator')) {
            return;
        }

        // Get the user's ID
        $userId = ArrayHelper::getValue($data, 'id', 0, 'int');

        // If user already consented before, no need to check it further
        if ($userId > 0 && $this->isUserConsented($userId)) {
            return;
        }

        $input  = $this->getApplication()->getInput();
        $option = $input->get('option');
        $task   = $input->post->get('task');
        $form   = $input->post->get('jform', [], 'array');

        if (
            $option == 'com_users'
            && \in_array($task, ['registration.register', 'profile.save'])
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
                $this->getDatabase()->insertObject('#__privacy_consents', $userNote);
            } catch (\Exception $e) {
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
            $model = $this->getApplication()->bootComponent('com_actionlogs')->getMVCFactory()->createModel('Actionlog', 'Administrator');
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
            $query = $this->getDatabase()->getQuery(true)
                ->delete($this->getDatabase()->quoteName('#__privacy_consents'))
                ->where($this->getDatabase()->quoteName('user_id') . ' = :userid')
                ->bind(':userid', $userId, ParameterType::INTEGER);
            $this->getDatabase()->setQuery($query);
            $this->getDatabase()->execute();
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
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        $userId = $this->getApplication()->getIdentity()->id;

        // Check to see whether user already consented, if not, redirect to user profile page
        if ($userId > 0) {
            // Load plugin language files
            $this->loadLanguage();

            // If user consented before, no need to check it further
            if ($this->isUserConsented($userId)) {
                return;
            }

            $input  = $this->getApplication()->getInput();
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
            $isAllowedUserTask = \in_array($task, $allowedUserTasks)
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
            $this->getApplication()->enqueueMessage($this->getRedirectMessage(), 'notice');
            $link = 'index.php?option=com_users&view=profile&layout=edit';
            $this->getApplication()->redirect(Route::_($link, false));
        }
    }

    /**
     * Event to specify whether a privacy policy has been published.
     *
     * @param   CheckPrivacyPolicyPublishedEvent  $event  The privacy policy status event.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function onPrivacyCheckPrivacyPolicyPublished(CheckPrivacyPolicyPublishedEvent $event)
    {
        // Data, with keys "published", "editLink" and "articlePublished".
        $policy = $event->getPolicyInfo();

        // If another plugin has already indicated a policy is published, we won't change anything here
        if ($policy['published']) {
            return;
        }

        $articleId = (int) $this->params->get('privacy_article');

        if (!$articleId) {
            return;
        }

        // Check if the article exists in database and is published
        $query = $this->getDatabase()->getQuery(true)
            ->select($this->getDatabase()->quoteName(['id', 'state']))
            ->from($this->getDatabase()->quoteName('#__content'))
            ->where($this->getDatabase()->quoteName('id') . ' = :id')
            ->bind(':id', $articleId, ParameterType::INTEGER);
        $this->getDatabase()->setQuery($query);

        $article = $this->getDatabase()->loadObject();

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

        $event->updatePolicyInfo($policy);
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
            return $this->getApplication()->getLanguage()->_('PLG_SYSTEM_PRIVACYCONSENT_REDIRECT_MESSAGE_DEFAULT');
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
        $db     = $this->getDatabase();
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
            $currentLang       = $this->getApplication()->getLanguage()->getTag();

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
            $currentLang       = $this->getApplication()->getLanguage()->getTag();

            if (isset($privacyAssociated[$currentLang])) {
                $itemId = $privacyAssociated[$currentLang]->id;
            }
        }

        return $itemId;
    }
}
