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
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
$versionFile       = '/libraries/cms/version/version.php';

$coreXmlFiles      = array(
			'/administrator/manifests/files/joomla.xml',
			);

$componentXmlFiles = array(
			'/administrator/components/com_admin/admin.xml',
			'/administrator/components/com_ajax/ajax.xml',
			'/administrator/components/com_banners/banners.xml',
			'/administrator/components/com_cache/cache.xml',
			'/administrator/components/com_categories/categories.xml',
			'/administrator/components/com_checkin/checkin.xml',
			'/administrator/components/com_config/config.xml',
			'/administrator/components/com_contact/contact.xml',
			'/administrator/components/com_content/content.xml',
			'/administrator/components/com_contenthistory/contenthistory.xml',
			'/administrator/components/com_cpanel/cpanel.xml',
			'/administrator/components/com_finder/finder.xml',
			'/administrator/components/com_installer/installer.xml',
			'/administrator/components/com_joomlaupdate/joomlaupdate.xml',
			'/administrator/components/com_languages/languages.xml',
			'/administrator/components/com_login/login.xml',
			'/administrator/components/com_media/media.xml',
			'/administrator/components/com_menus/menus.xml',
			'/administrator/components/com_messages/messages.xml',
			'/administrator/components/com_modules/modules.xml',
			'/administrator/components/com_newsfeeds/newsfeeds.xml',
			'/administrator/components/com_plugins/plugins.xml',
			'/administrator/components/com_postinstall/postinstall.xml',
			'/administrator/components/com_redirect/redirect.xml',
			'/administrator/components/com_search/search.xml',
			'/administrator/components/com_tags/tags.xml',
			'/administrator/components/com_templates/templates.xml',
			'/administrator/components/com_users/users.xml',
			);

$moduleXmlFiles    = array(
			'/administrator/modules/mod_custom/mod_custom.xml',
			'/administrator/modules/mod_feed/mod_feed.xml',
			'/administrator/modules/mod_latest/mod_latest.xml',
			'/administrator/modules/mod_logged/mod_logged.xml',
			'/administrator/modules/mod_login/mod_login.xml',
			'/administrator/modules/mod_menu/mod_menu.xml',
			'/administrator/modules/mod_multilangstatus/mod_multilangstatus.xml',
			'/administrator/modules/mod_popular/mod_popular.xml',
			'/administrator/modules/mod_quickicon/mod_quickicon.xml',
			'/administrator/modules/mod_stats_admin/mod_stats_admin.xml',
			'/administrator/modules/mod_status/mod_status.xml',
			'/administrator/modules/mod_submenu/mod_submenu.xml',
			'/administrator/modules/mod_title/mod_title.xml',
			'/administrator/modules/mod_toolbar/mod_toolbar.xml',
			'/administrator/modules/mod_version/mod_version.xml',
			'/modules/mod_articles_archive/mod_articles_archive.xml',
			'/modules/mod_articles_categories/mod_articles_categories.xml',
			'/modules/mod_articles_category/mod_articles_category.xml',
			'/modules/mod_articles_latest/mod_articles_latest.xml',
			'/modules/mod_articles_news/mod_articles_news.xml',
			'/modules/mod_articles_popular/mod_articles_popular.xml',
			'/modules/mod_banners/mod_banners.xml',
			'/modules/mod_breadcrumbs/mod_breadcrumbs.xml',
			'/modules/mod_custom/mod_custom.xml',
			'/modules/mod_feed/mod_feed.xml',
			'/modules/mod_finder/mod_finder.xml',
			'/modules/mod_footer/mod_footer.xml',
			'/modules/mod_languages/mod_languages.xml',
			'/modules/mod_login/mod_login.xml',
			'/modules/mod_menu/mod_menu.xml',
			'/modules/mod_random_image/mod_random_image.xml',
			'/modules/mod_related_items/mod_related_items.xml',
			'/modules/mod_search/mod_search.xml',
			'/modules/mod_stats/mod_stats.xml',
			'/modules/mod_syndicate/mod_syndicate.xml',
			'/modules/mod_tags_popular/mod_tags_popular.xml',
			'/modules/mod_tags_similar/mod_tags_similar.xml',
			'/modules/mod_users_latest/mod_users_latest.xml',
			'/modules/mod_whosonline/mod_whosonline.xml',
			'/modules/mod_wrapper/mod_wrapper.xml',
			);

