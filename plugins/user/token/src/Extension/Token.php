<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  User.token
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\User\Token\Extension;

use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Event\Model\PrepareDataEvent;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Event\User\AfterDeleteEvent;
use Joomla\CMS\Event\User\AfterSaveEvent;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * An example custom terms and conditions plugin.
 *
 * @since  3.9.0
 */
final class Token extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use UserFactoryAwareTrait;

    /**
     * Joomla XML form contexts where we should inject our token management user interface.
     *
     * @var     array
     * @since   4.0.0
     */
    private $allowedContexts = [
        'com_users.profile',
        'com_users.user',
    ];

    /**
     * The prefix of the user profile keys, without the dot.
     *
     * @var     string
     * @since   4.0.0
     */
    private $profileKeyPrefix = 'joomlatoken';

    /**
     * Token length, in bytes.
     *
     * @var     integer
     * @since   4.0.0
     */
    private $tokenLength = 32;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onContentPrepareData' => 'onContentPrepareData',
            'onContentPrepareForm' => 'onContentPrepareForm',
            'onUserAfterSave'      => 'onUserAfterSave',
            'onUserAfterDelete'    => 'onUserAfterDelete',
        ];
    }

    /**
     * Inject the Joomla token management panel's data into the User Profile.
     *
     * This method is called whenever Joomla is preparing the data for an XML form for display.
     *
     * @param   PrepareDataEvent $event  The event instance.
     *
     * @return  void
     * @since   4.0.0
     */
    public function onContentPrepareData(PrepareDataEvent $event): void
    {
        // Only do something if the api-authentication plugin with the same name is published
        if (!PluginHelper::isEnabled('api-authentication', $this->_name)) {
            return;
        }

        $context = $event->getContext();
        $data    = $event->getData();

        // Check we are manipulating a valid form.
        if (!\in_array($context, $this->allowedContexts)) {
            return;
        }

        // $data must be an object
        if (!\is_object($data)) {
            return;
        }

        // We expect the numeric user ID in $data->id
        if (!isset($data->id)) {
            return;
        }

        // Get the user ID
        $userId = \intval($data->id);

        // Make sure we have a positive integer user ID
        if ($userId <= 0) {
            return;
        }

        if (!$this->isInAllowedUserGroup($userId)) {
            return;
        }

        // Load plugin language files
        $this->loadLanguage();

        $data->{$this->profileKeyPrefix} = [];

        // Load the profile data from the database.
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select([
                        $db->quoteName('profile_key'),
                        $db->quoteName('profile_value'),
                    ])
                ->from($db->quoteName('#__user_profiles'))
                ->where($db->quoteName('user_id') . ' = :userId')
                ->where($db->quoteName('profile_key') . ' LIKE :profileKey')
                ->order($db->quoteName('ordering'));

            $profileKey = $this->profileKeyPrefix . '.%';
            $query->bind(':userId', $userId, ParameterType::INTEGER);
            $query->bind(':profileKey', $profileKey, ParameterType::STRING);

            $results = $db->setQuery($query)->loadRowList();

            foreach ($results as $v) {
                $k = str_replace($this->profileKeyPrefix . '.', '', $v[0]);

                $data->{$this->profileKeyPrefix}[$k] = $v[1];
            }
        } catch (\Exception $e) {
            // We suppress any database error. It means we get no token saved by default.
        }

        /**
         * Modify the data for display in the user profile view page in the frontend.
         *
         * It's important to note that we deliberately not register HTMLHelper methods to do the
         * same (unlike e.g. the actionlogs system plugin) because the names of our fields are too
         * generic and we run the risk of creating naming clashes. Instead, we manipulate the data
         * directly.
         */
        if (($context === 'com_users.profile') && ($this->getApplication()->getInput()->get('layout') !== 'edit')) {
            $pluginData = $data->{$this->profileKeyPrefix} ?? [];
            $enabled    = $pluginData['enabled'] ?? false;
            $token      = $pluginData['token'] ?? '';

            $pluginData['enabled'] = $this->getApplication()->getLanguage()->_('JDISABLED');
            $pluginData['token']   = '';

            if ($enabled) {
                $algo                  = $this->getAlgorithmFromFormFile();
                $pluginData['enabled'] = $this->getApplication()->getLanguage()->_('JENABLED');
                $pluginData['token']   = $this->getTokenForDisplay($userId, $token, $algo);
            }

            $data->{$this->profileKeyPrefix} = $pluginData;
        }
    }

    /**
     * Runs whenever Joomla is preparing a form object.
     *
     * @param   PrepareFormEvent $event  The event instance.
     *
     * @return  void
     *
     * @throws  \Exception  When $form is not a valid form object
     * @since   4.0.0
     */
    public function onContentPrepareForm(PrepareFormEvent $event): void
    {
        // Only do something if the api-authentication plugin with the same name is published
        if (!PluginHelper::isEnabled('api-authentication', $this->_name)) {
            return;
        }

        $form = $event->getForm();
        $data = $event->getData();

        // Check we are manipulating a valid form.
        if (!\in_array($form->getName(), $this->allowedContexts)) {
            return;
        }

        // If we are on the save command, no data is passed to $data variable, we need to get it directly from request
        $jformData = $this->getApplication()->getInput()->get('jform', [], 'array');

        if ($jformData && !$data) {
            $data = $jformData;
        }

        if (\is_array($data)) {
            $data = (object) $data;
        }

        // Check if the user belongs to an allowed user group
        $userId = (\is_object($data) && isset($data->id)) ? $data->id : 0;

        if (!empty($userId) && !$this->isInAllowedUserGroup($userId)) {
            return;
        }

        // Load plugin language files
        $this->loadLanguage();

        // Add the registration fields to the form.
        Form::addFormPath(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms');
        $form->loadFile('token', false);

        // No token: no reset
        $userTokenSeed = $this->getTokenSeedForUser($userId);
        $currentUser   = $this->getApplication()->getIdentity();

        if (empty($userTokenSeed)) {
            $form->removeField('notokenforotherpeople', 'joomlatoken');
            $form->removeField('reset', 'joomlatoken');
            $form->removeField('token', 'joomlatoken');
            $form->removeField('enabled', 'joomlatoken');
        } else {
            $form->removeField('saveme', 'joomlatoken');
        }

        if ($userId != $currentUser->id) {
            $form->removeField('token', 'joomlatoken');
        } else {
            $form->removeField('notokenforotherpeople', 'joomlatoken');
        }

        if (($userId != $currentUser->id) && empty($userTokenSeed)) {
            $form->removeField('saveme', 'joomlatoken');
        } else {
            $form->removeField('savemeforotherpeople', 'joomlatoken');
        }

        // Remove the Reset field when displaying the user profile form
        if (($form->getName() === 'com_users.profile') && ($this->getApplication()->getInput()->get('layout') !== 'edit')) {
            $form->removeField('reset', 'joomlatoken');
        }
    }

    /**
     * Save the Joomla token in the user profile field
     *
     * @param   AfterSaveEvent $event  The event instance.
     *
     * @return  void
     * @since   4.0.0
     */
    public function onUserAfterSave(AfterSaveEvent $event): void
    {
        $data   = $event->getUser();
        $isNew  = $event->getIsNew();
        $result = $event->getSavingResult();

        if (!\is_array($data)) {
            return;
        }

        $userId = ArrayHelper::getValue($data, 'id', 0, 'int');

        if ($userId <= 0) {
            return;
        }

        if (!$result) {
            return;
        }

        $noToken = false;

        // No Joomla token data. Set the $noToken flag which results in a new token being generated.
        if (!isset($data[$this->profileKeyPrefix])) {
            /**
             * Is the user being saved programmatically, without passing the user profile
             * information? In this case I do not want to accidentally try to generate a new token!
             *
             * We determine that by examining whether the Joomla token field exists. If it does but
             * it wasn't passed when saving the user I know it's a programmatic user save and I have
             * to ignore it.
             */
            if ($this->hasTokenProfileFields($userId)) {
                return;
            }

            $noToken                       = true;
            $data[$this->profileKeyPrefix] = [];
        }

        if (isset($data[$this->profileKeyPrefix]['reset'])) {
            $reset = $data[$this->profileKeyPrefix]['reset'] == 1;
            unset($data[$this->profileKeyPrefix]['reset']);

            if ($reset) {
                $noToken = true;
            }
        }

        // We may have a token already saved. Let's check, shall we?
        if (!$noToken) {
            $noToken       = true;
            $existingToken = $this->getTokenSeedForUser($userId);

            if (!empty($existingToken)) {
                $noToken                                = false;
                $data[$this->profileKeyPrefix]['token'] = $existingToken;
            }
        }

        // If there is no token or this is a new user generate a new token.
        if ($noToken || $isNew) {
            if (
                isset($data[$this->profileKeyPrefix]['token'])
                && empty($data[$this->profileKeyPrefix]['token'])
            ) {
                unset($data[$this->profileKeyPrefix]['token']);
            }

            $default                       = $this->getDefaultProfileFieldValues();
            $data[$this->profileKeyPrefix] = array_merge($default, $data[$this->profileKeyPrefix]);
        }

        // Remove existing Joomla Token user profile values
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__user_profiles'))
            ->where($db->quoteName('user_id') . ' = :userId')
            ->where($db->quoteName('profile_key') . ' LIKE :profileKey');

        $profileKey = $this->profileKeyPrefix . '.%';
        $query->bind(':userId', $userId, ParameterType::INTEGER);
        $query->bind(':profileKey', $profileKey, ParameterType::STRING);

        $db->setQuery($query)->execute();

        // If the user is not in the allowed user group don't save any new token information.
        if (!$this->isInAllowedUserGroup($data['id'])) {
            return;
        }

        // Save the new Joomla Token user profile values
        $order = 1;
        $query = $db->getQuery(true)
            ->insert($db->quoteName('#__user_profiles'))
            ->columns([
                    $db->quoteName('user_id'),
                    $db->quoteName('profile_key'),
                    $db->quoteName('profile_value'),
                    $db->quoteName('ordering'),
                ]);

        foreach ($data[$this->profileKeyPrefix] as $k => $v) {
            $query->values($userId . ', '
                . $db->quote($this->profileKeyPrefix . '.' . $k)
                . ', ' . $db->quote($v)
                . ', ' . ($order++));
        }

        $db->setQuery($query)->execute();
    }

    /**
     * Remove the Joomla token when the user account is deleted from the database.
     *
     * This event is called after the user data is deleted from the database.
     *
     * @param   AfterDeleteEvent $event  The event instance.
     *
     * @return  void
     *
     * @throws  \Exception
     * @since   4.0.0
     */
    public function onUserAfterDelete(AfterDeleteEvent $event): void
    {
        $user    = $event->getUser();
        $success = $event->getDeletingResult();

        if (!$success) {
            return;
        }

        $userId = ArrayHelper::getValue($user, 'id', 0, 'int');

        if ($userId <= 0) {
            return;
        }

        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__user_profiles'))
                ->where($db->quoteName('user_id') . ' = :userId')
                ->where($db->quoteName('profile_key') . ' LIKE :profileKey');

            $profileKey = $this->profileKeyPrefix . '.%';
            $query->bind(':userId', $userId, ParameterType::INTEGER);
            $query->bind(':profileKey', $profileKey, ParameterType::STRING);

            $db->setQuery($query)->execute();
        } catch (\Exception $e) {
            // Do nothing.
        }
    }

    /**
     * Returns an array with the default profile field values.
     *
     * This is used when saving the form data of a user (new or existing) without a token already
     * set.
     *
     * @return  array
     * @since   4.0.0
     */
    private function getDefaultProfileFieldValues(): array
    {
        return [
            'token'   => base64_encode(Crypt::genRandomBytes($this->tokenLength)),
            'enabled' => true,
        ];
    }

    /**
     * Retrieve the token seed string for the given user ID.
     *
     * @param   int  $userId  The numeric user ID to return the token seed string for.
     *
     * @return  string|null  Null if there is no token configured or the user doesn't exist.
     * @since   4.0.0
     */
    private function getTokenSeedForUser(int $userId): ?string
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('profile_value'))
                ->from($db->quoteName('#__user_profiles'))
                ->where($db->quoteName('profile_key') . ' = :profileKey')
                ->where($db->quoteName('user_id') . ' = :userId');

            $profileKey = $this->profileKeyPrefix . '.token';
            $query->bind(':profileKey', $profileKey, ParameterType::STRING);
            $query->bind(':userId', $userId, ParameterType::INTEGER);

            return $db->setQuery($query)->loadResult();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the configured user groups which are allowed to have access to tokens.
     *
     * @return  int[]
     * @since   4.0.0
     */
    private function getAllowedUserGroups(): array
    {
        $userGroups = $this->params->get('allowedUserGroups', [8]);

        if (empty($userGroups)) {
            return [];
        }

        if (!\is_array($userGroups)) {
            $userGroups = [$userGroups];
        }

        return $userGroups;
    }

    /**
     * Is the user with the given ID in the allowed User Groups with access to tokens?
     *
     * @param   int  $userId  The user ID to check
     *
     * @return  boolean  False when doesn't belong to allowed user groups, user not found, or guest
     * @since   4.0.0
     */
    private function isInAllowedUserGroup($userId)
    {
        $allowedUserGroups = $this->getAllowedUserGroups();

        $user = $this->getUserFactory()->loadUserById($userId);

        if ($user->id != $userId) {
            return false;
        }

        if ($user->guest) {
            return false;
        }

        // No specifically allowed user groups: allow ALL user groups.
        if (empty($allowedUserGroups)) {
            return true;
        }

        $groups       = $user->getAuthorisedGroups();
        $intersection = array_intersect($groups, $allowedUserGroups);

        return !empty($intersection);
    }

    /**
     * Returns the token formatted suitably for the user to copy.
     *
     * @param   integer  $userId     The user id for token
     * @param   string   $tokenSeed  The token seed data stored in the database
     * @param   string   $algorithm  The hashing algorithm to use for the token (default: sha256)
     *
     * @return  string
     * @since   4.0.0
     */
    private function getTokenForDisplay(
        int $userId,
        string $tokenSeed,
        string $algorithm = 'sha256'
    ): string {
        if (empty($tokenSeed)) {
            return '';
        }

        try {
            $siteSecret = $this->getApplication()->get('secret');
        } catch (\Exception $e) {
            $siteSecret = '';
        }

        // NO site secret? You monster!
        if (empty($siteSecret)) {
            return '';
        }

        $rawToken  = base64_decode($tokenSeed);
        $tokenHash = hash_hmac($algorithm, $rawToken, $siteSecret);
        $message   = base64_encode("$algorithm:$userId:$tokenHash");

        if ($userId !== $this->getApplication()->getIdentity()->id) {
            $message = '';
        }

        return $message;
    }

    /**
     * Get the token algorithm as defined in the form file
     *
     * We use a simple RegEx match instead of loading the form for better performance.
     *
     * @return  string  The configured algorithm, 'sha256' as a fallback if none is found.
     */
    private function getAlgorithmFromFormFile(): string
    {
        $algo = 'sha256';

        $file     = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms/token.xml';
        $contents = @file_get_contents($file);

        if ($contents === false) {
            return $algo;
        }

        if (preg_match('/\s*algo=\s*"\s*([a-z0-9]+)\s*"/i', $contents, $matches) !== 1) {
            return $algo;
        }

        return $matches[1];
    }

    /**
     * Does the user have the Joomla Token profile fields?
     *
     * @param   int|null  $userId  The user we're interested in
     *
     * @return  bool  True if the user has Joomla Token profile fields
     */
    private function hasTokenProfileFields(?int $userId): bool
    {
        if (\is_null($userId) || ($userId <= 0)) {
            return false;
        }

        $db = $this->getDatabase();
        $q  = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__user_profiles'))
            ->where($db->quoteName('user_id') . ' = ' . $userId)
            ->where($db->quoteName('profile_key') . ' = ' . $db->quote($this->profileKeyPrefix . '.token'));

        try {
            $numRows = $db->setQuery($q)->loadResult() ?? 0;
        } catch (\Exception $e) {
            return false;
        }

        return $numRows > 0;
    }
}
