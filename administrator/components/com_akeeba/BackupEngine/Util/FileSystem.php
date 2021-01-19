<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Engine\Util;

defined('AKEEBAENGINE') || die();

use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;

/**
 * Utility functions related to filesystem objects, e.g. path translation
 */
class FileSystem
{
	/**
	 * Are we running under Windows?
	 *
	 * @var   bool
	 */
	private $isWindows = false;

	/**
	 * Local cache of the platform stock directories
	 *
	 * @var   array|null
	 * @since 7.0.3
	 */
	protected static $stockDirs = null;

	/**
	 * Initialise the object
	 */
	public function __construct()
	{
		$this->isWindows = (DIRECTORY_SEPARATOR == '\\');
	}

	/**
	 * Makes a Windows path more UNIX-like, by turning backslashes to forward slashes.
	 * It takes into account UNC paths, e.g. \\myserver\some\folder becomes
	 * \\myserver/some/folder.
	 *
	 * This function will also fix paths with multiple slashes, e.g. convert /var//www////html to /var/www/html
	 *
	 * @param   string  $p_path  The path to transform
	 *
	 * @return  string
	 */
	public function TranslateWinPath($p_path)
	{
		$is_unc = false;

		if ($this->isWindows)
		{
			// Is this a UNC path?
			$is_unc = (substr($p_path, 0, 2) == '\\\\') || (substr($p_path, 0, 2) == '//');

			// Change potential windows directory separator
			if ((strpos($p_path, '\\') > 0) || (substr($p_path, 0, 1) == '\\'))
			{
				$p_path = strtr($p_path, '\\', '/');
			}
		}

		// Remove multiple slashes
		$p_path = str_replace('///', '/', $p_path);
		$p_path = str_replace('//', '/', $p_path);

		// Fix UNC paths
		if ($is_unc)
		{
			$p_path = '//' . ltrim($p_path, '/');
		}

		return $p_path;
	}

	/**
	 * Removes trailing slash or backslash from a pathname
	 *
	 * @param   string  $path  The path to treat
	 *
	 * @return  string  The path without the trailing slash/backslash
	 */
	public function TrimTrailingSlash($path)
	{
		$newpath = $path;

		if (substr($path, strlen($path) - 1, 1) == '\\')
		{
			$newpath = substr($path, 0, strlen($path) - 1);
		}

		if (substr($path, strlen($path) - 1, 1) == '/')
		{
			$newpath = substr($path, 0, strlen($path) - 1);
		}

		return $newpath;
	}

	/**
	 * Returns an array with the archive name variables and their values. This is used to replace variables in archive
	 * and directory names, etc.
	 *
	 * If there is a non-empty configuration value called volatile.core.archivenamevars with a serialised array it will
	 * be unserialised and used. Otherwise the name variables will be calculated on-the-fly.
	 *
	 * IMPORTANT: These variables do NOT include paths such as [SITEROOT]
	 *
	 * @return  array
	 */
	public function get_archive_name_variables()
	{
		$variables = [];

		$registry   = Factory::getConfiguration();
		$serialized = $registry->get('volatile.core.archivenamevars', null);

		if (!empty($serialized))
		{
			$variables = @unserialize($serialized);
		}

		if (empty($variables) || !is_array($variables))
		{
			$host         = Platform::getInstance()->get_host();
			$version      = defined('AKEEBA_VERSION') ? AKEEBA_VERSION : 'svn';
			$version      = defined('AKEEBABACKUP_VERSION') ? AKEEBABACKUP_VERSION : $version;
			$platformVars = Platform::getInstance()->getPlatformVersion();

			$siteName = $this->stringUrlUnicodeSlug(Platform::getInstance()->get_site_name());

			if (strlen($siteName) > 50)
			{
				$siteName = substr($siteName, 0, 50);
			}

			/**
			 * Time components. Expressed in whatever timezone the Platform decides to use.
			 */
			// Raw timezone, e.g. "EEST"
			$rawTz = Platform::getInstance()->get_local_timestamp("T");
			// Filename-safe timezone, e.g. "eest". Note the lowercase letters.
			$fsSafeTZ = strtolower(str_replace([' ', '/', ':'], ['_', '_', '_'], $rawTz));

			$randVal = new RandomValue();

			$variables = [
				'[DATE]'             => Platform::getInstance()->get_local_timestamp("Ymd"),
				'[YEAR]'             => Platform::getInstance()->get_local_timestamp("Y"),
				'[MONTH]'            => Platform::getInstance()->get_local_timestamp("m"),
				'[DAY]'              => Platform::getInstance()->get_local_timestamp("d"),
				'[TIME]'             => Platform::getInstance()->get_local_timestamp("His"),
				'[TIME_TZ]'          => Platform::getInstance()->get_local_timestamp("His") . $fsSafeTZ,
				'[WEEK]'             => Platform::getInstance()->get_local_timestamp("W"),
				'[WEEKDAY]'          => Platform::getInstance()->get_local_timestamp("l"),
				'[TZ]'               => $fsSafeTZ,
				'[TZ_RAW]'           => $rawTz,
				'[GMT_OFFSET]'       => Platform::getInstance()->get_local_timestamp("O"),
				'[HOST]'             => empty($host) ? 'unknown_host' : $host,
				'[VERSION]'          => $version,
				'[PLATFORM_NAME]'    => $platformVars['name'],
				'[PLATFORM_VERSION]' => $platformVars['version'],
				'[SITENAME]'         => $siteName,
				'[RANDOM]'           => $randVal->generateString(16),
			];
		}

		return $variables;
	}

