<?php
/**
 * @package     Joomla.Site
 * @subpackage  RoboFile
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is joomla project's console command file for Robo.li task runner.
 *
 * Download robo.phar from http://robo.li/robo.phar and type in the root of the repo: $ php robo.phar
 * Or do: $ composer update, and afterwards you will be able to execute robo like $ php libraries/vendor/bin/robo
 *
 * @see         http://robo.li/
 */
require_once __DIR__ . '/tests/codeception/vendor/autoload.php';

if (!defined('JPATH_BASE'))
{
	define('JPATH_BASE', __DIR__);
}

/**
 * Modern php task runner for Joomla! Browser Automated Tests execution
 *
 * @package  RoboFile
 *
 * @since    __DEPLOY_VERSION__
 */
class RoboFile extends \Robo\Tasks
{
	// Load tasks from composer, see composer.json
	use \joomla_projects\robo\loadTasks;
	use \Joomla\Jorobo\Tasks\loadTasks;

	/**
	 * Path to the codeception tests folder
	 *
	 * @var   string
	 */
	private $testsPath = 'tests/codeception/';

	/**
	 * Local configuration parameters
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $configuration = array();

	/**
	 * @var array | null
	 * @since  __DEPLOY_VERSION__
	 */
	private $suiteConfig;

	/**
	 * Path to the local CMS test folder
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $cmsPath = null;

	/**
	 * RoboFile constructor.
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 */
	public function __construct()
	{
		$this->configuration = $this->getConfiguration();
		$this->cmsPath       = $this->getTestingPath();

		// Set default timezone (so no warnings are generated if it is not set)
		date_default_timezone_set('UTC');
	}

	/**
	 * Get (optional) configuration from an external file
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  \stdClass|null
	 */
	public function getConfiguration()
	{
		$configurationFile = __DIR__ . '/RoboFile.ini';

		if (!file_exists($configurationFile))
		{
			$this->say("No local configuration file");

			return null;
		}

		$configuration = parse_ini_file($configurationFile);

		if ($configuration === false)
		{
			$this->say('Local configuration file is empty or wrong (check is it in correct .ini format');

			return null;
		}

		return json_decode(json_encode($configuration));
	}

	/**
	 * Get the correct CMS root path
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  string
	 */
	private function getTestingPath()
	{
		if (empty($this->configuration->cmsPath))
		{
			return $this->testsPath . 'joomla-cms3';
		}

		if (!file_exists(dirname($this->configuration->cmsPath)))
		{
			$this->say("CMS path written in local configuration does not exists or is not readable");

			return $this->testsPath . 'joomla-cms3';
		}

		return $this->configuration->cmsPath;
	}

	/**
	 * Build the Joomla CMS
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  bool  This is allways true
	 */
	public function build()
	{
		return true;
	}

	/**
	 * Creates a testing Joomla site for running the tests (use it before run:test)
	 *
	 * @param   bool $useHtaccess (1/0) Rename and enable embedded Joomla .htaccess file
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function createTestingSite($useHtaccess = false)
	{
		// Clean old testing site
		if (is_dir($this->cmsPath))
		{
			try
			{
				$this->taskDeleteDir($this->cmsPath)->run();
			}
			catch (Exception $e)
			{
				// Sorry, we tried :(
				$this->say('Sorry, you will have to delete ' . $this->cmsPath . ' manually.');

				exit(1);
			}
		}

		$this->build();

		$exclude = ['tests', 'tests-phpunit', '.run', '.github', '.git'];

		$this->copyJoomla($this->cmsPath, $exclude);

		// Optionally change owner to fix permissions issues
		if (!empty($this->configuration->localUser))
		{
			$this->_exec('chown -R ' . $this->configuration->localUser . ' ' . $this->cmsPath);
		}

		// Optionally uses Joomla default htaccess file. Used by TravisCI
		if ($useHtaccess == true)
		{
			$this->say("Renaming htaccess.txt to .htaccess");
			$this->_copy('./htaccess.txt', $this->cmsPath . '/.htaccess');
			$this->_exec('sed -e "s,# RewriteBase /,RewriteBase /tests/codeception/joomla-cms3/,g" -in-place tests/codeception/joomla-cms3/.htaccess');
		}
	}

	/**
	 * Copy the joomla installation excluding folders
	 *
	 * @param   string $dst     Target folder
	 * @param   array  $exclude Exclude list of folders
	 *
	 * @throws  Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	protected function copyJoomla($dst, $exclude = array())
	{
		$dir = @opendir(".");

		if (false === $dir)
		{
			throw new Exception($this, "Cannot open source directory");
		}

		if (!is_dir($dst))
		{
			mkdir($dst, 0755, true);
		}

		while (false !== ($file = readdir($dir)))
		{
			if (in_array($file, $exclude))
			{
				continue;
			}

			if (($file !== '.') && ($file !== '..'))
			{
				$srcFile  = "." . '/' . $file;
				$destFile = $dst . '/' . $file;

				if (is_dir($srcFile))
				{
					$this->_copyDir($srcFile, $destFile);
				}
				else
				{
					copy($srcFile, $destFile);
				}
			}
		}

		closedir($dir);
	}

	/**
	 * Downloads Composer
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	private function getComposer()
	{
		// Make sure we have Composer
		if (!file_exists($this->testsPath . 'composer.phar'))
		{
			$this->_exec('curl -o ' . $this->testsPath . 'composer.phar  --retry 3 --retry-delay 5 -sS https://getcomposer.org/installer | php');
		}
	}

	/**
	 * Runs Selenium Standalone Server.
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  void
	 */
	public function runSelenium()
	{
		if (!$this->isWindows())
		{
			$this->_exec($this->testsPath . "vendor/bin/selenium-server-standalone " . $this->getWebDriver() . ' >> selenium.log 2>&1 &');
		}
		else
		{
			$this->_exec("START java.exe -jar " . $this->getWebDriver() . ' tests\codeception\vendor\joomla-projects\selenium-server-standalone\bin\selenium-server-standalone.jar ');
		}

		if ($this->isWindows())
		{
			sleep(3);
		}
		else
		{
			$this->taskWaitForSeleniumStandaloneServer()
				->run()
				->stopOnFail();
		}
	}

