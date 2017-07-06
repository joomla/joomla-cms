<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Deprecated class placeholder. You should use JAccessRules instead.
 *
 * @since       1.6
 * @deprecated  2.5
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
	 * @since   1.6
	 * @deprecated  2.5
	 */
	public function __construct($input = '')
	{
		JLog::add('JRules is deprecated. Use JAccessRules instead.', JLog::WARNING, 'deprecated');
		parent::__construct($input);
	}
}
