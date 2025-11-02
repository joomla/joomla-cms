<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Webauthn
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Webauthn;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Encrypt\Aes;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Database\DatabaseAwareInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Plugin\System\Webauthn\Extension\Webauthn;
use Joomla\Registry\Registry;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Handles the storage of WebAuthn credentials in the database
 *
 * @since   4.0.0
 */
final class CredentialRepository implements PublicKeyCredentialSourceRepository, DatabaseAwareInterface
{
    use DatabaseAwareTrait;

    /**
     * Public constructor.
     *
     * @param   ?DatabaseInterface  $db  The database driver object to use for persistence.
     *
     * @since   4.2.0
     */
    public function __construct(?DatabaseInterface $db = null)
    {
        $this->setDatabase($db);
    }

    /**
     * Returns a PublicKeyCredentialSource object given the public key credential ID
     *
     * @param   string  $publicKeyCredentialId  The identified of the public key credential we're searching for
     *
     * @return  PublicKeyCredentialSource|null
     *
     * @since   4.0.0
     */
    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        /** @var DatabaseInterface $db */
        $db           = $this->getDatabase();
        $credentialId = base64_encode($publicKeyCredentialId);
        $query        = $db->getQuery(true)
            ->select($db->quoteName('credential'))
            ->from($db->quoteName('#__webauthn_credentials'))
            ->where($db->quoteName('id') . ' = :credentialId')
            ->bind(':credentialId', $credentialId);

        $encrypted = $db->setQuery($query)->loadResult();

        if (empty($encrypted)) {
            return null;
        }

        $json = $this->decryptCredential($encrypted);

        try {
            return PublicKeyCredentialSource::createFromArray(json_decode($json, true));
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Returns all PublicKeyCredentialSource objects given a user entity. We only use the `id` property of the user
     * entity, cast to integer, as the Joomla user ID by which records are keyed in the database table.
     *
     * @param   PublicKeyCredentialUserEntity  $publicKeyCredentialUserEntity  Public key credential user entity record
     *
     * @return  PublicKeyCredentialSource[]
     *
     * @since  4.0.0
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        /** @var DatabaseInterface $db */
        $db         = $this->getDatabase();
        $userHandle = $publicKeyCredentialUserEntity->getId();
        $query      = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__webauthn_credentials'))
            ->where($db->quoteName('user_id') . ' = :user_id')
            ->bind(':user_id', $userHandle);

        try {
            $records = $db->setQuery($query)->loadAssocList();
        } catch (\Exception $e) {
            return [];
        }

        /**
         * Converts invalid credential records to PublicKeyCredentialSource objects, or null if they
         * are invalid.
         *
         * This closure is defined as a variable to prevent PHP-CS from getting a stoke trying to
         * figure out the correct indentation :)
         *
         * @param   array  $record  The record to convert
         *
         * @return  PublicKeyCredentialSource|null
         */
        $recordsMapperClosure = function ($record) {
            try {
                $json = $this->decryptCredential($record['credential']);
                $data = json_decode($json, true);
            } catch (\JsonException $e) {
                return null;
            }

            if (empty($data)) {
                return null;
            }

            try {
                return PublicKeyCredentialSource::createFromArray($data);
            } catch (\InvalidArgumentException) {
                return null;
            }
        };

        $records = array_map($recordsMapperClosure, $records);

        /**
         * Filters the list of records to only keep valid entries.
         *
         * Only array members that are PublicKeyCredentialSource objects survive the filter.
         *
         * This closure is defined as a variable to prevent PHP-CS from getting a stoke trying to
         * figure out the correct indentation :)
         *
         * @param  PublicKeyCredentialSource|mixed  $record  The record to filter
         *
         * @return boolean
         */
        $filterClosure = function ($record) {
            return !\is_null($record) && \is_object($record) && ($record instanceof PublicKeyCredentialSource);
        };

        return array_filter($records, $filterClosure);
    }

