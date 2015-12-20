<?php
/**
 * @package     Joomla.Framework
 * @subpackage  Service Layer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Service\EventBase;

final class ContactEventFormvalidationerroroccurred extends EventBase
{
	/**
	 * Constructor.
	 * 
	 * @param   integer  $id      Contact id.
	 * @param   array    $data    Array of data fields.
	 * @param   array    $errors  Array of error messages.
	 * 
	 */
	public function __construct($id, array $data, array $errors)
	{
		$this->id = $id;
		$this->data = $data;
		$this->errors = $errors;

		parent::__construct();
	}
}