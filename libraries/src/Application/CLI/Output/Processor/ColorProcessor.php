<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application\CLI\Output\Processor;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CLI\ColorStyle;

/**
 * Command line output processor supporting ANSI-colored output
 *
 * @since       4.0.0
 * @deprecated  5.0  Use the `joomla/console` package instead
 */
class ColorProcessor implements ProcessorInterface
{
	/**
	 * Flag to remove color codes from the output
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	public $noColors = false;

	/**
	 * Regex to match tags
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $tagFilter = '/<([a-z=;]+)>(.*?)<\/\\1>/s';

	/**
	 * Regex used for removing color codes
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected static $stripFilter = '/<[\/]?[a-z=;]+>/';

	/**
	 * Array of ColorStyle objects
	 *
	 * @var    ColorStyle[]
	 * @since  4.0.0
	 */
	protected $styles = [];

	/**
	 * Class constructor
	 *
	 * @param   boolean  $noColors  Defines non-colored mode on construct
	 *
	 * @since   4.0.0
	 */
	public function __construct($noColors = null)
	{
		if ($noColors === null)
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
	 * @return  $this
	 *
	 * @since   4.0.0
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
	 * @since   4.0.0
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
	 * @since   4.0.0
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
			if (\array_key_exists($matches[1][$i], $this->styles))
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
	 * @since   4.0.0
	 */
	private function replaceColors($text, $tag, $match, ColorStyle $style)
	{
		$replace = $this->noColors
			? $match
			: "\033[" . $style . 'm' . $match . "\033[0m";

		return str_replace('<' . $tag . '>' . $match . '</' . $tag . '>', $replace, $text);
	}

	/**
	 * Adds predefined color styles to the ColorProcessor object
	 *
	 * @return  $this
	 *
	 * @since   4.0.0
	 */
	private function addPredefinedStyles()
	{
		$this->addStyle(
			'info',
			new ColorStyle('green', '', ['bold'])
		);

		$this->addStyle(
			'comment',
			new ColorStyle('yellow', '', ['bold'])
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
