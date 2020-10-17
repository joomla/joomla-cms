<?php
/**
 * Script used to make a version bump
 * Updates all versions xmls and version.php
 *
 * Usage: php build/bump.php -v <version> -c <codename>
 *
 * Examples:
 * - php build/bump.php -v 3.6.0-dev
 * - php build/bump.php -v 3.6.0-beta1
 * - php build/bump.php -v 3.6.0-beta1-dev
 * - php build/bump.php -v 3.6.0-beta2
 * - php build/bump.php -v 3.6.0-rc1
 * - php build/bump.php -v 3.6.0
 * - php build/bump.php -v 3.6.0 -c Unicorn
 * - php build/bump.php -v 3.6.0 -c "Custom Codename"
 * - /usr/bin/php /path/to/joomla-cms/build/bump.php -v 3.7.0
 *
 * @package    Joomla.Build
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Functions.
function usage($command)
{
	echo PHP_EOL;
	echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
	echo PHP_TAB . '[options]:'.PHP_EOL;
	echo PHP_TAB . PHP_TAB . '-v <version>:' . PHP_TAB . 'Version (ex: 3.6.0-dev, 3.6.0-beta1, 3.6.0-beta1-dev, 3.6.0-rc1, 3.6.0)' . PHP_EOL;
	echo PHP_TAB . PHP_TAB . '-c <codename>:' . PHP_TAB . 'Codename [optional] (ex: Unicorn)' . PHP_EOL;
	echo PHP_EOL;
}

// Constants.
const PHP_TAB = "\t";

// File paths.
$versionFile = '/libraries/src/Version.php';

$coreXmlFiles = [
	'/administrator/manifests/files/joomla.xml',
];

$languageXmlFiles = [
	'/language/en-GB/langmetadata.xml',
	'/language/en-GB/install.xml',
	'/administrator/language/en-GB/langmetadata.xml',
	'/administrator/language/en-GB/install.xml',
	'/installation/language/en-GB/en-GB.xml',
	'/api/language/en-GB/langmetadata.xml'
];

$languagePackXmlFile = '/administrator/manifests/packages/pkg_en-GB.xml';

$antJobFile = '/build.xml';

$readMeFiles = [
	'/README.md',
	'/README.txt',
];

/*
 * Change copyright date exclusions.
 * Some systems may try to scan the .git directory, exclude it.
 * Also exclude build resources such as the packaging space or the API documentation build.
 */
$directoryLoopExcludeDirectories = [
	'/.git',
	'/build/api/',
	'/build/coverage/',
	'/build/tmp/',
	'/libraries/vendor/',
	'/libraries/php-encryption/',
	'/libraries/phpass/',
];

$directoryLoopExcludeFiles = [
];

// Check arguments (exit if incorrect cli arguments).
$opts = getopt("v:c:");

if (empty($opts['v']))
{
	usage($argv[0]);
	die();
}

// Check version string (exit if not correct).
$versionParts = explode('-', $opts['v']);

if (!preg_match('#^\d+(?:\.\d+){2}$#', $versionParts[0]))
{
	usage($argv[0]);
	die();
}

// Get version dev status.
if (isset($versionParts[1]))
{
	if (!preg_match('#(dev|alpha|beta|rc)[0-9]*#', $versionParts[1], $stageMatch))
	{
		usage($argv[0]);
		die();
	}
}
else
{
	$versionParts[1] = '';
}
$stageTranslations = [
	''      => 'Stable',
	'dev'   => 'Development',
	'alpha' => 'Alpha',
	'beta'  => 'Beta',
	'rc'    => 'Release Candidate',
];
$dev_status = $stageTranslations[$stageMatch[1] ?? $versionParts[1]] ?? 'Stable';

if (isset($versionParts[2]))
{
	if ($versionParts[2] !== 'dev')
	{
		usage($argv[0]);
		die();
	}
	$dev_status = 'Development';
}
else
{
	$versionParts[2] = '';
}

// Make sure we use the correct language and timezone.
setlocale(LC_ALL, 'en_GB');
date_default_timezone_set('Europe/London');

// Make sure file and folder permissions are set correctly.
umask(022);

// Set version properties.
$versionSubParts = explode('.', $versionParts[0]);

$version = [
	'main'       => $versionSubParts[0] . '.' . $versionSubParts[1],
	'major'      => $versionSubParts[0],
	'minor'      => $versionSubParts[1],
	'patch'      => $versionSubParts[2],
	'extra'      => ($versionParts[1] ?: '') . ($versionParts[1] && $versionParts[2] ? '-' : '') . ($versionParts[2] ?: ''),
	'release'    => $versionSubParts[0] . '.' . $versionSubParts[1] . '.' . $versionSubParts[2],
	'dev_devel'  => $versionSubParts[2] . ($versionParts[1] ? '-' . $versionParts[1] : '') . ($versionParts[2] ? '-' . $versionParts[2] : ''),
	'dev_status' => $dev_status,
	'build'      => '',
	'reldate'    => date('j-F-Y'),
	'reltime'    => date('H:i'),
	'reltz'      => 'GMT',
	'credate'    => date('F Y'),
];

// Version Codename.
if (!empty($opts['c']))
{
	$version['codename'] = trim($opts['c']);
}

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
if (!empty($version['codename']))
{
	echo '- Codename:' . PHP_TAB . PHP_TAB . $version['codename'] . PHP_EOL;
}
echo PHP_EOL;

