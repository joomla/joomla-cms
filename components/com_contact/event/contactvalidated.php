<?php
/**
 * @package     Joomla.Framework
 * @subpackage  Service Layer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Service\EventBase;

final class ContactEventContactvalidated extends EventBase
{
	public function __construct($id, array $data, $contact)
	{
		$this->id = $id;
		$this->data = $data;
		$this->contact = $contact;

		parent::__construct();
	}
}