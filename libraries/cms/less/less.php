<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  LESS
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for lessc
 *
 * @package     Joomla.Libraries
 * @subpackage  Less
 * @since       3.4
 * @deprecated  4.0  without replacement
 */
class JLess extends lessc
{
	/**
	 * Constructor
	 *
	 * @param   string                 $fname      Filename to process
	 * @param   \JLessFormatterJoomla  $formatter  Formatter object
	 *
	 * @since   3.4
	 */
	public function __construct($fname = null, $formatter = null)
	{
		parent::__construct($fname);

		if ($formatter === null)
		{
			$formatter = new JLessFormatterJoomla;
		}

		$this->setFormatter($formatter);
	}

	/**
	 * Override compile to reset $this->allParsedFiles array to allow
	 * parsing multiple files/strings using same imports.
	 * PR: https://github.com/leafo/lessphp/pull/607
	 *
	 * For documentation on this please see /vendor/leafo/lessc.inc.php
	 *
	 * @param   string  $string  LESS string to parse.
	 * @param   string  $name    The sourceName used for error messages.
	 *
	 * @return  string  $out     The compiled css output.
	 */
	public function compile($string, $name = null)
	{
		$this->allParsedFiles = array();

		return parent::compile($string, $name);
	}
}
