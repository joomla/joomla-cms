<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\User;

use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
     * Per-request identity map of User instances, keyed by user ID.
     *
     * Avoids repeated database queries for the same user within a single request.
     *
     * @var    User[]
     * @since  __DEPLOY_VERSION__
     */
    protected $cacheById = [];

    /**
     * Per-request identity map of user IDs, keyed by username.
     *
     * Allows username lookups to benefit from the ID-based cache.
     *
     * @var    int[]
     * @since  __DEPLOY_VERSION__
     */
    protected $cacheByUsername = [];

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
     * Returns a cached instance if the same user ID has already been loaded
     * during the current request.
     *
     * @param   int  $id  The id
     *
     * @return  User
     *
     * @since   4.0.0
     */
    public function loadUserById(int $id): User
    {
        if (isset($this->cacheById[$id])) {
            return $this->cacheById[$id];
        }

        $user = new User($id);

        if (!empty($user->id)) {
            $this->cacheById[$id]                   = $user;
            $this->cacheByUsername[$user->username] = $id;
        }

        return $user;
    }

    /**
     * Method to get an instance of a user for the given username.
     *
     * Returns a cached instance if the same username has already been loaded
     * during the current request.
     *
     * @param   string  $username  The username
     *
     * @return  User
     *
     * @since   4.0.0
     */
    public function loadUserByUsername(string $username): User
    {
        // Check if we already resolved this username
        if (isset($this->cacheByUsername[$username])) {
            return $this->cacheById[$this->cacheByUsername[$username]];
        }

        // Initialise some variables
        $query = $this->db->createQuery()
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__users'))
            ->where($this->db->quoteName('username') . ' = :username')
            ->bind(':username', $username)
            ->setLimit(1);
        $this->db->setQuery($query);

        return $this->loadUserById((int) $this->db->loadResult());
    }

    /**
     * Clear the per-request user cache.
     *
     * Useful after user data has been modified (e.g. profile save, user deletion)
     * and the caller needs fresh data from the database.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function clearCache(): void
    {
        $this->cacheById       = [];
        $this->cacheByUsername = [];
    }
}
