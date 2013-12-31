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
 * Deprecated class placeholder. You should use JAccessRules instead.
 *
 * @package     Joomla.Legacy
 * @subpackage  Access
 * @since       11.1
 * @deprecated  12.3 (Platform) & 4.0 (CMS)
 * @codeCoverageIgnore
 */
class JRules extends JAccessRules
{
	/**
	 * Constructor.
	 *
	 * The input array must be in the form: array('action' => array(-42 => true, 3 => true, 4 => false))
	 * or an equivalent JSON encoded string, or an object where properties are arrays.
	 *
	 * @param   mixed  $input  A JSON format string (probably from the database) or a nested array.
	 *
	 * @since   11.1
	 * @deprecated  12.3
	 */
	public function __construct($input = '')
	{
		JLog::add('JRules is deprecated. Use JAccessRules instead.', JLog::WARNING, 'deprecated');
		parent::__construct($input);
	}
}
