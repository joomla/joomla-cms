<?php
/**
 * @package     Joomla.Framework
 * @subpackage  Service Layer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Service\QueryBase;

/**
 * Contact parameters query.
 * 
 * @since  __DEPLOY__
 */
final class ContactQueryParams extends QueryBase
{
	/**
	 * Constructor.
	 * 
	 * @param   JValueContactid  $id  Contact id.
	 */
	public function __construct(JValueContactid $id)
	{
		$this->contactId = $id;

		parent::__construct();
	}
}
