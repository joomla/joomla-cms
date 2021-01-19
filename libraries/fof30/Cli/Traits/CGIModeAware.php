<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Cli\Traits;

defined('_JEXEC') || die;

/**
 * CGI Mode detection and workaround
 *
 * Some hosts only give access to the PHP CGI binary, even for running CLI scripts. While problematic, it mostly works.
 * This trait detects PHP-CGI and manipulates $_GET in such a way that we populate the $argv and $argc global variables
 * in the same way that PHP-CLI would set them. This allows the CLI input object to work. Moreover, we unset the PHP
 * execution time limit, if possible, to prevent accidental timeouts.
 *
 * @package FOF30\Cli\Traits
 */
trait CGIModeAware
{
	/**
	 * Detect if we are running under CGI mode. In this case it populates the global $argv and $argc parameters off the
	 * CGI input ($_GET superglobal).
	 */
	private function detectAndWorkAroundCGIMode()
	{
		// This code only executes when running under CGI. So let's detect it first.
		$cgiMode = (!defined('STDOUT') || !defined('STDIN') || !isset($_SERVER['argv']));

		if (!$cgiMode)
		{
			return;
		}

		// CGI mode has a time limit. Unset it to prevent timeouts.
		if (function_exists('set_time_limit'))
		{
			set_time_limit(0);
		}

		// Convert $_GET into the appropriate $argv representation. This allows Input\Cli to work under PHP-CGI.
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

		global $argv, $argc;

		$argv = explode(' ', $query);
		$argc = count($argv);

		$_SERVER['argv'] = $argv;
	}

}