	/**
	 * Expands the archive name variables in $source. For example "[DATE]-foobar" would be expanded to something
	 * like "141101-foobar". IMPORTANT: These variables do NOT include paths.
	 *
	 * @param   string  $source  The input string, possibly containing variables in the form of [VARIABLE]
	 *
	 * @return  string  The expanded string
	 */
	public function replace_archive_name_variables($source)
	{
		$tagReplacements = $this->get_archive_name_variables();

		return str_replace(array_keys($tagReplacements), array_values($tagReplacements), $source);
	}

	/**
	 * Expand the platform-specific stock directories variables in the input string. For example "[SITEROOT]/foobar"
	 * would be expanded to something like "/var/www/html/mysite/foobar"
	 *
	 * @param   string  $folder               The input string to expand
	 * @param   bool    $translate_win_dirs   Should I translate Windows path separators to UNIX path separators? (default: false)
	 * @param   bool    $trim_trailing_slash  Should I remove the trailing slash (default: false)
	 *
	 * @return  string  The expanded string
	 */
	public function translateStockDirs($folder, $translate_win_dirs = false, $trim_trailing_slash = false)
	{
		if (is_null(self::$stockDirs))
		{
			self::$stockDirs = Platform::getInstance()->get_stock_directories();
		}

		$temp = $folder;

		foreach (self::$stockDirs as $find => $replace)
		{
			$temp = str_replace($find, $replace, $temp);
		}

		if ($translate_win_dirs)
		{
			$temp = $this->TranslateWinPath($temp);
		}

		if ($trim_trailing_slash)
		{
			$temp = $this->TrimTrailingSlash($temp);
		}

		return $temp;
	}

	/**
	 * Rebase a path to the platform filesystem variables (most to least specific).
	 *
	 * This is the inverse procedure of translateStockDirs().
	 *
	 * @param   string  $path
	 *
	 * @return  string
	 * @since   7.3.0
	 */
	public function rebaseFolderToStockDirs(string $path): string
	{
		// Normalize the path
		$path = $this->TrimTrailingSlash($path);
		$path = $this->TranslateWinPath($path);

		// Get the stock directories, normalize them and sort them by longest to shortest
		$stock_directories = Platform::getInstance()->get_stock_directories();

		$stock_directories = array_map(function ($path) {
			$path = $this->TrimTrailingSlash($path);

			return $this->TranslateWinPath($path);
		}, $stock_directories);

		uasort($stock_directories, function ($a, $b) {
			return -($a <=> $b);
		});

		// Start replacing paths with variables
		foreach ($stock_directories as $var => $stockPath)
		{
			if (strpos($path, $stockPath) !== 0)
			{
				continue;
			}

			$path = $var . substr($path, strlen($stockPath));
		}

		return $path;
	}

