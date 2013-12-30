<?php
// We are a valid entry point.
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

// Get the framework.
require_once JPATH_LIBRARIES . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Configure error reporting to maximum for CLI output.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load the admin en-GB.ini language file to get the JHELP language keys
JFactory::getLanguage()->load('joomla', JPATH_ADMINISTRATOR, null, false, false);

/**
 * Utility CLI to retrieve data via JMediawiki
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
		// Set up options for JMediawiki
		$options = new JRegistry;
		$options->set('api.url', 'http://docs.joomla.org');

		$mediawiki = new JMediawiki($options);

		// Get the category members (local hack)
		$categoryMembers = $mediawiki->categories->getCategoryMembers();

		$members = array();

		foreach ($categoryMembers->query->categorymembers as $catmembers)
		{
			foreach ($catmembers as $member)
			{
				$members[] = (string) $member['title'];
			}
		}

		// Get the language object
		$language = JFactory::getLanguage();

		// Get the strings via Reflection
		$refl = new ReflectionClass($language);
		$property = $refl->getProperty('strings');
		$property->setAccessible(true);
		$strings = $property->getValue($language);

		// Now we start fancy processing so we can get the language key for the titles
		$cleanMembers = array();

		foreach ($members as $member)
		{
			$member = str_replace(array('Help32:', ' '), array('', '_'), $member);
			$cleanMembers[] = $member;
		}

		$matchedMembers = array();

		// Loop through the cleaned up title array and the language strings array to match things up
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
		asort($matchedMembers);

		// Now we strip off the JHELP_ prefix from the strings to get usable strings for both COM_ADMIN and JHELP
		$toc = array();

		foreach ($matchedMembers as $member)
		{
			$toc[] = str_replace('JHELP_', '', $member);
		}

		// JSON encode the file and write it to JPATH_ADMINISTRATOR/help/en-GB/toc.json
		file_put_contents(JPATH_ADMINISTRATOR . '/help/en-GB/toc.json', json_encode($toc));

		$this->out('Help Screen TOC written', true);
	}
}

// Instantiate the application and execute it
JApplicationCli::getInstance('MediawikiCli')->execute();
