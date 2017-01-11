<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Class with general helper methods for testing
 *
 * @since  3.5
 */
class TestHelper
{
	/**
	 * Internal flag to track if the deprecation handler has been registered
	 *
	 * @var    boolean
	 * @since  3.5
	 */
	private static $isDeprecationHandlerRegistered = false;

	/**
	 * Registers a message handler to catch deprecation errors
	 *
	 * This method is based on \Symfony\Bridge\PhpUnit\DeprecationErrorHandler::register()
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public static function registerDeprecationHandler()
	{
		if (self::$isDeprecationHandlerRegistered)
		{
			return;
		}

		$deprecations = array(
			'phpCount'  => 0,
			'userCount' => 0,
			'php'       => array(),
			'user'      => array(),
		);

		$deprecationHandler = function ($type, $msg, $file, $line, $context) use (&$deprecations)
		{
			// Check if the type is E_DEPRECATED or E_USER_DEPRECATED
			if (!in_array($type, array(E_DEPRECATED, E_USER_DEPRECATED)))
			{
				return PHPUnit_Util_ErrorHandler::handleError($type, $msg, $file, $line, $context);
			}

			$trace = debug_backtrace(PHP_VERSION_ID >= 50400 ? DEBUG_BACKTRACE_IGNORE_ARGS | DEBUG_BACKTRACE_PROVIDE_OBJECT : true);

			$i = count($trace);

			while (isset($trace[--$i]['class']) && ('ReflectionMethod' === $trace[$i]['class'] || 0 === strpos($trace[$i]['class'], 'PHPUnit_')))
			{
				// Nothing to do
			}

			$group = $type === E_USER_DEPRECATED ? 'user' : 'php';

			if (isset($trace[$i]['object']) || isset($trace[$i]['class']))
			{
				$class  = isset($trace[$i]['object']) ? get_class($trace[$i]['object']) : $trace[$i]['class'];
				$method = $trace[$i]['function'];

				$ref = &$deprecations[$group][$msg]['count'];
				$ref++;
				$ref = &$deprecations[$group][$msg][$class . '::' . $method];
				$ref++;
			}
			else
			{
				$ref = &$deprecations[$group][$msg]['count'];
				$ref++;
			}

			$deprecations[$group . 'Count']++;
		};

		$oldErrorHandler = set_error_handler($deprecationHandler);

		if (null !== $oldErrorHandler)
		{
			restore_error_handler();

			if (array('PHPUnit_Util_ErrorHandler', 'handleError') === $oldErrorHandler)
			{
				restore_error_handler();
				self::register();
			}
		}
		else
		{
			self::$isDeprecationHandlerRegistered = true;

			if (self::hasColorSupport())
			{
				$colorize = function ($str, $red)
				{
					$color = $red ? '41;37' : '43;30';

					return "\x1B[{$color}m{$str}\x1B[0m";
				};
			}
			else
			{
				$colorize = function ($str)
				{
					return $str;
				};
			}

			register_shutdown_function(function () use (&$deprecations, $deprecationHandler, $colorize)
			{
				$currErrorHandler = set_error_handler('var_dump');
				restore_error_handler();

				if ($currErrorHandler !== $deprecationHandler)
				{
					echo "\n", $colorize('THE ERROR HANDLER HAS CHANGED!', true), "\n";
				}

				$cmp = function ($a, $b)
				{
					return $b['count'] - $a['count'];
				};

				foreach (array('php', 'user') as $group)
				{
					if ($deprecations[$group . 'Count'])
					{
						echo "\n", $colorize(sprintf('%s deprecation notices (%d)', ucfirst($group), $deprecations[$group . 'Count']), true), "\n";

						uasort($deprecations[$group], $cmp);

						foreach ($deprecations[$group] as $msg => $notices)
						{
							echo "\n", rtrim($msg, '.'), ': ', $notices['count'], "x\n";

							arsort($notices);

							foreach ($notices as $method => $count)
							{
								if ('count' !== $method)
								{
									echo '    ', $count, 'x in ', preg_replace('/(.*)\\\\(.*?::.*?)$/', '$2 from $1', $method), "\n";
								}
							}
						}
					}
				}

				if (!empty($notices))
				{
					echo "\n";
				}
			});
		}
	}

	/**
	 * Adds a logger to include Joomla deprecations in the unit test results
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public static function registerDeprecationLogger()
	{
		JLog::addLogger(
			array(
				'logger'   => 'callback',
				'callback' => function (JLogEntry $entry)
				{
					@trigger_error($entry->message, E_USER_DEPRECATED);
				},
			),
			JLog::ALL,
			array('deprecated')
		);
	}

	/**
	 * Adds optional logging support for the unit test run
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public static function registerLogger()
	{
		if (defined('JOOMLA_TEST_LOGGING') && JOOMLA_TEST_LOGGING === 'yes')
		{
			JLog::addLogger(
				array(
					'logger'         => 'formattedtext',
					'text_file'      => 'unit_test.php',
					'text_file_path' => dirname(dirname(__DIR__)) . '/tmp'
				)
			);
		}
	}

	/**
	 * Detects if the CLI output supports color codes
	 *
	 * This method is based on \Symfony\Bridge\PhpUnit\DeprecationErrorHandler::register()
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	private static function hasColorSupport()
	{
		if (DIRECTORY_SEPARATOR === '\\')
		{
			return getenv('ANSICON') !== false || getenv('ConEmuANSI') === 'ON';
		}

		return defined('STDOUT') && function_exists('posix_isatty') && @posix_isatty(STDOUT);
	}
}