$rootPath = dirname(__DIR__);

// Updates the version in version class.
if (file_exists($rootPath . $versionFile))
{
	$fileContents = preg_replace(
		[
			"#MAJOR_VERSION\s*=\s*\K[^;]*#",
			"#MINOR_VERSION\s*=\s*\K[^;]*#",
			"#PATCH_VERSION\s*=\s*\K[^;]*#",
			"#EXTRA_VERSION\s*=\s*'\K[^\']*#",
			"#RELEASE\s*=\s*'\K[^\']*#",
			"#DEV_LEVEL\s*=\s*'\K[^\']*#",
			"#DEV_STATUS\s*=\s*'\K[^\']*#",
			"#BUILD\s*=\s*'\K[^\']*#",
			"#RELDATE\s*=\s*'\K[^\']*#",
			"#RELTIME\s*=\s*'\K[^\']*#",
			"#RELTZ\s*=\s*'\K[^\']*#",
		],
		[
			$version['major'],
			$version['minor'],
			$version['patch'],
			$version['extra'],
			$version['main'],
			$version['dev_devel'],
			$version['dev_status'],
			$version['build'],
			$version['reldate'],
			$version['reltime'],
			$version['reltz'],
		],
		file_get_contents($rootPath . $versionFile)
	);

	if (!empty($version['codename']))
	{
		$fileContents = preg_replace("#CODENAME\s*=\s*'\K[^\']*#", $version['codename'], $fileContents);
	}
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
		$fileContents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['release'] . '</version>', $fileContents);
		$fileContents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['credate'] . '</creationDate>', $fileContents);
		file_put_contents($rootPath . $languageXmlFile, $fileContents);
	}
}

// Updates the version and creation date in language package xml file.
if (file_exists($rootPath . $languagePackXmlFile))
{
	$fileContents = file_get_contents($rootPath . $languagePackXmlFile);
	$fileContents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['release'] . '.1</version>', $fileContents);
	$fileContents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['credate'] . '</creationDate>', $fileContents);
	file_put_contents($rootPath . $languagePackXmlFile, $fileContents);
}

// Updates the version for the `phpdoc` task in the Ant job file.
if (file_exists($rootPath . $antJobFile))
{
	$fileContents = file_get_contents($rootPath . $antJobFile);
	$fileContents = preg_replace('#<arg value="Joomla! CMS \S* API" />#', '<arg value="Joomla! CMS ' . $version['main'] . ' API" />', $fileContents);
	file_put_contents($rootPath . $antJobFile, $fileContents);
}

// Updates the version in readme files.
foreach ($readMeFiles as $readMeFile)
{
	if (file_exists($rootPath . $readMeFile))
	{
		file_put_contents(
			$rootPath . $readMeFile,
			preg_replace(
				[
					'#Joomla! \K\d+\.\d+(?= \[?version)#',
					'#Joomla_\K\d+\.\d+(?=_version)#',
				],
				$version['main'],
				file_get_contents($rootPath . $readMeFile)
			)
		);
	}
}

// Updates the copyright date in core files.
$changedFilesCopyrightDate = 0;
$changedFilesSinceVersion  = 0;
$year                      = date('Y');
$directory                 = new \RecursiveDirectoryIterator($rootPath);
$iterator                  = new \RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

foreach ($iterator as $file)
{
	if ($file->isFile())
	{
		$filePath     = $file->getPathname();
		$relativePath = str_replace($rootPath, '', $filePath);

		// Exclude certain extensions.
		if (preg_match('#\.(png|jpeg|jpg|gif|bmp|ico|webp|svg|woff|woff2|ttf|eot)$#', $filePath))
		{
			continue;
		}

		// Exclude certain files.
		if (in_array($relativePath, $directoryLoopExcludeFiles))
		{
			continue;
		}

		// Exclude certain directories.
		foreach ($directoryLoopExcludeDirectories as $excludeDirectory)
		{
			if (strpos($relativePath, $excludeDirectory) === 0)
			{
				continue 2;
			}
		}

		// Load the file.
		$fileContents = file_get_contents($filePath);

		// Change the copyright date if string qualifies.
		$fileContents = preg_replace(
			'#2005\s+-\s+(?!' . $year . ')\K\d{4}(?=\s+Open\s+Source\s+Matters)#',
			$year,
			$fileContents,
			1,
			$count
		);
		$isFileContentChanged = (bool)$count;
		$changedFilesCopyrightDate += $count;

		// Check if need to change the since version.
		if ($relativePath !== '/build/bump.php' && (strpos($fileContents, '#__DEPLOY_VERSION__#') !== false))
		{
			$fileContents = str_replace('__DEPLOY_VERSION__', $version['release'], $fileContents);
			$isFileContentChanged = true;
			++$changedFilesSinceVersion;
		}

		// Save the file.
		if ($isFileContentChanged)
		{
			file_put_contents($filePath, $fileContents);
		}
	}
}

if ($changedFilesCopyrightDate > 0 || $changedFilesSinceVersion > 0)
{
	if ($changedFilesCopyrightDate > 0)
	{
		echo '- Copyright Date changed in ' . $changedFilesCopyrightDate . ' files.' . PHP_EOL;
	}
	if ($changedFilesSinceVersion > 0)
	{
		echo '- Since Version changed in ' . $changedFilesSinceVersion . ' files.' . PHP_EOL;
	}
	echo PHP_EOL;
}

echo 'Version bump complete!' . PHP_EOL;
