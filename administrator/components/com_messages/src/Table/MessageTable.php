<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Message Table class
 *
 * @since  1.5
 */
class MessageTable extends Table
{
	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   1.5
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__messages', 'message_id', $db);

		$this->setColumnAlias('published', 'state');
	}

	/**
	 * Validation and filtering.
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function check()
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// Check the to and from users.
		$user = new User($this->user_id_from);

		if (empty($user->id))
		{
			$this->setError(Text::_('COM_MESSAGES_ERROR_INVALID_FROM_USER'));

			return false;
		}

		$user = new User($this->user_id_to);

		if (empty($user->id))
		{
			$this->setError(Text::_('COM_MESSAGES_ERROR_INVALID_TO_USER'));

			return false;
		}

		if (empty($this->subject))
		{
			$this->setError(Text::_('COM_MESSAGES_ERROR_INVALID_SUBJECT'));

			return false;
		}

		if (empty($this->message))
		{
			$this->setError(Text::_('COM_MESSAGES_ERROR_INVALID_MESSAGE'));

			return false;
		}

		return true;
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param   mixed    $pks     An optional array of primary key values to update.  If not
	 *                            set the instance property value is used.
	 * @param   integer  $state   The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param   integer  $userId  The user id of the user performing the operation.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		$k = $this->_tbl_key;

		// Sanitize input.
		$pks = ArrayHelper::toInteger($pks);
		$state  = (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks))
		{
			if ($this->$k)
			{
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else
			{
				$this->setError(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));

				return false;
			}
		}

		// Update the publishing state for rows with the given primary keys.
		$query = $this->_db->getQuery(true);
		$query->update($this->_db->quoteName($this->_tbl))
			->set($this->_db->quoteName('state') . ' = :state')
			->whereIn($this->_db->quoteName($k), $pks)
			->bind(':state', $state, ParameterType::INTEGER);

		try
		{
			$this->_db->setQuery($query)->execute();
		}
		catch (\RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		// If the \JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks))
		{
			$this->state = $state;
		}

		$this->setError('');

		return true;
	}
}
