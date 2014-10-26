<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Deprecated class placeholder. You should use JAccessRule instead.
 *
 * @package     Joomla.Legacy
 * @subpackage  Access
 * @since       11.1
 * @deprecated  12.3 (Platform) & 4.0 (CMS)
 * @codeCoverageIgnore
 */
class JRule extends JAccessRule
{
	/**
	 * Constructor.
	 *
	 * The input array must be in the form: array(-42 => true, 3 => true, 4 => false)
	 * or an equivalent JSON encoded string.
	 *
	 * @param   mixed  $identities  A JSON format string (probably from the database) or a named array.
	 *
	 * @since   11.1
	 * @deprecated  12.3
	 */
	public function __construct($identities)
	{
		JLog::add('JRule is deprecated. Use JAccessRule instead.', JLog::WARNING, 'deprecated');
		parent::__construct($identities);
	}
}
