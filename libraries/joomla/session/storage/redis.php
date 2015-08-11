<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session Redis
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Redis session storage handler for PHP
 *
 * @see    http://www.php.net/manual/en/function.session-set-save-handler.php
 * @see    https://github.com/phpredis/phpredis#php-session-handler
 * @since  3.5
 */
class JSessionStorageRedis extends JSessionStorage
{
	/**
	 * Read the data for a particular session identifier from the SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  string  The session data.
	 *
	 * @since   3.5
	 */
	public function read($id)
	{
		// Get the databstore connection object and verify its connected.
		$ds = JFactory::getDso();

		try
		{
			// Get the session data from the datastore.
			$data   = $ds->get('sess-' . $id);
			$result = json_decode($data);
			$result = str_replace('\0\0\0', chr(0) . '*' . chr(0), $result);

			return $result;
		}
		catch (Exception $e)
		{
			throw new RuntimeException(JText::_('JERROR_SESSION_REDIS_READ'));
			return false;
		}
	}

	/**
	 * Write session data to the SessionHandler backend.
	 *
	 * @param   string  $id    The session identifier.
	 * @param   string  $data  The session data.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   3.5
	 */
	public function write($id, $data)
	{
		// Get the datastore connection object and verify its connected.
		$ds             = JFactory::getDso();
		$db             = JFactory::getDbo();
		$user           = JFactory::getUser();
		$data           = str_replace(chr(0) . '*' . chr(0), '\0\0\0', $data);
		$key            = 'sess-' . $id;
		$key4sessionuid = 'sessid-' . (int) $user->get('id') . '-' . (int) JFactory::getApplication()->getClientId();
		$key_uname      = 'user-' . $user->get('username');
		$jsonValue      = json_encode($data);

		// Calculate the session lifetime.
		$lifetime = ((JFactory::getConfig()->get('lifetime')) ? JFactory::getConfig()->get('lifetime') * 60 : 900);

		try
		{
			$ds->setex($key, $lifetime, $jsonValue);

			if ($user->get('id') > 0)
			{
				$hash = array(
					'client_id' => (int) JFactory::getApplication()->getClientId(),
					'guest' => $db->quote($user->get('guest')),
					'time' => (int) JFactory::getSession()->get('session.timer.start'),
					'userid' => (int) $user->get('id'),
					'username' => $db->quote($user->get('username')),
				);

				$jsonValue = json_encode($hash);
				$ds->setex($key4sessionuid, $lifetime, $key);
				$ds->setex($key_uname, $lifetime, $jsonValue);
				$ds->setex($user->username, $lifetime, $user->id);
				$ds->setex($user->id, $lifetime, $user->username);
				$ds->sadd('utenti', $user->username);
			}

			return true;
		}
		catch (Exception $e)
		{
			throw new RuntimeException(JText::_('JERROR_SESSION_redis_write'));

			return false;
		}
	}

	/**
	 * Destroy the data for a particular session identifier in the SessionHandler backend.
	 *
	 * @param   string  $id  The session identifier.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   3.5
	 */
	public function destroy($id)
	{
		$ds = JFactory::getDso();

		try
		{
			$ds->delete($id);
		}
		catch (Exception $e)
		{
			throw new RuntimeException(JText::_('JERROR_SESSION_REDIS_DESTROY'));

			return false;
		}
	}

	/**
	 * Garbage collect stale sessions from the SessionHandler backend.
	 *
	 * @param   integer  $lifetime  The maximum age of a session.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   3.5
	 */
	public function gc($lifetime = 1440)
	{
		$ds = JFactory::getDso();

		try
		{
			$lista = $ds->smembers('utenti');

		}
		catch (Exception $e)
		{
			throw new RuntimeException(JText::_('JERROR_SESSION_REDIS_DESTROY'));

			return false;
		}

		foreach ($lista as $elm)
		{
			try
			{
				$exist = $ds->ttl($elm);
			}
			catch (Exception $e)
			{
				throw new RuntimeException(JText::_('JERROR_SESSION_REDIS_DESTROY'));

				return false;
			}
		}

		if ($exist == -1)
		{
			$ds->srem('utenti', $elm);
			$ds->delete('user-' . $elm);
		}
	}

	/**
	 * Test to see if the SessionHandler is available.
	 *
	 * @return boolean  True on success, false otherwise.
	 *
	 * @since   3.5
	 */
	public static function isSupported()
	{
		return (extension_loaded('redis') && class_exists('Redis'));
	}
}