$pluginXmlFiles    = array(
			'/plugins/authentication/cookie/cookie.xml',
			'/plugins/authentication/gmail/gmail.xml',
			'/plugins/authentication/joomla/joomla.xml',
			'/plugins/authentication/ldap/ldap.xml',
			'/plugins/captcha/recaptcha/recaptcha.xml',
			'/plugins/content/contact/contact.xml',
			'/plugins/content/emailcloak/emailcloak.xml',
			'/plugins/content/finder/finder.xml',
			'/plugins/content/joomla/joomla.xml',
			'/plugins/content/loadmodule/loadmodule.xml',
			'/plugins/content/pagebreak/pagebreak.xml',
			'/plugins/content/pagenavigation/pagenavigation.xml',
			'/plugins/content/vote/vote.xml',
			'/plugins/editors/none/none.xml',
			'/plugins/editors-xtd/article/article.xml',
			'/plugins/editors-xtd/image/image.xml',
			'/plugins/editors-xtd/module/module.xml',
			'/plugins/editors-xtd/pagebreak/pagebreak.xml',
			'/plugins/editors-xtd/readmore/readmore.xml',
			'/plugins/extension/joomla/joomla.xml',
			'/plugins/finder/categories/categories.xml',
			'/plugins/finder/contacts/contacts.xml',
			'/plugins/finder/content/content.xml',
			'/plugins/finder/newsfeeds/newsfeeds.xml',
			'/plugins/finder/tags/tags.xml',
			'/plugins/quickicon/extensionupdate/extensionupdate.xml',
			'/plugins/quickicon/joomlaupdate/joomlaupdate.xml',
			'/plugins/search/categories/categories.xml',
			'/plugins/search/contacts/contacts.xml',
			'/plugins/search/content/content.xml',
			'/plugins/search/newsfeeds/newsfeeds.xml',
			'/plugins/search/tags/tags.xml',
			'/plugins/system/cache/cache.xml',
			'/plugins/system/debug/debug.xml',
			'/plugins/system/highlight/highlight.xml',
			'/plugins/system/languagecode/languagecode.xml',
			'/plugins/system/languagefilter/languagefilter.xml',
			'/plugins/system/log/log.xml',
			'/plugins/system/logout/logout.xml',
			'/plugins/system/p3p/p3p.xml',
			'/plugins/system/redirect/redirect.xml',
			'/plugins/system/remember/remember.xml',
			'/plugins/system/sef/sef.xml',
			'/plugins/system/stats/stats.xml',
			'/plugins/system/updatenotification/updatenotification.xml',
			'/plugins/twofactorauth/totp/totp.xml',
			'/plugins/twofactorauth/yubikey/yubikey.xml',
			'/plugins/user/contactcreator/contactcreator.xml',
			'/plugins/user/joomla/joomla.xml',
			'/plugins/user/profile/profile.xml',
			'/plugins/installer/folderinstaller/folderinstaller.xml',
			'/plugins/installer/packageinstaller/packageinstaller.xml',
			'/plugins/installer/urlinstaller/urlinstaller.xml',
			);

$templateXmlFiles = array(
			'/administrator/templates/hathor/templateDetails.xml',
			'/administrator/templates/isis/templateDetails.xml',
			'/templates/beez3/templateDetails.xml',
			'/templates/protostar/templateDetails.xml',
			);

$languageXmlFiles  = array(
			'/language/en-GB/en-GB.xml',
			'/language/en-GB/install.xml',
			'/administrator/language/en-GB/en-GB.xml',
			'/administrator/language/en-GB/install.xml',
			'/installation/language/en-GB/en-GB.xml',
			);

$languagePackXmlFile = '/administrator/manifests/packages/pkg_en-GB.xml';

$antJobFile = '/build.xml';

// Check arguments (exit if incorrect cli arguments).
$opts = getopt("v:c:");

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

if (isset($versionParts[2]) && $versionParts[2] !== 'dev')
{
	usage($argv[0]);
	die();
}

// Make sure we use the correct language and timezone.
setlocale(LC_ALL, 'en_GB');
date_default_timezone_set('Europe/London');

// Make sure file and folder permissions are set correctly.
umask(022);

// Get version dev status.
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

if (!isset($versionParts[2]))
{
	$versionParts[2] = '';
}
else
{
	$dev_status = 'Development';
}

// Set version properties.
$versionSubParts = explode('.', $versionParts[0]);