	/**
	 * Executes all the Selenium System Tests in a suite on your machine
	 *
	 * @param   array $opts   Array of configuration options:
	 *                        - 'use-htaccess': renames and enable embedded Joomla .htaccess file
	 *                        - 'env': set a specific environment to get configuration from
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  mixed
	 */
	public function runTests($opts = ['use-htaccess' => false, 'env' => 'desktop'])
	{
		$this->say("Running tests");

		$this->createTestingSite($opts['use-htaccess']);
		$this->createDatabase();

		$this->getComposer();
		$this->taskComposerInstall($this->testsPath . 'composer.phar')->run();

		$this->runSelenium();

		// Make sure to run the build command to generate AcceptanceTester
		if ($this->isWindows())
		{
			$this->_exec('php ' . $this->getWindowsPath($this->testsPath . 'vendor/bin/codecept') . ' build');
			$pathToCodeception = $this->getWindowsPath($this->testsPath . 'vendor/bin/codecept');
		}
		else
		{
			$this->_exec('php ' . $this->testsPath . 'vendor/bin/codecept build');

			$pathToCodeception = $this->testsPath . 'vendor/bin/codecept';
		}

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/install/')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/content.feature')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/users.feature')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/users_frontend.feature')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/banner.feature')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/extensions.feature')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/category.feature')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/administrator/')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->env($opts['env'])
			->arg($this->testsPath . 'acceptance/frontend/')
			->run()
			->stopOnFail();
	}

	/**
	 * Executes a specific Selenium System Tests in your machine
	 *
	 * @param   string $pathToTestFile Optional name of the test to be run
	 * @param   string $suite          Optional name of the suite containing the tests, Acceptance by default.
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @return  mixed
	 */
	public function runTest($pathToTestFile = null, $suite = 'acceptance')
	{
		$this->runSelenium();

		// Make sure to run the build command to generate AcceptanceTester

		$path = 'tests/codeception/vendor/bin/codecept';
		$this->_exec('php ' . $this->isWindows() ? $this->getWindowsPath($path) : $path . ' build');

		if (!$pathToTestFile)
		{
			$this->say('Available tests in the system:');

			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator(
					$this->testsPath . $suite,
					RecursiveDirectoryIterator::SKIP_DOTS
				),
				RecursiveIteratorIterator::SELF_FIRST
			);

			$tests = array();
			$i     = 1;

			$iterator->rewind();

			while ($iterator->valid())
			{
				if (strripos($iterator->getSubPathName(), 'cept.php')
					|| strripos($iterator->getSubPathName(), 'cest.php')
					|| strripos($iterator->getSubPathName(), '.feature')
				)
				{
					$this->say('[' . $i . '] ' . $iterator->getSubPathName());

					$tests[$i] = $iterator->getSubPathName();
					$i++;
				}

				$iterator->next();
			}

			$this->say('');
			$testNumber = $this->ask('Type the number of the test in the list that you want to run...');
			$test       = $tests[$testNumber];
		}

		$pathToTestFile = $this->testsPath . $suite . '/' . $test;

		// Loading the class to display the methods in the class

		// Logic to fetch the class name from the file name
		$fileName = explode("/", $test);

		// If the selected file is cest only then we will give the option to execute individual methods, we don't need this in cept or feature files
		$i = 1;

		if (isset($fileName[1]) && strripos($fileName[1], 'cest'))
		{
			require $this->testsPath . $suite . '/' . $test;

			$className     = explode(".", $fileName[1]);
			$class_methods = get_class_methods($className[0]);

			$this->say('[' . $i . '] ' . 'All');

			$methods[$i] = 'All';
			$i++;

			foreach ($class_methods as $method_name)
			{
				$reflect = new ReflectionMethod($className[0], $method_name);

				if (!$reflect->isConstructor() && $reflect->isPublic())
				{
					$this->say('[' . $i . '] ' . $method_name);

					$methods[$i] = $method_name;

					$i++;
				}
			}

			$this->say('');
			$methodNumber = $this->ask('Please choose the method in the test that you would want to run...');
			$method       = $methods[$methodNumber];
		}

		if (isset($method) && $method != 'All')
		{
			$pathToTestFile = $pathToTestFile . ':' . $method;
		}

		$testPathCodecept = $this->testsPath . 'vendor/bin/codecept';

		$this->taskCodecept($this->isWindows() ? $this->getWindowsPath($testPathCodecept) : $testPathCodecept)
			->test($pathToTestFile)
			->arg('--steps')
			->arg('--debug')
			->run()
			->stopOnFail();
	}

	/**
	 * Check if local OS is Windows
	 *
	 * @return bool
	 */
	private function isWindows()
	{
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/**
	 * Return the correct path for Windows
	 *
	 * @param   string $path - The linux path
	 *
	 * @return string
	 */
	private function getWindowsPath($path)
	{
		return str_replace('/', DIRECTORY_SEPARATOR, $path);
	}

	/**
	 * Detect the correct driver for selenium
	 *
	 * @return  string the webdriver string to use with selenium
	 *
	 * @since version
	 */
	public function getWebdriver()
	{
		$suiteConfig        = $this->getSuiteConfig();
		$codeceptMainConfig = \Codeception\Configuration::config();
		$browser            = $suiteConfig['modules']['config']['JoomlaBrowser']['browser'];

		if ($browser == 'chrome')
		{
			$driver['type'] = 'webdriver.chrome.driver';
		}
		elseif ($browser == 'firefox')
		{
			$driver['type'] = 'webdriver.gecko.driver';
		}
		elseif ($browser == 'MicrosoftEdge')
		{
			$driver['type'] = 'webdriver.edge.driver';

			// Check if we are using Windows Insider builds
			if ($suiteConfig['modules']['config']['AcceptanceHelper']['MicrosoftEdgeInsiders'])
			{
				$browser = 'MicrosoftEdgeInsiders';
			}
		}
		elseif ($browser == 'internet explorer')
		{
			$driver['type'] = 'webdriver.ie.driver';
		}

		// Check if we have a path for this browser and OS in the codeception settings
		if (isset($codeceptMainConfig['webdrivers'][$browser][$this->getOs()]))
		{
			$driverPath = $codeceptMainConfig['webdrivers'][$browser][$this->getOs()];
		}
		else
		{
			$this->yell('No driver for your browser. Check your browser in acceptance.suite.yml and the webDrivers in codeception.yml');

			// We can't do anything without a driver, exit
			exit(1);
		}

		$driver['path'] = $driverPath;

		return '-D' . implode('=', $driver);
	}

	/**
	 * Return the os name
	 *
	 * @return string
	 *
	 * @since version
	 */
	private function getOs()
	{
		$os = php_uname('s');

		if (strpos(strtolower($os), 'windows') !== false)
		{
			$os = 'windows';
		}
		// Who have thought that Mac is actually Darwin???
		elseif (strpos(strtolower($os), 'darwin') !== false)
		{
			$os = 'mac';
		}
		else
		{
			$os = 'linux';
		}

		return $os;
	}

	/**
	 * Get the suite configuration
	 *
	 * @param string $suite
	 *
	 * @return array
	 */
	private function getSuiteConfig($suite = 'acceptance')
	{
		if (!$this->suiteConfig)
		{
			$this->suiteConfig = Symfony\Component\Yaml\Yaml::parse(file_get_contents("tests/codeception/{$suite}.suite.yml"));
		}

		return $this->suiteConfig;
	}

	private function createDatabase()
	{
		$suiteConfig = $this->getSuiteConfig();

		$host   = $suiteConfig['modules']['config']['JoomlaBrowser']['database host'];
		$user   = $suiteConfig['modules']['config']['JoomlaBrowser']['database user'];
		$pass   = $suiteConfig['modules']['config']['JoomlaBrowser']['database password'];
		$dbName = $suiteConfig['modules']['config']['JoomlaBrowser']['database name'];

		// Create connection
		$connection = new mysqli($host, $user, $pass);
		// Check connection
		if ($connection->connect_error)
		{
			$this->yell("Connection failed: " . $connection->connect_error);
		}

		// Create database
		$sql = "CREATE DATABASE IF NOT EXISTS {$dbName}";
		if ($connection->query($sql) === true)
		{
			$this->say("Database {$dbName} created successfully");
		}
		else
		{
			$this->yell("Error creating database: " . $connection->error);
		}
	}
}
