<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Table;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * User notes table class
 *
 * @since  2.5
 */
class NoteTable extends Table implements VersionableTableInterface
{
    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $_supportNullValue = true;

    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database object
     *
     * @since  2.5
     */
    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_users.note';
        parent::__construct('#__user_notes', 'id', $db);

        $this->setColumnAlias('published', 'state');
    }

    /**
     * Overloaded store method for the notes table.
     *
     * @param   boolean  $updateNulls  Toggle whether null values should be updated.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   2.5
     */
    public function store($updateNulls = true)
    {
        $date   = Factory::getDate()->toSql();
        $userId = Factory::getUser()->get('id');

        if (!((int) $this->review_time)) {
            $this->review_time = null;
        }

        if ($this->id) {
            // Existing item
            $this->modified_time    = $date;
            $this->modified_user_id = $userId;
        } else {
            // New record.
            $this->created_time     = $date;
            $this->created_user_id  = $userId;
            $this->modified_time    = $date;
            $this->modified_user_id = $userId;
        }

        // Attempt to store the data.
        return parent::store($updateNulls);
    }

    /**
     * Method to perform sanity checks on the Table instance properties to ensure they are safe to store in the database.
     *
     * @return  boolean  True if the instance is sane and able to be stored in the database.
     *
     * @since   4.0.0
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        if (empty($this->modified_time)) {
            $this->modified_time = $this->created_time;
        }

        if (empty($this->modified_user_id)) {
            $this->modified_user_id = $this->created_user_id;
        }

        return true;
    }

    /**
     * Get the type alias for the history table
     *
     * @return  string  The alias as described above
     *
     * @since   4.0.0
     */
    public function getTypeAlias()
    {
        return $this->typeAlias;
    }
}
