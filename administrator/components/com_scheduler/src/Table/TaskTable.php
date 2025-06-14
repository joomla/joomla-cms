<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Table;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Exception\QueryTypeAlreadyDefinedException;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Table class for tasks scheduled through `com_scheduler`.
 * The type alias for Task table entries is `com_scheduler.task`.
 *
 * @since  4.1.0
 */
class TaskTable extends Table implements CurrentUserInterface
{
    use CurrentUserTrait;

    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  4.1.1
     */
    protected $_supportNullValue = true;

    /**
     * Ensure params are json encoded in the bind method.
     *
     * @var    string[]
     * @since  4.1.0
     */
    protected $_jsonEncode = ['params', 'execution_rules', 'cron_rules'];

    /**
     * The 'created' column.
     *
     * @var    string
     * @since  4.1.0
     */
    public $created;

    /**
     * The 'title' column.
     *
     * @var    string
     * @since  4.1.0
     */
    public $title;

    /**
     * @var    string
     * @since  4.1.0
     */
    public $typeAlias = 'com_scheduler.task';

    /**
     * TaskTable constructor override, needed to pass the DB table name and primary key to {@see Table::__construct()}.
     *
     * @param   DatabaseDriver        $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   4.1.0
     */
    public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null)
    {
        $this->setColumnAlias('published', 'state');

        parent::__construct('#__scheduler_tasks', 'id', $db, $dispatcher);
    }

    /**
     * Overloads {@see Table::check()} to perform sanity checks on properties and make sure they're
     * safe to store.
     *
     * @return  boolean  True if checks pass.
     *
     * @since   4.1.0
     * @throws  \Exception
     */
    public function check(): bool
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        $this->title = htmlspecialchars_decode($this->title, ENT_QUOTES);

        // Set created date if not set.
        // ? Might not need since the constructor already sets this
        if (!(int) $this->created) {
            $this->created = Factory::getDate()->toSql();
        }

        // @todo : Add more checks if needed

        return true;
    }

    /**
     * Override {@see Table::store()} to update null fields as a default, which is needed when DATETIME
     * fields need to be updated to NULL. This override is needed because {@see AdminModel::save()} does not
     * expose an option to pass true to Table::store(). Also ensures the `created` and `created_by` fields are
     * set.
     *
     * @param   boolean  $updateNulls  True to update fields even if they're null.
     *
     * @return  boolean  True if successful.
     *
     * @since   4.1.0
     * @throws  \Exception
     */
    public function store($updateNulls = true): bool
    {
        $isNew = empty($this->getId());

        // Set creation date if not set for a new item.
        if ($isNew && empty($this->created)) {
            $this->created = Factory::getDate()->toSql();
        }

        // Set `created_by` if not set for a new item.
        if ($isNew && empty($this->created_by)) {
            $this->created_by = $this->getCurrentUser()->id;
        }

        // @todo : Should we add modified, modified_by fields? [ ]

        return parent::store($updateNulls);
    }

    /**
     * Returns the asset name of the entry as it appears in the {@see Asset} table.
     *
     * @return  string  The asset name.
     *
     * @since   4.1.0
     */
    protected function _getAssetName(): string
    {
        $k = $this->_tbl_key;

        return 'com_scheduler.task.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return  string
     *
     * @since   5.2.3
     */
    protected function _getAssetTitle(): string
    {
        return $this->title;
    }

    /**
     * Method to get the parent asset under which to register this one.
     * By default, all assets are registered to the ROOT node with ID,
     * which will default to 1 if none exists.
     * The extended class can define a table and id to lookup.  If the
     * asset does not exist it will be created.
     *
     * @param   Table|null  $table  A Table object for the asset parent.
     * @param   null        $id     Id to look up
     *
     * @return  integer
     *
     * @since   5.2.3
     */
    protected function _getAssetParentId(?Table $table = null, $id = null): int
    {
        $assetId = null;
        $asset   = new Asset($this->getDbo(), $this->getDispatcher());

        if ($asset->loadByName('com_scheduler')) {
            $assetId = $asset->id;
        }

        return $assetId ?? parent::_getAssetParentId($table, $id);
    }

    /**
     * Override {@see Table::bind()} to bind some fields even if they're null given they're present in $src.
     * This override is needed specifically for DATETIME fields, of which the `next_execution` field is updated to
     * null if a task is configured to execute only on manual trigger.
     *
     * @param   array|object  $src     An associative array or object to bind to the Table instance.
     * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
     *
     * @return  boolean
     *
     * @since   4.1.0
     */
    public function bind($src, $ignore = []): bool
    {
        $fields = ['next_execution'];

        foreach ($fields as $field) {
            if (\array_key_exists($field, $src) && \is_null($src[$field])) {
                $this->$field = $src[$field];
            }
        }

        return parent::bind($src, $ignore);
    }

    /**
     * Release pseudo-locks on a set of task records. If an empty set is passed, this method releases lock on its
     * instance primary key, if available.
     *
     * @param   integer[]  $pks     An optional array of primary key values to update. If not set the instance property
     *                              value is used.
     * @param   ?int       $userId  ID of the user unlocking the tasks.
     *
     * @return  boolean  True on success; false if $pks is empty.
     *
     * @since   4.1.0
     * @throws  QueryTypeAlreadyDefinedException|\UnexpectedValueException|\BadMethodCallException
     */
    public function unlock(array $pks = [], ?int $userId = null): bool
    {
        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTaskBeforeUnlock',
            [
                'subject' => $this,
                'pks'     => $pks,
                'userId'  => $userId,
            ]
        );

        $this->getDispatcher()->dispatch('onTaskBeforeUnlock', $event);

        // Some pre-processing before we can work with the keys.
        if (!empty($pks)) {
            foreach ($pks as $key => $pk) {
                if (!\is_array($pk)) {
                    $pks[$key] = [$this->_tbl_key => $pk];
                }
            }
        }

        // If there are no primary keys set check to see if the instance key is set and use that.
        if (empty($pks)) {
            $pk = [];

            foreach ($this->_tbl_keys as $key) {
                if ($this->$key) {
                    $pk[$key] = $this->$key;
                } else {
                    // We don't have a full primary key - return false.
                    $this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

                    return false;
                }
            }

            $pks = [$pk];
        }

        $lockedField = $this->getColumnAlias('locked');

        foreach ($pks as $pk) {
            // Update the publishing state for rows with the given primary keys.
            $query = $this->_db->getQuery(true)
                ->update($this->_tbl)
                ->set($this->_db->quoteName($lockedField) . ' = NULL');

            // Build the WHERE clause for the primary keys.
            $this->appendPrimaryKeys($query, $pk);

            $this->_db->setQuery($query);

            try {
                $this->_db->execute();
            } catch (\RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }

            // If the Table instance value is in the list of primary keys that were set, set the instance.
            $ours = true;

            foreach ($this->_tbl_keys as $key) {
                if ($this->$key != $pk[$key]) {
                    $ours = false;
                }
            }

            if ($ours) {
                $this->$lockedField = null;
            }
        }

        // Pre-processing by observers
        $event = AbstractEvent::create(
            'onTaskAfterUnlock',
            [
                'subject' => $this,
                'pks'     => $pks,
                'userId'  => $userId,
            ]
        );

        $this->getDispatcher()->dispatch('onTaskAfterUnlock', $event);

        return true;
    }
}
