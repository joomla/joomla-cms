<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

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

// Load Library language
$lang = JFactory::getLanguage();

// Try the files_joomla file in the current language (without allowing the loading of the file in the default language)
$lang->load('files_joomla.sys', JPATH_SITE, null, false, false)
// Fallback to the files_joomla file in the default language
|| $lang->load('files_joomla.sys', JPATH_SITE, null, true);

/**
 * A command line cron job to attempt to remove files that should have been deleted at update.
 *
 * @package  Joomla.Cli
 * @since    3.0
 */
class CreatecheckfilesCli extends JApplicationCli
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
		echo "Creating checksums... \n";
		$files = $this->readFolder('');
		echo "Writing checksum file... \n";
		file_put_contents(JPATH_ADMINISTRATOR.'/checksums/joomla.md5', implode("\n", $files));
		echo "Done!";
	}
	
	function readFolder($dir)
	{
		$result = array();
		
		if(!$dh = @opendir(JPATH_BASE.'/'.$dir))
		{
			return;
		}echo "Reading $dir \n";
		while (false !== ($obj = readdir($dh)))
		{
			if ($obj == '.' || $obj == '..' || substr($obj, 0, 1) == '.')
			{
				continue;
			}
			
			$file = JPATH_BASE.'/'.$dir.'/'.$obj;
			
			if (is_dir($file))
			{
				$result = array_merge($result, $this->readFolder($dir.'/'.$obj));
			} elseif (is_file($file))
			{
				$result[] = md5_file($file).' '.$dir.'/'.$obj;
			}

        }

		return $result;
	} 
}

// Instantiate the application object, passing the class name to JCli::getInstance
// and use chaining to execute the application.
JApplicationCli::getInstance('CreatecheckfilesCli')->execute();
