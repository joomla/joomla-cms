<?php

/**
 * @package    Joomla.Administrator
 * @subpackage com_users
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Table;

use Exception;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\Helper\Mfa as MfaHelper;
use Joomla\Component\Users\Administrator\Model\BackupcodesModel;
use Joomla\Component\Users\Administrator\Service\Encrypt;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;
use RuntimeException;
use Throwable;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Table for the Multi-Factor Authentication records
 *
 * @property int    $id          Record ID.
 * @property int    $user_id     User ID
 * @property string $title       Record title.
 * @property string $method      MFA Method (corresponds to one of the plugins).
 * @property int    $default     Is this the default Method?
 * @property array  $options     Configuration options for the MFA Method.
 * @property string $created_on  Date and time the record was created.
 * @property string $last_used   Date and time the record was last used successfully.
 * @property int    $tries       Counter for unsuccessful tries
 * @property string $last_try    Date and time of the last unsuccessful try
 *
 * @since 4.2.0
 */
class MfaTable extends Table implements CurrentUserInterface
{
    use CurrentUserTrait;

    /**
     * Delete flags per ID, set up onBeforeDelete and used onAfterDelete
     *
     * @var   array
     * @since 4.2.0
     */
    private $deleteFlags = [];

    /**
     * Encryption service
     *
     * @var   Encrypt
     * @since 4.2.0
     */
    private $encryptService;

    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var   boolean
     * @since 4.2.0
     */
    // phpcs:ignore
    protected $_supportNullValue = true;

    /**
     * Table constructor
     *
     * @param   DatabaseDriver            $db          Database driver object
     * @param   DispatcherInterface|null  $dispatcher  Events dispatcher object
     *
     * @since 4.2.0
     */
    public function __construct(DatabaseDriver $db, DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__user_mfa', 'id', $db, $dispatcher);

        $this->encryptService = new Encrypt();
    }

    /**
     * Method to store a row in the database from the Table instance properties.
     *
     * If a primary key value is set the row with that primary key value will be updated with the instance property values.
     * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since 4.2.0
     */
    public function store($updateNulls = true)
    {
        // Encrypt the options before saving them
        $this->options = $this->encryptService->encrypt(json_encode($this->options ?: []));

        // Set last_used date to null if empty or zero date
        if (!((int) $this->last_used)) {
            $this->last_used = null;
        }

        $records = MfaHelper::getUserMfaRecords($this->user_id);

        if ($this->id) {
            // Existing record. Remove it from the list of records.
            $records = array_filter(
                $records,
                function ($rec) {
                    return $rec->id != $this->id;
                }
            );
        }

        // Update the dates on a new record
        if (empty($this->id)) {
            $this->created_on = Date::getInstance()->toSql();
            $this->last_used  = null;
        }

        // Do I need to mark this record as the default?
        if ($this->default == 0) {
            $hasDefaultRecord = array_reduce(
                $records,
                function ($carry, $record) {
                    return $carry || ($record->default == 1);
                },
                false
            );

            $this->default = $hasDefaultRecord ? 0 : 1;
        }

        // Let's find out if we are saving a new MFA method record without having backup codes yet.
        $mustCreateBackupCodes = false;

        if (empty($this->id) && $this->method !== 'backupcodes') {
            // Do I have any backup records?
            $hasBackupCodes = array_reduce(
                $records,
                function (bool $carry, $record) {
                    return $carry || $record->method === 'backupcodes';
                },
                false
            );

            $mustCreateBackupCodes = !$hasBackupCodes;

            // If the only other entry is the backup records one I need to make this the default method
            if ($hasBackupCodes && count($records) === 1) {
                $this->default = 1;
            }
        }

        // Store the record
        try {
            $result = parent::store($updateNulls);
        } catch (Throwable $e) {
            $this->setError($e->getMessage());

            $result = false;
        }

        // Decrypt the options (they must be decrypted in memory)
        $this->decryptOptions();

        if ($result) {
            // If this record is the default unset the default flag from all other records
            $this->switchDefaultRecord();

            // Do I need to generate backup codes?
            if ($mustCreateBackupCodes) {
                $this->generateBackupCodes();
            }
        }

        return $result;
    }

    /**
     * Method to load a row from the database by primary key and bind the fields to the Table instance properties.
     *
     * @param   mixed    $keys   An optional primary key value to load the row by, or an array of fields to match.
     *                           If not set the instance property value is used.
     * @param   boolean  $reset  True to reset the default values before loading the new row.
     *
     * @return  boolean  True if successful. False if row not found.
     *
     * @since 4.2.0
     * @throws  \InvalidArgumentException
     * @throws  RuntimeException
     * @throws  \UnexpectedValueException
     */
    public function load($keys = null, $reset = true)
    {
        $result = parent::load($keys, $reset);

        if ($result) {
            $this->decryptOptions();
        }

        return $result;
    }

