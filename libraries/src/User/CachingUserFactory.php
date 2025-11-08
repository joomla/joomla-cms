<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2025 Open Source Matters
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\User;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Factory that creates and caches User instances.
 *
 * @since  6.0.0
 */
class CachingUserFactory extends UserFactory
{
	/**
	 * Cache of User instances.
	 *
	 * @var User[]
	 */
	private array $cache = [];

	/**
	 * Get a cached instance of a user by ID.
	 *
	 * @param   int  $id  The user ID
	 *
	 * @return  User
	 *
	 * @since   6.0.0
	 */
	public function loadUserById(int $id): User
	{
		if (isset($this->cache[$id]))
		{
			return $this->cache[$id];
		}

		$user = parent::loadUserById($id);
		$this->cache[$id] = $user;

		return $user;
	}

	/**
	 * Get a cached instance of a user by username.
	 *
	 * @param   string  $username  The username
	 *
	 * @return  User
	 *
	 * @since   6.0.0
	 */
	public function loadUserByUsername(string $username): User
	{
		foreach ($this->cache as $user)
		{
			if ($user->username === $username)
			{
				return $user;
			}
		}

		return parent::loadUserByUsername($username);
	}

	/**
	 * Clear the cached users.
	 *
	 * @return void
	 */
	public function clearCache(): void
	{
		$this->cache = [];
	}
}
