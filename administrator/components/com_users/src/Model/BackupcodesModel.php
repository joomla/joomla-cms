<?php

/**
 * @package    Joomla.Administrator
 * @subpackage com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Model;

use Joomla\CMS\Crypt\Crypt;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\Table\MfaTable;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Model for managing backup codes
 *
 * @since 4.2.0
 */
class BackupcodesModel extends BaseDatabaseModel
{
    /**
     * Caches the backup codes per user ID
     *
     * @var  array
     * @since 4.2.0
     */
    protected $cache = [];

    /**
     * Get the backup codes record for the specified user
     *
     * @param   User|null   $user   The user in question. Use null for the currently logged in user.
     *
     * @return  MfaTable|null  Record object or null if none is found
     * @throws  \Exception
     * @since 4.2.0
     */
    public function getBackupCodesRecord(User $user = null): ?MfaTable
    {
        // Make sure I have a user
        if (empty($user)) {
            $user = Factory::getApplication()->getIdentity() ?:
                Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
        }

        /** @var MfaTable $record */
        $record = $this->getTable('Mfa', 'Administrator');
        $loaded = $record->load(
            [
                'user_id' => $user->id,
                'method'  => 'backupcodes',
            ]
        );

        if (!$loaded) {
            $record = null;
        }

        return $record;
    }

    /**
     * Generate a new set of backup codes for the specified user. The generated codes are immediately saved to the
     * database and the internal cache is updated.
     *
     * @param   User|null   $user   Which user to generate codes for?
     *
     * @return void
     * @throws \Exception
     * @since 4.2.0
     */
    public function regenerateBackupCodes(User $user = null): void
    {
        // Make sure I have a user
        if (empty($user)) {
            $user = Factory::getApplication()->getIdentity() ?:
                Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
        }

        // Generate backup codes
        $backupCodes = [];

        for ($i = 0; $i < 10; $i++) {
            // Each backup code is 2 groups of 4 digits
            $backupCodes[$i] = sprintf('%04u%04u', random_int(0, 9999), random_int(0, 9999));
        }

        // Save the backup codes to the database and update the cache
        $this->saveBackupCodes($backupCodes, $user);
    }

    /**
     * Saves the backup codes to the database
     *
     * @param   array       $codes   An array of exactly 10 elements
     * @param   User|null   $user    The user for which to save the backup codes
     *
     * @return  boolean
     * @throws  \Exception
     * @since 4.2.0
     */
    public function saveBackupCodes(array $codes, ?User $user = null): bool
    {
        // Make sure I have a user
        if (empty($user)) {
            $user = Factory::getApplication()->getIdentity() ?:
                Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
        }

        // Try to load existing backup codes
        $existingCodes = $this->getBackupCodes($user);
        $jNow          = Date::getInstance();

        /** @var MfaTable $record */
        $record = $this->getTable('Mfa', 'Administrator');

        if (is_null($existingCodes)) {
            $record->reset();

            $newData = [
                'user_id'    => $user->id,
                'title'      => Text::_('COM_USERS_USER_BACKUPCODES'),
                'method'     => 'backupcodes',
                'default'    => 0,
                'created_on' => $jNow->toSql(),
                'options'    => $codes,
            ];
        } else {
            $record->load(
                [
                    'user_id' => $user->id,
                    'method'  => 'backupcodes',
                ]
            );

            $newData = [
                'options' => $codes,
            ];
        }

        $saved = $record->save($newData);

        if (!$saved) {
            return false;
        }

        // Finally, update the cache
        $this->cache[$user->id] = $codes;

        return true;
    }

    /**
     * Returns the backup codes for the specified user. Cached values will be preferentially returned, therefore you
     * MUST go through this model's Methods ONLY when dealing with backup codes.
     *
     * @param   User|null   $user   The user for which you want the backup codes
     *
     * @return  array|null  The backup codes, or null if they do not exist
     * @throws  \Exception
     * @since 4.2.0
     */
    public function getBackupCodes(User $user = null): ?array
    {
        // Make sure I have a user
        if (empty($user)) {
            $user = Factory::getApplication()->getIdentity() ?:
                Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);
        }

        if (isset($this->cache[$user->id])) {
            return $this->cache[$user->id];
        }

        // If there is no cached record try to load it from the database
        $this->cache[$user->id] = null;

        // Try to load the record
        /** @var MfaTable $record */
        $record = $this->getTable('Mfa', 'Administrator');
        $loaded = $record->load(
            [
                'user_id' => $user->id,
                'method'  => 'backupcodes',
            ]
        );

        if ($loaded) {
            $this->cache[$user->id] = $record->options;
        }

        return $this->cache[$user->id];
    }

    /**
     * Check if the provided string is a backup code. If it is, it will be removed from the list (replaced with an empty
     * string) and the codes will be saved to the database. All comparisons are performed in a timing safe manner.
     *
     * @param   string      $code   The code to check
     * @param   User|null   $user   The user to check against
     *
     * @return  boolean
     * @throws  \Exception
     * @since 4.2.0
     */
    public function isBackupCode($code, ?User $user = null): bool
    {
        // Load the backup codes
        $codes = $this->getBackupCodes($user) ?: array_fill(0, 10, '');

        // Keep only the numbers in the provided $code
        $code = filter_var($code, FILTER_SANITIZE_NUMBER_INT);
        $code = trim($code);

        // Check if the code is in the array. We always check against ten codes to prevent timing attacks which
        // determine the amount of codes.
        $result = false;

        // The two arrays let us always add an element to an array, therefore having PHP expend the same amount of time
        // for the correct code, the incorrect codes and the fake codes.
        $newArray   = [];
        $dummyArray = [];

        $realLength = count($codes);
        $restLength = 10 - $realLength;

        for ($i = 0; $i < $realLength; $i++) {
            if (hash_equals($codes[$i], $code)) {
                // This may seem redundant but makes sure both branches of the if-block are isochronous
                $result       = $result || true;
                $newArray[]   = '';
                $dummyArray[] = $codes[$i];
            } else {
                // This may seem redundant but makes sure both branches of the if-block are isochronous
                $result       = $result || false;
                $dummyArray[] = '';
                $newArray[]   = $codes[$i];
            }
        }

        /**
         * This is an intentional waste of time, symmetrical to the code above, making sure
         * evaluating each of the total of ten elements takes the same time. This code should never
         * run UNLESS someone messed up with our backup codes array and it no longer contains 10
         * elements.
         */
        $otherResult = false;

        $temp1 = '';

        for ($i = 0; $i < 10; $i++) {
            $temp1[$i] = random_int(0, 99999999);
        }

        for ($i = 0; $i < $restLength; $i++) {
            if (Crypt::timingSafeCompare($temp1[$i], $code)) {
                $otherResult  = $otherResult || true;
                $newArray[]   = '';
                $dummyArray[] = $temp1[$i];
            } else {
                $otherResult  = $otherResult || false;
                $newArray[]   = '';
                $dummyArray[] = $temp1[$i];
            }
        }

        // This last check makes sure than an empty code does not validate
        $result = $result && !hash_equals('', $code);

        // Save the backup codes
        $this->saveBackupCodes($newArray, $user);

        // Finally return the result
        return $result;
    }
}
