<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Table;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Table interface class for the #__privacy_consents table
 *
 * @property   integer  $id       Item ID (primary key)
 * @property   integer  $remind   The status of the reminder request
 * @property   string   $token    Hashed token for the reminder request
 * @property   integer  $user_id  User ID (pseudo foreign key to the #__users table) if the request is associated to a user account
 *
 * @since  3.9.0
 */
class ConsentTable extends Table
{
    /**
     * The class constructor.
     *
     * @param   DatabaseDriver  $db  DatabaseInterface connector object.
     *
     * @since   3.9.0
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__privacy_consents', 'id', $db);
    }

    /**
     * Method to store a row in the database from the Table instance properties.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   3.9.0
     */
    public function store($updateNulls = false)
    {
        $date = Factory::getDate();

        // Set default values for new records
        if (!$this->id) {
            if (!$this->remind) {
                $this->remind = '0';
            }

            if (!$this->created) {
                $this->created = $date->toSql();
            }
        }

        return parent::store($updateNulls);
    }
}
