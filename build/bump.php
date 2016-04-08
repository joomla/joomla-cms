<?php
/**
 * Script used to make a version bump
 * Updates all versions xmls and version.php
 *
 * Usage:
 * 1. php build/bump.php -v <version>
 *
 * Examples:
 * - php build/bump.php -v 3.6.0-dev
 * - php build/bump.php -v 3.6.0-beta1
 * - php build/bump.php -v 3.6.0-beta2
 * - php build/bump.php -v 3.6.0-rc1
 * - php build/bump.php -v 3.6.0
 * - /usr/bin/php /path/to/joomla-cms/build/bump.php -v 3.7.0
 *
 * @package    Joomla.Build
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Functions.
function usage($command)
{
	echo PHP_EOL;
	echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
	echo PHP_TAB . '[options]:'.PHP_EOL;
	echo PHP_TAB . PHP_TAB . '-v <version>:' . PHP_TAB . 'ex: 3.6.0-dev, 3.6.0-beta1, 3.6.0-rc1, 3.6.0' . PHP_EOL;
	echo PHP_EOL;
}

// Constants.
const PHP_TAB = "\t";

// File paths.
$versionFile      = '/libraries/cms/version/version.php';

$coreXmlFiles     = array(
			'/administrator/manifests/files/joomla.xml',
			);

$languageXmlFiles = array(
			'/administrator/manifests/packages/pkg_en-GB.xml',
			'/language/en-GB/en-GB.xml',
			'/language/en-GB/install.xml',
			'/administrator/language/en-GB/en-GB.xml',
			'/administrator/language/en-GB/install.xml',
			'/installation/language/en-GB/en-GB.xml',
			);

// Check arguments (exit if incorrect cli arguments).
$opts = getopt("v:");

if (empty($opts['v']))
{
	usage($argv[0]);
	die();
}

// Check version string (exit if not correct).
$versionParts = explode('-', $opts['v']);

if (!preg_match('#^[0-9]+\.[0-9]+\.[0-9]+$#', $versionParts[0]))
{
	usage($argv[0]);
	die();
}

if (isset($versionParts[1]) && !preg_match('#(dev|alpha|beta|rc)[0-9]*#', $versionParts[1]))
{
	usage($argv[0]);
	die();
}

// Make sure we use the correct language and timezone.
setlocale(LC_ALL, 'en_GB');
date_default_timezone_set('Europe/London');

// Make sure file and folder permissions are set correctly
umask(022);
$dev_status = 'Stable';

if (!isset($versionParts[1]))
{
	$versionParts[1] = '';
}
else
{
	if (preg_match('#^dev#', $versionParts[1]))
	{
		$dev_status = 'Development';
	}
	elseif (preg_match('#^alpha#', $versionParts[1]))
	{
		$dev_status = 'Alpha';
	}
	elseif (preg_match('#^beta#', $versionParts[1]))
	{
		$dev_status = 'Beta';
	}
	elseif (preg_match('#^rc#', $versionParts[1]))
	{
		$dev_status = 'Release Candidate';
	}
}

// Set version properties.
$versionSubParts = explode('.', $versionParts[0]);

$version = array(
		'main'       => $versionSubParts[0] . '.' . $versionSubParts[1],
		'release'    => $versionSubParts[0] . '.' . $versionSubParts[1] . '.' . $versionSubParts[2],
		'dev'        => $versionParts[1],
		'dev_devel'  => $versionSubParts[2] . (!empty($versionParts[1]) ? '-' . $versionParts[1] : ''),
		'dev_status' => $dev_status,
		'build'      => '',
		'reldate'    => date('j-F-Y'),
		'reltime'    => date('H:i'),
		'reltz'      => 'GMT',
		'credate'    => date('F Y'),
		);

// Prints version information.
echo PHP_EOL;
echo 'Version data:'. PHP_EOL;
echo '- Main:' . PHP_TAB . PHP_TAB . PHP_TAB . $version['main'] . PHP_EOL;
echo '- Release:' . PHP_TAB . PHP_TAB . $version['release'] . PHP_EOL;
echo '- Full:'  . PHP_TAB . PHP_TAB . PHP_TAB . $version['main'] . '.' . $version['dev_devel'] . PHP_EOL;
echo '- Build:' . PHP_TAB . PHP_TAB . $version['build'] . PHP_EOL;
echo '- Dev Level:' . PHP_TAB . PHP_TAB . $version['dev_devel'] . PHP_EOL;
echo '- Dev Status:' . PHP_TAB . PHP_TAB . $version['dev_status'] . PHP_EOL;
echo '- Release date:' . PHP_TAB . PHP_TAB . $version['reldate'] . PHP_EOL;
echo '- Release time:' . PHP_TAB . PHP_TAB . $version['reltime'] . PHP_EOL;
echo '- Release timezone:'  . PHP_TAB . $version['reltz'] . PHP_EOL;
echo '- Creation date:' . PHP_TAB . $version['credate'] . PHP_EOL;
echo PHP_EOL;

$rootPath = dirname(__DIR__);

// Updates the version in version class.
if (file_exists($rootPath . $versionFile))
{
	$fileContents = file_get_contents($rootPath . $versionFile);
	$fileContents = preg_replace("#RELEASE\s*=\s*'[^\']*'#", "RELEASE = '" . $version['main'] . "'", $fileContents);
	$fileContents = preg_replace("#DEV_LEVEL\s*=\s*'[^\']*'#", "DEV_LEVEL = '" . $version['dev_devel'] . "'", $fileContents);
	$fileContents = preg_replace("#DEV_STATUS\s*=\s*'[^\']*'#", "DEV_STATUS = '" . $version['dev_status'] . "'", $fileContents);
	$fileContents = preg_replace("#BUILD\s*=\s*'[^\']*'#", "BUILD = ''", $fileContents);
	$fileContents = preg_replace("#RELDATE\s*=\s*'[^\']*'#", "RELDATE = '" . $version['reldate'] . "'", $fileContents);
	$fileContents = preg_replace("#RELTIME\s*=\s*'[^\']*'#", "RELTIME = '" . $version['reltime'] . "'", $fileContents);
	$fileContents = preg_replace("#RELTZ\s*=\s*'[^\']*'#", "RELTZ = '" . $version['reltz'] . "'", $fileContents);
	file_put_contents($rootPath . $versionFile, $fileContents);
}

// Updates the version and creation date in core xml files.
foreach ($coreXmlFiles as $coreXmlFile)
{
	if (file_exists($rootPath . $coreXmlFile))
	{
		$fileContents = file_get_contents($rootPath . $coreXmlFile);
		$fileContents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['main'] . '.' . $version['dev_devel'] . '</version>', $fileContents);
		$fileContents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['credate'] . '</creationDate>', $fileContents);
		file_put_contents($rootPath . $coreXmlFile, $fileContents);
	}
}

// Updates the version and creation date in language xml files.
foreach ($languageXmlFiles as $languageXmlFile)
{
	if (file_exists($rootPath . $languageXmlFile))
	{
		$fileContents = file_get_contents($rootPath . $languageXmlFile);
		$fileContents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['release'] . '.0</version>', $fileContents);
		$fileContents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['credate'] . '</creationDate>', $fileContents);
		file_put_contents($rootPath . $languageXmlFile, $fileContents);
	}
}

echo 'Version bump complete!' . PHP_EOL;
