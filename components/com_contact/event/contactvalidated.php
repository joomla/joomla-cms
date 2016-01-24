<?php
/**
 * @package     Joomla.Framework
 * @subpackage  Service Layer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Service\DomainEvent;

/**
 * Contact validated domain event.
 * 
 * @since  __DEPLOY__
 */
final class ContactEventContactvalidated extends DomainEvent
{
	/**
	 * Constructor.
	 * 
	 * @param   JValueContactid  $id       Contact id.
	 * @param   array            $data     Array of data items.
	 * @param   object           $contact  Contact object.
	 */
	public function __construct(JValueContactid $id, array $data, $contact)
	{
		$this->contactId = $id;
		$this->data = $data;
		$this->contact = $contact;

		parent::__construct();
	}
}
