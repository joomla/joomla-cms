<?php
/**
 * @package    Joomla.Build
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Set flag that this is a parent file.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

// Get the Platform without legacy libraries.
require_once JPATH_LIBRARIES . '/import.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load the admin en-GB.ini language file to get the JHELP language keys
JFactory::getLanguage()->load('joomla', JPATH_ADMINISTRATOR, null, false, false);

/**
 * Utility CLI to retrieve the list of help screens from the docs wiki and create an index for the admin help view.
 *
 * @since  3.0
 */
class MediawikiCli extends JApplicationCli
{
	/**
	 * Entry point for CLI script
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function doExecute()
	{
		// Get the version data for the script
		$version     = new JVersion;
		$helpVersion = str_replace('.', '', $version::RELEASE);
		$namespace   = 'Help' . $helpVersion . ':';

		// Set up options for JMediawiki
		$options = new JRegistry;
		$options->set('api.url', 'https://docs.joomla.org');

		$mediawiki = new JMediawiki($options);

		// Get the category members (local hack)
		$this->out('Fetching data from docs wiki', true);
		$categoryMembers = $mediawiki->categories->getCategoryMembers('Category:Help_screen_' . $version::RELEASE, null, 'max');

		$members = array();

		// Loop through the result objects to get every document
		foreach ($categoryMembers->query->categorymembers as $catmembers)
		{
			foreach ($catmembers as $member)
			{
				$members[] = (string) $member['title'];
			}
		}

		// Get the language object
		$language = JFactory::getLanguage();

		// Get the language strings via Reflection as the property is protected
		$refl = new ReflectionClass($language);
		$property = $refl->getProperty('strings');
		$property->setAccessible(true);
		$strings = $property->getValue($language);

		/*
		 * Now we start fancy processing so we can get the language key for the titles
		 */

		$cleanMembers = array();

		// Strip the namespace prefix off the titles and replace spaces with underscores
		foreach ($members as $member)
		{
			$cleanMembers[] = str_replace(array($namespace, ' '), array('', '_'), $member);
		}

		// Make sure we only have an array of unique values before continuing
		$cleanMembers = array_unique($cleanMembers);

		/*
		 * Loop through the cleaned up title array and the language strings array to match things up
		 */

		$matchedMembers = array();

		foreach ($cleanMembers as $member)
		{
			foreach ($strings as $k => $v)
			{
				if ($member === $v)
				{
					$matchedMembers[] = $k;

					continue;
				}
			}
		}

		// Alpha sort the array
		asort($matchedMembers);

		// Now we strip off the JHELP_ prefix from the strings to get usable strings for both COM_ADMIN and JHELP
		$stripped = array();

		foreach ($matchedMembers as $member)
		{
			$stripped[] = str_replace('JHELP_', '', $member);
		}

		/*
		 * Check to make sure a COM_ADMIN_HELP string exists, don't include in the TOC if not
		 */

		// Load the admin com_admin language file
		$language->load('com_admin', JPATH_ADMINISTRATOR);

		$toc = array();

		foreach ($stripped as $string)
		{
			// Validate the key exists
			$this->out('Validating key COM_ADMIN_HELP_' . $string, true);

			if ($language->hasKey('COM_ADMIN_HELP_' . $string))
			{
				$this->out('Adding ' . $string, true);

				$toc[$string] = $string;
			}
			// We check if the string for words in singular/plural form and check again
			else
			{
				$this->out('Inflecting ' . $string, true);

				if (strpos($string, '_CATEGORIES') !== false)
				{
					$inflected = str_replace('_CATEGORIES', '_CATEGORY', $string);
				}
				elseif (strpos($string, '_USERS') !== false)
				{
					$inflected = str_replace('_USERS', '_USER', $string);
				}
				elseif (strpos($string, '_CATEGORY') !== false)
				{
					$inflected = str_replace('_CATEGORY', '_CATEGORIES', $string);
				}
				elseif (strpos($string, '_USER') !== false)
				{
					$inflected = str_replace('_USER', '_USERS', $string);
				}
				else
				{
					$inflected = '';
				}

				// Now try to validate the key
				if ($inflected !== '')
				{
					$this->out('Validating key COM_ADMIN_HELP_' . $inflected, true);

					if ($language->hasKey('COM_ADMIN_HELP_' . $inflected))
					{
						$this->out('Adding ' . $inflected, true);

						$toc[$string] = $inflected;
					}
				}
			}
		}

		$this->out('Number of strings: ' . count($toc), true);

		// JSON encode the file and write it to JPATH_ADMINISTRATOR/help/en-GB/toc.json
		file_put_contents(JPATH_ADMINISTRATOR . '/help/en-GB/toc.json', json_encode($toc));

		$this->out('Help Screen TOC written', true);
	}
}

// Instantiate the application and execute it
JApplicationCli::getInstance('MediawikiCli')->execute();
