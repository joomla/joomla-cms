<?php
/**
 * @package     Joomla.Framework
 * @subpackage  Service Layer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Service\Command;

/**
 * Request contact command.
 * 
 * @since  __DEPLOY__
 */
final class ContactCommandRequestcontact extends Command
{
	/**
	 * Constructor.
	 * 
	 * @param   JValueContactid  $id    Contact id.
	 * @param   array            $data  Array of contact request information.
	 */
	public function __construct(JValueContactid $id, array $data)
	{
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

		$this->contactId = $id;
		$this->data = $data;

		parent::__construct();
	}
}
