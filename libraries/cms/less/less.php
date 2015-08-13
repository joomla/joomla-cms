<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  LESS
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for lessc
 *
 * @package     Joomla.Libraries
 * @subpackage  Less
 * @since       3.4
 */
class JLess extends lessc
{
	/**
	 * Constructor
	 *
	 * @param   string  $fname      Filename to process
	 * @param   mided   $formatter  Formatter object
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
	 * Override compile because $this->allParsedFiles needs to be an array
	 * For documentation on this please see /vendor/leafo/lessc.inc.php
	 *
	 * @param   string   $string
	 * @param   string   $name
	 *
	 * @return string $out
	 */
	public function compile($string, $name = null)
	{
		$locale = setlocale(LC_NUMERIC, 0);
		setlocale(LC_NUMERIC, "C");

		$this->parser = $this->makeParser($name);
		$root = $this->parser->parse($string);

		$this->env = null;
		$this->scope = null;
		$this->allParsedFiles = array();

		$this->formatter = $this->newFormatter();

		if (!empty($this->registeredVars))
		{
			$this->injectVariables($this->registeredVars);
		}

		// Used for error messages
		$this->sourceParser = $this->parser;
		$this->compileBlock($root);

		ob_start();
		$this->formatter->block($this->scope);
		$out = ob_get_clean();
		setlocale(LC_NUMERIC, $locale);
		return $out;
	}
}
