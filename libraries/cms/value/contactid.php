<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Service\Immutable;

/**
 * Contact id value object.
 * 
 * @since  __DEPLOY__
 */
class JValueContactid extends Immutable
{
	/**
	 * Constructor.
	 * 
	 * @param   integer  $contactId  Contact id.
	 */
	public function __construct($contactId)
	{
		if (!is_numeric($contactId) || $contactId <= 0)
		{
			throw new InvalidArgumentException('Invalid contact id');
		}

		$this->id = $contactId;

		parent::__construct();
	}
}
