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
 * Form validation error occurred domain event.
 * 
 * @since  __DEPLOY__
 */
final class ContactEventFormvalidationerroroccurred extends DomainEvent
{
	/**
	 * Constructor.
	 * 
	 * @param   JValueContactid  $id      Contact id.
	 * @param   array            $data    Array of data fields.
	 * @param   array            $errors  Array of error messages.
	 * 
	 * @since  __DEPLOY__
	 */
	public function __construct(JValueContactid $id, array $data, array $errors)
	{
		$this->contactId = $id;
		$this->data = $data;
		$this->errors = $errors;

		parent::__construct();
	}
}
