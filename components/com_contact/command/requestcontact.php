<?php
/**
 * @package     Joomla.Framework
 * @subpackage  Service Layer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Service\CommandBase;

final class ContactCommandRequestcontact extends CommandBase
{
	/**
	 * Constructor.
	 * 
	 * @param   integer  $id    Contact id.
	 * @param   array    $data  Array of contact request information.
	 */
	public function __construct($id, array $data)
	{
		if (!is_numeric($id) || $id <= 0)
		{
			throw new InvalidArgumentException('Invalid contact id');
		}

		if (empty($data['contact_name']))
		{
			throw new InvalidArgumentException('Invalid or missing contact name');
		}

		if (empty($data['contact_email']))
		{
			throw new InvalidArgumentException('Invalid or missing email address');
		}

		if (empty($data['contact_subject']))
		{
			throw new InvalidArgumentException('Invalid or missing message subject');
		}

		if (empty($data['contact_message']))
		{
			throw new InvalidArgumentException('Invalid or missing message body');
		}

		$this->id = $id;
		$this->data = $data;

		parent::__construct();
	}
}