	/**
	 * Generates a set of files which prevent direct web access or at least web listing of the folder contents.
	 *
	 * This method generates a .htaccess for Apache, Lighttpd and Litespeed; a web.config file for IIS 7 or later; an
	 * index.php, index.html and index.htm file for all other browsers.
	 *
	 * Despite this security precaution it is STRONGLY advised to keep your backup archives in a directory outside the
	 * site's web root as explained in the Security Information chapter of the documentation. This method is designed
	 * to only provide a defence of last resort.
	 *
	 * @param   string  $dir    The output directory to secure against web access
	 * @param   bool    $force  Forcibly overwrite existing files
	 *
	 * @return  void
	 * @since   7.0.3
	 */
	public function ensureNoAccess($dir, $force = false)
	{
		// Create a .htaccess file to prevent all web access (Apache 1.3+, Lightspeed, Lighttpd, ...)
		if (!is_file($dir . '/.htaccess') || $force)
		{
			$htaccess = <<< APACHE
## This file was generated automatically by the Akeeba Backup Engine
##
## DO NOT REMOVE THIS FILE
##
## This file makes sure that your backup output directory is not directly accessible from the web if you are using
## the Apache, Lighttpd and Litespeed web server. This prevents unauthorized access to your backup archive files and
## backup log files. Removing this file could have security implications for your site.
##
## You are strongly advised to never delete or modify any of the files automatically created in this folder by the
## Akeeba Backup Engine, namely:
##
## * .htaccess
## * web.config
## * index.html
## * index.htm
## * index.php
##
<IfModule !mod_authz_core.c>
Order deny,allow
Deny from all
</IfModule>
<IfModule mod_authz_core.c>
  <RequireAll>
    Require all denied
  </RequireAll>
</IfModule>
APACHE;

			@file_put_contents($dir . '/.htaccess', $htaccess);
		}

		// Create a web.config to prevent all web access (IIS 7+)
		if (!is_file($dir . '/web.config') || $force)
		{
			$webConfig = <<< XML
<?xml version="1.0"?>
<!--
This file was generated automatically by the Akeeba Backup Engine

DO NOT REMOVE THIS FILE

This file makes sure that your backup output directory is not directly accessible from the web if you are using the
Microsoft Internet Information Services (IIS) web server, version 7 or later. This prevents unauthorized access to your
backup archive files and backup log files. Removing this file could have security implications for your site.

As noted above, this only works on IIS 7 or later.
See https://www.iis.net/configreference/system.webserver/security/requestfiltering/fileextensions

You are strongly advised to never delete or modify any of the files automatically created in this folder by the
Akeeba Backup Engine, namely:

* .htaccess
* web.config
* index.html
* index.htm
* index.php

-->
<configuration>
    <system.webServer>
        <security>
            <requestFiltering>
                <fileExtensions allowUnlisted="false" >
                    <clear />
                    <add fileExtension=".html" allowed="true"/>
                </fileExtensions>
            </requestFiltering>
        </security>
    </system.webServer>
</configuration>
XML;
			@file_put_contents($dir . '/web.config', $webConfig);
		}

		// Create a blank index.html or index.htm to prevent directory listings (all servers)
		$blankHtml = <<< HTML
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <title>Access Denied</title>
  </head>
  <body>
	  <h1>Access Denied</h1>
  </body>
</html>
HTML;

		if (!is_file($dir . '/index.html') || $force)
		{
			@file_put_contents($dir . '/index.html', $blankHtml);
		}

		if (!is_file($dir . '/index.htm') || $force)
		{
			@file_put_contents($dir . '/index.htm', $blankHtml);
		}

		// Create a default index.php to prevent directory listings with an error (all servers)
		if (!is_file($dir . '/index.php') || $force)
		{
			$deadPHP = '<' . '?' . 'php header(\'HTTP/1.1 403 Forbidden\'); return;' . '?' . ">\n";
			$deadPHP .= <<< TEXT
This file was generated automatically by the Akeeba Backup Engine

DO NOT REMOVE THIS FILE

This file tells your web server to not list the contents of this directory, instead returning an HTTP 403 Forbidden
error. This makes it implausible for a malicious third party to successfully guess the filenames of your backup
archives. Therefore, even if this folder is directly web accessible – despite the .htaccess and web.config file already
put in place by the Akeeba Backup Engine – it will still be reasonably protected against malicious users trying to
download your backup archives.

Please do not remove this file as it could have security implications for your site.

You are strongly advised to never delete or modify any of the files automatically created in this folder by the
Akeeba Backup Engine, namely:

* .htaccess
* web.config
* index.html
* index.htm
* index.php

TEXT;

			@file_put_contents($dir . '/index.php', $deadPHP);
		}
	}

	/**
	 * Convert a string to a (Unicode) slug
	 *
	 * @param   string  $string  String to process
	 *
	 * @return  string  Processed string
	 *
	 * @since   7.5.0
	 */
	public function stringUrlUnicodeSlug(string $string): string
	{
		// Replace double byte whitespaces by single byte (East Asian languages)
		$str = preg_replace('/\xE3\x80\x80/', ' ', $string);

		// Remove any '-' from the string as they will be used as concatenator.
		$str = str_replace('-', ' ', $str);

		// Replace forbidden characters by whitespaces
		$str = preg_replace('#[:\?\#\*"@+=;!><&\.%()\]\/\'\\\\|\[]#', "\x20", $str);

		// Delete all '?'
		$str = str_replace('?', '', $str);

		// Trim white spaces at beginning and end of alias and make lowercase
		$str = trim(strtolower($str));

		// Remove any duplicate whitespace and replace whitespaces by hyphens
		$str = preg_replace('#\x20+#', '-', $str);

		return $str;
	}

}
