<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc.
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
     * The database connection.
     *
     * @var  DatabaseInterface
     */
    private $db;

    /**
     * Static cache for loaded user instances.
     *
     * @var  User[]
     */
    private static array $cache = [];

    /**
     * UserFactory constructor.
     *
     * @param   DatabaseInterface  $db  The database connection
     */
    public function __construct(DatabaseInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Method to get a cached instance of a user for the given ID.
     * Returns from cache if already loaded, otherwise creates and caches a new instance.
     *
     * @param   int  $id  The user ID
     *
     * @return  User
     *
     * @since   4.0.0
     */
    public function loadUserById(int $id): User
    {
        // Return cached user if available
        if (isset(self::$cache[$id])) {
            return self::$cache[$id];
        }

        // Otherwise create and cache a new instance
        $user = new User($id);
        self::$cache[$id] = $user;

        return $user;
    }

    /**
     * Method to get a cached instance of a user for the given username.
     * Reuses cached data if possible to avoid redundant database queries.
     *
     * @param   string  $username  The username
     *
     * @return  User
     *
     * @since   4.0.0
     */
    public function loadUserByUsername(string $username): User
    {
        // Try to find cached user by username first
        foreach (self::$cache as $user) {
            if ($user->username === $username) {
                return $user;
            }
        }

        // Otherwise query database for the user ID
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('id'))
            ->from($this->db->quoteName('#__users'))
            ->where($this->db->quoteName('username') . ' = :username')
            ->bind(':username', $username)
            ->setLimit(1);

        $this->db->setQuery($query);
        $id = (int) $this->db->loadResult();

        return $this->loadUserById($id);
    }

    /**
     * Clears the cached user instances.
     * Useful for testing or when user data changes at runtime.
     *
     * @return  void
     */
    public static function clearCache(): void
    {
        self::$cache = [];
    }
}
