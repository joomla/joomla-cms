<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application\Cli\Output\Processor;

use Joomla\Application\Cli\ColorStyle;
use Joomla\Application\Cli\Output\Stdout;

/**
 * Class ColorProcessor.
 *
 * @since  1.0
 */
class ColorProcessor implements ProcessorInterface
{
	/**
	 * Flag to remove color codes from the output
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	public $noColors = false;

	/**
	 * Regex to match tags
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $tagFilter = '/<([a-z=;]+)>(.*?)<\/\\1>/s';

	/**
	 * Regex used for removing color codes
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected static $stripFilter = '/<[\/]?[a-z=;]+>/';

	/**
	 * Array of ColorStyle objects
	 *
	 * @var    array
	 * @since  1.0
	 */
	protected $styles = array();

	/**
	 * Class constructor
	 *
	 * @param   boolean  $noColors  Defines non-colored mode on construct
	 *
	 * @since  1.1.0
	 */
	public function __construct($noColors = null)
	{
		if (is_null($noColors))
		{
			/*
			 * By default windows cmd.exe and PowerShell does not support ANSI-colored output
			 * if the variable is not set explicitly colors should be disabled on Windows
			 */
			$noColors = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
		}

		$this->noColors = $noColors;

		$this->addPredefinedStyles();
	}

	/**
	 * Add a style.
	 *
	 * @param   string      $name   The style name.
	 * @param   ColorStyle  $style  The color style.
	 *
	 * @return  ColorProcessor  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	public function addStyle($name, ColorStyle $style)
	{
		$this->styles[$name] = $style;

		return $this;
	}

	/**
	 * Strip color tags from a string.
	 *
	 * @param   string  $string  The string.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public static function stripColors($string)
	{
		return preg_replace(static::$stripFilter, '', $string);
	}

	/**
	 * Process a string.
	 *
	 * @param   string  $string  The string to process.
	 *
	 * @return  string
	 *
	 * @since   1.0
	 */
	public function process($string)
	{
		preg_match_all($this->tagFilter, $string, $matches);

		if (!$matches)
		{
			return $string;
		}

		foreach ($matches[0] as $i => $m)
		{
			if (array_key_exists($matches[1][$i], $this->styles))
			{
				$string = $this->replaceColors($string, $matches[1][$i], $matches[2][$i], $this->styles[$matches[1][$i]]);
			}
			// Custom format
			elseif (strpos($matches[1][$i], '='))
			{
				$string = $this->replaceColors($string, $matches[1][$i], $matches[2][$i], ColorStyle::fromString($matches[1][$i]));
			}
		}

		return $string;
	}

	/**
	 * Replace color tags in a string.
	 *
	 * @param   string      $text   The original text.
	 * @param   string      $tag    The matched tag.
	 * @param   string      $match  The match.
	 * @param   ColorStyle  $style  The color style to apply.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	private function replaceColors($text, $tag, $match, Colorstyle $style)
	{
		$replace = $this->noColors
			? $match
			: "\033[" . $style . "m" . $match . "\033[0m";

		return str_replace('<' . $tag . '>' . $match . '</' . $tag . '>', $replace, $text);
	}

	/**
	 * Adds predefined color styles to the ColorProcessor object
	 *
	 * @return  Stdout  Instance of $this to allow chaining.
	 *
	 * @since   1.0
	 */
	private function addPredefinedStyles()
	{
		$this->addStyle(
			'info',
			new ColorStyle('green', '', array('bold'))
		);

		$this->addStyle(
			'comment',
			new ColorStyle('yellow', '', array('bold'))
		);

		$this->addStyle(
			'question',
			new ColorStyle('black', 'cyan')
		);

		$this->addStyle(
			'error',
			new ColorStyle('white', 'red')
		);

		return $this;
	}
}
