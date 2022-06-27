<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\User;

use Joomla\Database\DatabaseInterface;

/**
 * Default factory for creating User objects
 *
 * @since  4.0.0
 */
class UserFactory implements UserFactoryInterface
{
    /**
     * The database.
     *
     * @var  DatabaseInterface
     */
    private $db;

    /**
     * UserFactory constructor.
     *
     * @param   DatabaseInterface  $db  The database
     */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Method to get an instance of a user for the given id.
     *
     * @param   int  $id  The id
     *
     * @return  User
     *
     * @since   4.0.0
     */
    public function loadUserById(int $id): User
    {
        return new User($id);
    }

    /**
     * Method to get an instance of a user for the given username.
     *
     * @param   string  $username  The username
     *
     * @return  User
     *
     * @since   4.0.0
     */
    public function loadUserByUsername(string $username): User
    {
        // Initialise some variables
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__users'))
            ->where($this->db->quoteName('username') . ' = :username')
            ->bind(':username', $username)
            ->setLimit(1);
        $this->db->setQuery($query);

        return $this->loadUserById((int) $this->db->loadResult());
    }
}