    /**
     * Add or update an attested credential for a given user.
     *
     * @param   PublicKeyCredentialSource  $publicKeyCredentialSource  The public key credential
     *                                                                 source to store
     *
     * @return  void
     *
     * @throws \Exception
     * @since   4.0.0
     */
    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        // Default values for saving a new credential source
        /** @var Webauthn $plugin */
        $plugin              = Factory::getApplication()->bootPlugin('webauthn', 'system');
        $knownAuthenticators = $plugin->getAuthenticationHelper()->getKnownAuthenticators();
        $aaguid              = (string) ($publicKeyCredentialSource->getAaguid() ?? '');
        $defaultName         = ($knownAuthenticators[$aaguid] ?? $knownAuthenticators[''])->description;
        $credentialId        = base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId());
        $user                = Factory::getApplication()->getIdentity();
        $o                   = (object) [
            'id'      => $credentialId,
            'user_id' => $this->getHandleFromUserId($user->id),
            'label'   => Text::sprintf(
                'PLG_SYSTEM_WEBAUTHN_LBL_DEFAULT_AUTHENTICATOR_LABEL',
                $defaultName,
                $this->formatDate('now')
            ),
            'credential' => json_encode($publicKeyCredentialSource),
        ];
        $update              = false;

        /** @var DatabaseInterface $db */
        $db = $this->getDatabase();

        // Try to find an existing record
        try {
            $query     = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__webauthn_credentials'))
                ->where($db->quoteName('id') . ' = :credentialId')
                ->bind(':credentialId', $credentialId);
            $oldRecord = $db->setQuery($query)->loadObject();

            if (\is_null($oldRecord)) {
                throw new \Exception('This is a new record');
            }

            /**
             * Sanity check. The existing credential source must have the same user handle as the one I am trying to
             * save. Otherwise something fishy is going on.
             */
            if ($oldRecord->user_id != $publicKeyCredentialSource->getUserHandle()) {
                throw new \RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CREDENTIAL_ID_ALREADY_IN_USE'));
            }

            $o->user_id = $oldRecord->user_id;
            $o->label   = $oldRecord->label;
            $update     = true;
        } catch (\Exception) {
        }

        $o->credential = $this->encryptCredential($o->credential);

        if ($update) {
            $db->updateObject('#__webauthn_credentials', $o, ['id']);

            return;
        }

        /**
         * This check is deliberately skipped for updates. When logging in the underlying library will try to save the
         * credential source. This is necessary to update the last known authenticator signature counter which prevents
         * replay attacks. When we are saving a new record, though, we have to make sure we are not a guest user. Hence
         * the check below.
         */
        if ((\is_null($user) || $user->guest)) {
            throw new \RuntimeException(Text::_('PLG_SYSTEM_WEBAUTHN_ERR_CANT_STORE_FOR_GUEST'));
        }

        $db->insertObject('#__webauthn_credentials', $o);
    }

    /**
     * Get all credential information for a given user ID. This is meant to only be used for displaying records.
     *
     * @param   int  $userId  The user ID
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getAll(int $userId): array
    {
        /** @var DatabaseInterface $db */
        $db         = $this->getDatabase();
        $userHandle = $this->getHandleFromUserId($userId);
        $query      = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__webauthn_credentials'))
            ->where($db->quoteName('user_id') . ' = :user_id')
            ->bind(':user_id', $userHandle);

        try {
            $results = $db->setQuery($query)->loadAssocList();
        } catch (\Exception $e) {
            return [];
        }

        if (empty($results)) {
            return [];
        }

        /**
         * Decodes the credentials on each record.
         *
         * @param   array  $record  The record to convert
         *
         * @return  array
         * @since   4.2.0
         */
        $recordsMapperClosure = function ($record) {
            try {
                $json = $this->decryptCredential($record['credential']);
                $data = json_decode($json, true);
            } catch (\JsonException $e) {
                $record['credential'] = null;

                return $record;
            }

            if (empty($data)) {
                $record['credential'] = null;

                return $record;
            }

            try {
                $record['credential'] = PublicKeyCredentialSource::createFromArray($data);

                return $record;
            } catch (\InvalidArgumentException) {
                $record['credential'] = null;

                return $record;
            }
        };

        return array_map($recordsMapperClosure, $results);
    }

    /**
     * Do we have stored credentials under the specified Credential ID?
     *
     * @param   string  $credentialId  The ID of the credential to check for existence
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function has(string $credentialId): bool
    {
        /** @var DatabaseInterface $db */
        $db           = $this->getDatabase();
        $credentialId = base64_encode($credentialId);
        $query        = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__webauthn_credentials'))
            ->where($db->quoteName('id') . ' = :credentialId')
            ->bind(':credentialId', $credentialId);

        try {
            $count = $db->setQuery($query)->loadResult();

            return $count > 0;
        } catch (\Exception) {
            return false;
        }
    }

    /**
     * Update the human readable label of a credential
     *
     * @param   string  $credentialId  The credential ID
     * @param   string  $label         The human readable label to set
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function setLabel(string $credentialId, string $label): void
    {
        /** @var DatabaseInterface $db */
        $db           = $this->getDatabase();
        $credentialId = base64_encode($credentialId);
        $o            = (object) [
            'id'    => $credentialId,
            'label' => $label,
        ];

        $db->updateObject('#__webauthn_credentials', $o, ['id'], false);
    }

    /**
     * Remove stored credentials
     *
     * @param   string  $credentialId  The credentials ID to remove
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function remove(string $credentialId): void
    {
        if (!$this->has($credentialId)) {
            return;
        }

        /** @var DatabaseInterface $db */
        $db           = $this->getDatabase();
        $credentialId = base64_encode($credentialId);
        $query        = $db->getQuery(true)
            ->delete($db->quoteName('#__webauthn_credentials'))
            ->where($db->quoteName('id') . ' = :credentialId')
            ->bind(':credentialId', $credentialId);

        $db->setQuery($query)->execute();
    }

    /**
     * Return the user handle for the stored credential given its ID.
     *
     * The user handle must not be personally identifiable. Per https://w3c.github.io/webauthn/#user-handle it is
     * acceptable to have a salted hash with a salt private to our server, e.g. Joomla's secret. The only immutable
     * information in Joomla is the user ID so that's what we will be using.
     *
     * @param   string  $credentialId  The credential ID to get the user handle for
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function getUserHandleFor(string $credentialId): string
    {
        $publicKeyCredentialSource = $this->findOneByCredentialId($credentialId);

        if (empty($publicKeyCredentialSource)) {
            return '';
        }

        return $publicKeyCredentialSource->getUserHandle();
    }

    /**
     * Return a user handle given an integer Joomla user ID. We use the HMAC-SHA-256 of the user ID with the site's
     * secret as the key. Using it instead of SHA-512 is on purpose! WebAuthn only allows user handles up to 64 bytes
     * long.
     *
     * @param   int  $id  The user ID to convert
     *
     * @return  string  The user handle (HMAC-SHA-256 of the user ID)
     *
     * @since   4.0.0
     */
    public function getHandleFromUserId(int $id): string
    {
        $key  = $this->getEncryptionKey();
        $data = \sprintf('%010u', $id);

        return hash_hmac('sha256', $data, $key, false);
    }

    /**
     * Get the user ID from the user handle
     *
     * This is a VERY inefficient method. Since the user handle is an HMAC-SHA-256 of the user ID we can't just go
     * directly from a handle back to an ID. We have to iterate all user IDs, calculate their handles and compare them
     * to the given handle.
     *
     * To prevent a lengthy infinite loop in case of an invalid user handle we don't iterate the entire 2+ billion valid
     * 32-bit integer range. We load the user IDs of active users (not blocked, not pending activation) and iterate
     * through them.
     *
     * To avoid memory outage on large sites with thousands of active user records we load up to 10000 users at a time.
     * Each block of 10,000 user IDs takes about 60-80 msec to iterate. On a site with 200,000 active users this method
     * will take less than 1.5 seconds. This is slow but not impractical, even on crowded shared hosts with a quarter of
     * the performance of my test subject (a mid-range, shared hosting server).
     *
     * @param   string|null  $userHandle  The user handle which will be converted to a user ID.
     *
     * @return  integer|null
     * @since   4.2.0
     */
    public function getUserIdFromHandle(?string $userHandle): ?int
    {
        if (empty($userHandle)) {
            return null;
        }

        /** @var DatabaseInterface $db */
        $db = $this->getDatabase();

        // Check that the userHandle does exist in the database
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__webauthn_credentials'))
            ->where($db->quoteName('user_id') . ' = ' . $db->q($userHandle));

        try {
            $numRecords = $db->setQuery($query)->loadResult();
        } catch (\Exception) {
            return null;
        }

        if (\is_null($numRecords) || ($numRecords < 1)) {
            return null;
        }

        // Prepare the query
        $query = $db->getQuery(true)
            ->select([$db->quoteName('id')])
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('block') . ' = 0')
            ->where(
                '(' .
                $db->quoteName('activation') . ' IS NULL OR ' .
                $db->quoteName('activation') . ' = 0 OR ' .
                $db->quoteName('activation') . ' = ' . $db->q('') .
                ')'
            );

        $key   = $this->getEncryptionKey();
        $start = 0;
        $limit = 10000;

        while (true) {
            try {
                $ids = $db->setQuery($query, $start, $limit)->loadColumn();
            } catch (\Exception) {
                return null;
            }

            if (empty($ids)) {
                return null;
            }

            foreach ($ids as $userId) {
                $data       = \sprintf('%010u', $userId);
                $thisHandle = hash_hmac('sha256', $data, $key, false);

                if ($thisHandle == $userHandle) {
                    return $userId;
                }
            }

            $start += $limit;
        }
    }

    /**
     * Encrypt the credential source before saving it to the database
     *
     * @param   string   $credential  The unencrypted, JSON-encoded credential source
     *
     * @return  string  The encrypted credential source, base64 encoded
     *
     * @since   4.0.0
     */
    private function encryptCredential(string $credential): string
    {
        $key = $this->getEncryptionKey();

        if (empty($key)) {
            return $credential;
        }

        $aes = new Aes($key, 256);

        return $aes->encryptString($credential);
    }

    /**
     * Decrypt the credential source if it was already encrypted in the database
     *
     * @param   string  $credential  The encrypted credential source, base64 encoded
     *
     * @return  string  The decrypted, JSON-encoded credential source
     *
     * @since   4.0.0
     */
    private function decryptCredential(string $credential): string
    {
        $key = $this->getEncryptionKey();

        if (empty($key)) {
            return $credential;
        }

        // Was the credential stored unencrypted (e.g. the site's secret was empty)?
        if ((str_contains($credential, '{')) && (str_contains($credential, '"publicKeyCredentialId"'))) {
            return $credential;
        }

        $aes = new Aes($key, 256);

        return $aes->decryptString($credential);
    }

    /**
     * Get the site's secret, used as an encryption key
     *
     * @return  string
     *
     * @since   4.0.0
     */
    private function getEncryptionKey(): string
    {
        try {
            $app = Factory::getApplication();
            /** @var Registry $config */
            $config = $app->getConfig();
            $secret = $config->get('secret', '');
        } catch (\Exception) {
            $secret = '';
        }

        return $secret;
    }

    /**
     * Format a date for display.
     *
     * The $tzAware parameter defines whether the formatted date will be timezone-aware. If set to false the formatted
     * date will be rendered in the UTC timezone. If set to true the code will automatically try to use the logged in
     * user's timezone or, if none is set, the site's default timezone (Server Timezone). If set to a positive integer
     * the same thing will happen but for the specified user ID instead of the currently logged in user.
     *
     * @param   string|\DateTime  $date     The date to format
     * @param   string|null       $format   The format string, default is Joomla's DATE_FORMAT_LC6 (usually "Y-m-d
     *                                      H:i:s")
     * @param   bool              $tzAware  Should the format be timezone aware? See notes above.
     *
     * @return  string
     * @since   4.2.0
     */
    private function formatDate($date, ?string $format = null, bool $tzAware = true): string
    {
        $utcTimeZone = new \DateTimeZone('UTC');
        $jDate       = new Date($date, $utcTimeZone);

        // Which timezone should I use?
        $tz = null;

        if ($tzAware !== false) {
            $userId = \is_bool($tzAware) ? null : (int) $tzAware;

            try {
                $tzDefault = Factory::getApplication()->get('offset');
            } catch (\Exception) {
                $tzDefault = 'GMT';
            }

            $user = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($userId ?? 0);
            $tz   = $user->getParam('timezone', $tzDefault);
        }

        if (!empty($tz)) {
            try {
                $userTimeZone = new \DateTimeZone($tz);

                $jDate->setTimezone($userTimeZone);
            } catch (\Exception) {
                // Nothing. Fall back to UTC.
            }
        }

        if (empty($format)) {
            $format = Text::_('DATE_FORMAT_LC6');
        }

        return $jDate->format($format, true);
    }
}
