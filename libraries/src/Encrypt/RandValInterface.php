<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Encrypt;

defined('JPATH_PLATFORM') or die;

interface RandValInterface
{
	/**
	 *
	 * Returns a cryptographically secure random value.
	 *
	 * @return string
	 *
	 */
	public function generate();
}