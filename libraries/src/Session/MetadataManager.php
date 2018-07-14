<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Session;

defined('JPATH_PLATFORM') or die;

use Joomla\Application\AbstractApplication;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Manager for optional session metadata.
 *
 * @since  3.8.6
 * @internal
 */
final class MetadataManager
{
	/**
	 * Application object.
	 *
	 * @var    AbstractApplication
	 * @since  3.8.6
	 */
	private $app;

	/**
	 * Database driver.
	 *
	 * @var    DatabaseInterface
	 * @since  3.8.6
	 */
	private $db;

	/**
	 * MetadataManager constructor.
	 *
	 * @param   AbstractApplication  $app  Application object.
	 * @param   DatabaseInterface    $db   Database driver.
	 *
	 * @since   3.8.6
	 */
	public function __construct(AbstractApplication $app, DatabaseInterface $db)
	{
		$this->app = $app;
		$this->db  = $db;
	}

	/**
	 * Create the metadata record if it does not exist.
	 *
	 * @param   Session  $session  The session to create the metadata record for.
	 * @param   User     $user     The user to associate with the record.
	 *
	 * @return  void
	 *
	 * @since   3.8.6
	 * @throws  \RuntimeException
	 */
	public function createRecordIfNonExisting(Session $session, User $user)
	{
		$query = $this->db->getQuery(true)
			->select($this->db->quoteName('session_id'))
			->from($this->db->quoteName('#__session'))
			->where($this->db->quoteName('session_id') . ' = ' . $this->db->quote($session->getId()));

		$this->db->setQuery($query, 0, 1);
		$exists = $this->db->loadResult();

		// If the session record doesn't exist initialise it.
		if ($exists)
		{
			return;
		}

		$query->clear();

		$time = $session->isNew() ? time() : $session->get('session.timer.start');

		$columns = array(
			$this->db->quoteName('session_id'),
			$this->db->quoteName('guest'),
			$this->db->quoteName('time'),
			$this->db->quoteName('userid'),
			$this->db->quoteName('username'),
		);

		$values = array(
			$this->db->quote($session->getId()),
			(int) $user->guest,
			$this->db->quote((int) $time),
			(int) $user->id,
			$this->db->quote($user->username),
		);

		if ($this->app instanceof CMSApplication && !$this->app->get('shared_session', false))
		{
			$columns[] = $this->db->quoteName('client_id');
			$values[] = (int) $this->app->getClientId();
		}

		$query->insert($this->db->quoteName('#__session'))
			->columns($columns)
			->values(implode(', ', $values));

		$this->db->setQuery($query);

		try
		{
			$this->db->execute();
		}
		catch (ExecutionFailureException $e)
		{
			// This failure isn't critical, we can go on without the metadata
		}
	}

	/**
	 * Delete records with a timestamp prior to the given time.
	 *
	 * @param   integer  $time  The time records should be deleted if expired before.
	 *
	 * @return  void
	 *
	 * @since   3.8.6
	 */
	public function deletePriorTo($time)
	{
		$query = $this->db->getQuery(true)
			->delete($this->db->quoteName('#__session'))
			->where($this->db->quoteName('time') . ' < ' . $this->db->quote($time));

		$this->db->setQuery($query);

		try
		{
			$this->db->execute();
		}
		catch (ExecutionFailureException $exception)
		{
			// Since garbage collection does not result in a fatal error when run in the session API, we don't allow it here either.
		}
	}
}