$version = array(
		'main'       => $versionSubParts[0] . '.' . $versionSubParts[1],
		'release'    => $versionSubParts[0] . '.' . $versionSubParts[1] . '.' . $versionSubParts[2],
		'dev_devel'  => $versionSubParts[2] . (!empty($versionParts[1]) ? '-' . $versionParts[1] : '') . (!empty($versionParts[2]) ? '-' . $versionParts[2] : ''),
		'dev_status' => $dev_status,
		'build'      => '',
		'reldate'    => date('j-F-Y'),
		'reltime'    => date('H:i'),
		'reltz'      => 'GMT',
		'credate'    => date('F Y'),
		);

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
	$fileContents = file_get_contents($rootPath . $versionFile);
	$fileContents = preg_replace("#RELEASE\s*=\s*'[^\']*'#", "RELEASE = '" . $version['main'] . "'", $fileContents);
	$fileContents = preg_replace("#DEV_LEVEL\s*=\s*'[^\']*'#", "DEV_LEVEL = '" . $version['dev_devel'] . "'", $fileContents);
	$fileContents = preg_replace("#DEV_STATUS\s*=\s*'[^\']*'#", "DEV_STATUS = '" . $version['dev_status'] . "'", $fileContents);
	$fileContents = preg_replace("#BUILD\s*=\s*'[^\']*'#", "BUILD = '" . $version['build'] . "'", $fileContents);
	$fileContents = preg_replace("#RELDATE\s*=\s*'[^\']*'#", "RELDATE = '" . $version['reldate'] . "'", $fileContents);
	$fileContents = preg_replace("#RELTIME\s*=\s*'[^\']*'#", "RELTIME = '" . $version['reltime'] . "'", $fileContents);
	$fileContents = preg_replace("#RELTZ\s*=\s*'[^\']*'#", "RELTZ = '" . $version['reltz'] . "'", $fileContents);

	if (!empty($version['codename']))
	{
		$fileContents = preg_replace("#CODENAME\s*=\s*'[^\']*'#", "CODENAME = '" . $version['codename'] . "'", $fileContents);
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

// Updates the version and creation date in component xml files.
foreach ($componentXmlFiles as $componentXmlFile)
{
	if (file_exists($rootPath . $componentXmlFile))
	{
		$dirAdmin           = dirname($componentXmlFile);
		$dirSite            = dirname(str_replace("/administrator", "", $dirAdmin));
		$getLastModDirAdmin = filemtime($rootPath . $dirAdmin);
		$getLastModDirSite  = is_dir($rootPath . $dirSite)
							? filemtime($rootPath . $dirSite)
							: $getLastModDirAdmin;
		$getLastModXml      = filemtime($rootPath . $componentXmlFile);

		if ($getLastModXml <= $getLastModDirAdmin
			|| $getLastModXml <= $getLastModDirSite)
		{
			$fileContents = file_get_contents($rootPath . $componentXmlFile);
			$fileContents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['release'] . '</version>', $fileContents);
			$fileContents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['credate'] . '</creationDate>', $fileContents);
			file_put_contents($rootPath . $componentXmlFile, $fileContents);

			echo 'Component update:' . PHP_TAB . 'com_' . basename($componentXmlFile, ".xml") . PHP_EOL;
		}
	}
}

// Updates the version and creation date in module xml files.
foreach ($moduleXmlFiles as $moduleXmlFile)
{
	if (file_exists($rootPath . $moduleXmlFile))
	{
		$dirname       = dirname($moduleXmlFile);
		$getLastModDir = filemtime($rootPath . $dirname);
		$getLastModXml = filemtime($rootPath . $moduleXmlFile);

		if ($getLastModXml <= $getLastModDir)
		{
			$fileContents = file_get_contents($rootPath . $moduleXmlFile);
			$fileContents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['release'] . '</version>', $fileContents);
			$fileContents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['credate'] . '</creationDate>', $fileContents);
			file_put_contents($rootPath . $moduleXmlFile, $fileContents);

			echo 'Module update:' . PHP_TAB . PHP_TAB . basename($moduleXmlFile, ".xml") . PHP_EOL;
		}
	}
}

// Updates the version and creation date in plugin xml files.
foreach ($pluginXmlFiles as $pluginXmlFile)
{
	if (file_exists($rootPath . $pluginXmlFile))
	{
		$dirname       = dirname($pluginXmlFile);
		$getLastModDir = filemtime($rootPath . $dirname);
		$getLastModXml = filemtime($rootPath . $pluginXmlFile);

		if ($getLastModXml <= $getLastModDir)
		{
			$fileContents = file_get_contents($rootPath . $pluginXmlFile);
			$fileContents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['release'] . '</version>', $fileContents);
			$fileContents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['credate'] . '</creationDate>', $fileContents);
			file_put_contents($rootPath . $pluginXmlFile, $fileContents);

			echo 'Plugin update:' . PHP_TAB . PHP_TAB . str_replace(array("/plugins/", basename($pluginXmlFile, ".xml")), "", $dirname)
				. basename($pluginXmlFile, ".xml") . PHP_EOL;
		}
	}
}

// Updates the version and creation date in template xml files.
foreach ($templateXmlFiles as $templateXmlFile)
{
	if (file_exists($rootPath . $templateXmlFile))
	{
		$dirname       = dirname($templateXmlFile);
		$getLastModDir = filemtime($rootPath . $dirname);
		$getLastModXml = filemtime($rootPath . $templateXmlFile);

		if ($getLastModXml <= $getLastModDir)
		{
			$fileContents = file_get_contents($rootPath . $templateXmlFile);
			$fileContents = preg_replace('#<version>[^<]*</version>#', '<version>' . $version['release'] . '</version>', $fileContents);
			$fileContents = preg_replace('#<creationDate>[^<]*</creationDate>#', '<creationDate>' . $version['credate'] . '</creationDate>', $fileContents);
			file_put_contents($rootPath . $templateXmlFile, $fileContents);

			echo 'Template update:' . PHP_TAB . str_replace(array("/", "administrator", "templates"), "", $dirname) . PHP_EOL;
		}
	}
}

echo PHP_EOL;

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
	$fileContents = preg_replace('#<arg value="Joomla! CMS [^ ]* API" />#', '<arg value="Joomla! CMS ' . $version['main'] . ' API" />', $fileContents);
	file_put_contents($rootPath . $antJobFile, $fileContents);
}

echo 'Version bump complete!' . PHP_EOL;
