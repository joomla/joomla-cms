<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Session;

defined('JPATH_PLATFORM') or die;

use Joomla\Application\AbstractApplication;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\User\User;

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
	 * @var    \JDatabaseDriver
	 * @since  3.8.6
	 */
	private $db;

	/**
	 * MetadataManager constructor.
	 *
	 * @param   AbstractApplication  $app  Application object.
	 * @param   \JDatabaseDriver     $db   Database driver.
	 *
	 * @since   3.8.6
	 */
	public function __construct(AbstractApplication $app, \JDatabaseDriver $db)
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
			->where($this->db->quoteName('session_id') . ' = ' . $this->db->quoteBinary($session->getId()));

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
			$this->db->quoteBinary($session->getId()),
			(int) $user->guest,
			(int) $time,
			(int) $user->id,
			$this->db->quote($user->username),
		);

		if ($this->app instanceof CMSApplication && !$this->app->get('shared_session', '0'))
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
		catch (\RuntimeException $e)
		{
			/*
			 * Because of how our session handlers are structured, we must abort the request if this insert query fails,
			 * especially in the case of the database handler which does not support "INSERT or UPDATE" logic. With the
			 * change to the `joomla/session` Framework package in 4.0, where the required logic is implemented in the
			 * handlers, we can change this catch block so that the error is gracefully handled and does not result
			 * in a fatal error for the request.
			 */
			throw new \RuntimeException(\JText::_('JERROR_SESSION_STARTUP'), $e->getCode(), $e);
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
			->where($this->db->quoteName('time') . ' < ' . (int) $time);

		$this->db->setQuery($query);

		try
		{
			$this->db->execute();
		}
		catch (\JDatabaseExceptionExecuting $exception)
		{
			/*
			 * The database API logs errors on failures so we don't need to add any error handling mechanisms here.
			 * Since garbage collection does not result in a fatal error when run in the session API, we don't allow it here either.
			 */
		}
	}
}
