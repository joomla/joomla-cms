<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Table interface class for the #__privacy_requests table
 *
 * @property   integer  $id                        Item ID (primary key)
 * @property   string   $email                     The email address of the individual requesting the data
 * @property   string   $requested_at              The time the request was created at
 * @property   integer  $status                    The status of the information request
 * @property   string   $request_type              The type of information request
 * @property   string   $confirm_token             Hashed token for confirming the information request
 * @property   string   $confirm_token_created_at  The time the confirmation token was generated
 *
 * @since  3.9.0
 */
class PrivacyTableRequest extends JTable
{
	/**
	 * The class constructor.
	 *
	 * @param   JDatabaseDriver  $db  JDatabaseDriver connector object.
	 *
	 * @since   3.9.0
	 */
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__privacy_requests', 'id', $db);
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
		$date = JFactory::getDate();

		// Set default values for new records
		if (!$this->id)
		{
			if (!$this->status)
			{
				$this->status = '0';
			}

			if (!$this->requested_at)
			{
				$this->requested_at = $date->toSql();
			}
		}

		return parent::store($updateNulls);
	}
}
