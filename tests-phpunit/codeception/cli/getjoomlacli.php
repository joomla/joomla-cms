<?php
/**
 * @package     Joomla
 * @subpackage  Tests
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GetJoomlaCli;

// Maximise error reporting.
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 1);

require_once dirname(__DIR__) . '/vendor/autoload.php';


use Joomla\Application\AbstractCliApplication;
use Joomla\Filesystem\Folder;

/**
 * Class GetJoomlaCli
 *
 * Simple Command Line Application to get the latest Joomla for running the tests
 */
class GetJoomlaCli extends AbstractCliApplication
{
	/**
	 * The application version.
	 *
	 * @var    string
	 * @since  1.0
	 */
	const VERSION = '1.0';

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function doExecute()
	{
		if ($this->input->get('h') || $this->input->get('help'))
		{
			$this->out();
			$this->out('GetJoomlaCLI ' . self::VERSION);
			$this->out('--------------------------------------------------------');
			$this->out('GetJoomlaCLI is a Joomla Framework simple Command Line Apllication.');
			$this->out('With GetJoomlaCLI you can easily download the latest Joomla CMS into a folder.');
			$this->out('called "testingsite".');
			$this->out();
			$this->out('           -h | --help    Prints this usage information.');
			$this->out('           -folder        the folder name where to create the joomla site');
			$this->out('                          "testingsite" will be used if no name is provided');
			$this->out('EXAMPLE USAGE:');
			$this->out('           php -f getjoomlacli.php -h');
			$this->out('           php -f getjoomlacli.php --folder=testingsite');
			$this->out();
		}
		else
		{
			$folder = JPATH_ROOT . '/' . $this->input->get('folder', 'testingsite');

			if (is_dir($folder))
			{
				$this->out('Removing old files in the folder...');
				Folder::delete($folder);
			}

			Folder::create($folder);

			$this->out('Downloading Joomla...');
			$repository = 'https://github.com/joomla/joomla-cms.git';
			$branch = 'staging';

			$command = "git clone -b ${branch} --single-branch --depth 1 ${repository} ${folder}";
			$this->out($command);

			$output = array();

			exec($command, $output, $returnValue);

			if ($returnValue)
			{
				$this->out('Sadly we were not able to download Joomla.');
			}
			else
			{
				$this->out('Joomla Downloaded and ready for executing the tests.');
			}
		}
	}
}

define('JPATH_ROOT', realpath(dirname(__DIR__)));

$app = new GetJoomlaCli;
$app->execute();

