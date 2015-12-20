<?php
/**
 * @package     Joomla.Framework
 * @subpackage  Service Layer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Service\QueryBase;

final class ContactQueryParams extends QueryBase
{
	/**
	 * Constructor.
	 * 
	 * @param   integer  $id  Contact id.
	 */
	public function __construct($id)
	{
		if (!is_numeric($id) || $id <= 0)
		{
			throw new InvalidArgumentException('Invalid contact id');
		}

		$this->id = $id;

		parent::__construct();
	}
}