    /**
     * Method to delete a row from the database table by primary key value.
     *
     * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
     *
     * @return  boolean  True on success.
     *
     * @since 4.2.0
     * @throws  \UnexpectedValueException
     */
    public function delete($pk = null)
    {
        $record = $this;

        if ($pk != $this->id) {
            $record = clone $this;
            $record->reset();
            $result = $record->load($pk);

            if (!$result) {
                // If the record does not exist I will stomp my feet and deny your request
                throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
            }
        }

        $user = $this->getCurrentUser();

        // The user must be a registered user, not a guest
        if ($user->guest) {
            throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        // Save flags used onAfterDelete
        $this->deleteFlags[$record->id] = [
            'default'    => $record->default,
            'numRecords' => $this->getNumRecords($record->user_id),
            'user_id'    => $record->user_id,
            'method'     => $record->method,
        ];

        if (\is_null($pk)) {
            $pk = [$this->_tbl_key => $this->id];
        } elseif (!\is_array($pk)) {
            $pk = [$this->_tbl_key => $pk];
        }

        $isDeleted = parent::delete($pk);

        if ($isDeleted) {
            $this->afterDelete($pk);
        }

        return $isDeleted;
    }

    /**
     * Decrypt the possibly encrypted options
     *
     * @return void
     * @since 4.2.0
     */
    private function decryptOptions(): void
    {
        // Try with modern decryption
        $decrypted = @json_decode($this->encryptService->decrypt($this->options ?? ''), true);

        if (is_string($decrypted)) {
            $decrypted = @json_decode($decrypted, true);
        }

        // Fall back to legacy decryption
        if (!is_array($decrypted)) {
            $decrypted = @json_decode($this->encryptService->decrypt($this->options ?? '', true), true);

            if (is_string($decrypted)) {
                $decrypted = @json_decode($decrypted, true);
            }
        }

        $this->options = $decrypted ?: [];
    }

    /**
     * If this record is set to be the default, unset the default flag from the other records for the same user.
     *
     * @return void
     * @since 4.2.0
     */
    private function switchDefaultRecord(): void
    {
        if (!$this->default) {
            return;
        }

        /**
         * This record is marked as default, therefore we need to unset the default flag from all other records for this
         * user.
         */
        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__user_mfa'))
            ->set($db->quoteName('default') . ' = 0')
            ->where($db->quoteName('user_id') . ' = :user_id')
            ->where($db->quoteName('id') . ' != :id')
            ->bind(':user_id', $this->user_id, ParameterType::INTEGER)
            ->bind(':id', $this->id, ParameterType::INTEGER);
        $db->setQuery($query)->execute();
    }

    /**
     * Regenerate backup code is the flag is set.
     *
     * @return void
     * @throws Exception
     * @since 4.2.0
     */
    private function generateBackupCodes(): void
    {
        /** @var MVCFactoryInterface $factory */
        $factory = Factory::getApplication()->bootComponent('com_users')->getMVCFactory();

        /** @var BackupcodesModel $backupCodes */
        $backupCodes = $factory->createModel('Backupcodes', 'Administrator');
        $user        = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($this->user_id);
        $backupCodes->regenerateBackupCodes($user);
    }

    /**
     * Runs after successfully deleting a record
     *
     * @param   int|array  $pk  The promary key of the deleted record
     *
     * @return void
     * @since 4.2.0
     */
    private function afterDelete($pk): void
    {
        if (is_array($pk)) {
            $pk = $pk[$this->_tbl_key] ?? array_shift($pk);
        }

        if (!isset($this->deleteFlags[$pk])) {
            return;
        }

        if (($this->deleteFlags[$pk]['numRecords'] <= 2) && ($this->deleteFlags[$pk]['method'] != 'backupcodes')) {
            /**
             * This was the second to last MFA record in the database (the last one is the `backupcodes`). Therefore, we
             * need to delete the remaining entry and go away. We don't trigger this if the Method we are deleting was
             * the `backupcodes` because we might just be regenerating the backup codes.
             */
            $db    = $this->getDbo();
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__user_mfa'))
                ->where($db->quoteName('user_id') . ' = :user_id')
                ->bind(':user_id', $this->deleteFlags[$pk]['user_id'], ParameterType::INTEGER);
            $db->setQuery($query)->execute();

            unset($this->deleteFlags[$pk]);

            return;
        }

        // This was the default record. Promote the next available record to default.
        if ($this->deleteFlags[$pk]['default']) {
            $db    = $this->getDbo();
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__user_mfa'))
                ->where($db->quoteName('user_id') . ' = :user_id')
                ->where($db->quoteName('method') . ' != ' . $db->quote('backupcodes'))
                ->bind(':user_id', $this->deleteFlags[$pk]['user_id'], ParameterType::INTEGER);
            $ids   = $db->setQuery($query)->loadColumn();

            if (empty($ids)) {
                return;
            }

            $id    = array_shift($ids);
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__user_mfa'))
                ->set($db->quoteName('default') . ' = 1')
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);
            $db->setQuery($query)->execute();
        }
    }

    /**
     * Get the number of MFA records for a give user ID
     *
     * @param   int  $userId  The user ID to check
     *
     * @return  integer
     *
     * @since 4.2.0
     */
    private function getNumRecords(int $userId): int
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__user_mfa'))
            ->where($db->quoteName('user_id') . ' = :user_id')
            ->bind(':user_id', $userId, ParameterType::INTEGER);
        $numOldRecords = $db->setQuery($query)->loadResult();

        return (int) $numOldRecords;
    }
}
