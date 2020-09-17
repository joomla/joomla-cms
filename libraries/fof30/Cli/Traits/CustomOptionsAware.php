<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Cli\Traits;

defined('_JEXEC') || die;

use Joomla\CMS\Filter\InputFilter;

/**
 * Implements a simpler, more straightforward options parser than the Joomla CLI input object. It supports short options
 * when the Joomla CLI input object doesn't. Eventually this will go away and we can use something like Symfony Console
 * instead.
 *
 * @package FOF30\Cli\Traits
 */
trait CustomOptionsAware
{
	/**
	 * POSIX-style CLI options. Access them with through the getOption method.
	 *
	 * @var   array
	 */
	protected static $cliOptions = [];

	/**
	 * Filter object to use for custom options parsing.
	 *
	 * @var    InputFilter
	 */
	protected $filter = null;

	/**
	 * Initializes the custom CLI options parsing
	 *
	 * @return void
	 */
	protected function initialiseCustomOptions()
	{
		// Create a new InputFilter
		$this->filter = InputFilter::getInstance();

		// Parse the POSIX options
		$this->parseOptions();
	}

	/**
	 * Parses POSIX command line options and sets the self::$cliOptions associative array. Each array item contains
	 * a single dimensional array of values. Arguments without a dash are silently ignored.
	 *
	 * This works much better than JInputCli since it allows you to use all valid POSIX ways of defining CLI parameters.
	 *
	 * @return  void
	 */
	protected function parseOptions()
	{
		global $argc, $argv;

		// Workaround for PHP-CGI
		if (!isset($argc) && !isset($argv))
		{
			$query = "";

			if (!empty($_GET))
			{
				foreach ($_GET as $k => $v)
				{
					$query .= " $k";

					if ($v != "")
					{
						$query .= "=$v";
					}
				}
			}

			$query = ltrim($query);
			$argv  = explode(' ', $query);
			$argc  = count($argv);
		}

		$currentName = "";
		$options     = [];

		for ($i = 1; $i < $argc; $i++)
		{
			$argument = $argv[$i];

			$value = $argument;

			if (strpos($argument, "-") === 0)
			{
				$argument = ltrim($argument, '-');

				$name  = $argument;
				$value = null;

				if (strstr($argument, '='))
				{
					[$name, $value] = explode('=', $argument, 2);
				}

				$currentName = $name;

				if (!isset($options[$currentName]) || ($options[$currentName] == null))
				{
					$options[$currentName] = [];
				}
			}

			if ((!is_null($value)) && (!is_null($currentName)))
			{
				$key = null;

				if (strstr($value, '='))
				{
					$parts = explode('=', $value, 2);
					$key   = $parts[0];
					$value = $parts[1];
				}

				$values = $options[$currentName];

				if (is_null($values))
				{
					$values = [];
				}

				if (is_null($key))
				{
					array_push($values, $value);
				}
				else
				{
					$values[$key] = $value;
				}

				$options[$currentName] = $values;
			}
		}

		self::$cliOptions = $options;
	}

	/**
	 * Returns the value of a command line option. This does NOT use JInputCLI. You MUST run parseOptions before.
	 *
	 * @param   string  $key      The full name of the option, e.g. "foobar"
	 * @param   mixed   $default  The default value to return
	 * @param   string  $type     Joomla! filter type, e.g. cmd, int, bool and so on.
	 *
	 * @return  mixed  The value of the option
	 */
	protected function getOption($key, $default = null, $type = 'raw')
	{
		// If the key doesn't exist set it to the default value
		if (!array_key_exists($key, self::$cliOptions))
		{
			self::$cliOptions[$key] = is_array($default) ? $default : [$default];
		}

		$type = strtolower($type);

		if ($type == 'array')
		{
			return self::$cliOptions[$key];
		}

		$value = null;

		if (!empty(self::$cliOptions[$key]))
		{
			$value = self::$cliOptions[$key][0];
		}

		return $this->filterVariable($value, $type);
	}

	/**
	 * Filter a variable using JInputFilter
	 *
	 * @param   mixed   $var   The variable to filter
	 * @param   string  $type  The filter type, default 'cmd'
	 *
	 * @return  mixed  The filtered value
	 */
	protected function filterVariable($var, $type = 'cmd')
	{
		return $this->filter->clean($var, $type);
	}

}
