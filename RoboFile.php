<?php
/**
 * This is joomla project's console command file for Robo.li task runner.
 *
 * Download robo.phar from http://robo.li/robo.phar and type in the root of the repo: $ php robo.phar
 * Or do: $ composer update, and afterwards you will be able to execute robo like $ php libraries/vendor/bin/robo
 *
 * @see http://robo.li/
 */
require_once __DIR__ . '/tests/codeception/vendor/autoload.php';

if (!defined('JPATH_BASE'))
{
	define('JPATH_BASE', __DIR__);
}

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
	 * @var array
	 */
	private $configuration = array();

	/**
	 * Path to the local CMS test folder
	 *
	 * @var string
	 */
	protected $cmsPath = null;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->configuration = $this->getConfiguration();

		$this->cmsPath = $this->getTestingPath();

		// Set default timezone (so no warnings are generated if it is not set)
		date_default_timezone_set('UTC');
	}

	/**
	 * Get (optional) configuration from an external file
	 *
	 * @return \stdClass|null
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
	 * @return string
	 */
	private function getTestingPath()
	{
		if (empty($this->configuration->cmsPath))
		{
			return $this->testsPath . 'joomla-cms3';
		}

		if (!file_exists(dirname($this->configuration->cmsPath)))
		{
			$this->say("Cms path written in local configuration does not exists or is not readable");

			return $this->testsPath . 'joomla-cms3';
		}

		return $this->configuration->cmsPath;
	}

	/**
	 * Build the Joomla CMS
	 *
	 * @return  bool
	 */
	public function build()
	{
		return true;
	}

	/**
	 * Creates a testing Joomla site for running the tests (use it before run:test)
	 *
	 * @param   bool  $use_htaccess  (1/0) Rename and enable embedded Joomla .htaccess file
	 */
	public function createTestingSite($use_htaccess = false)
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
				$this->say('Sorry, you will have to delete ' . $this->cmsPath . ' manually. ');
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
		if ($use_htaccess == true)
		{
			$this->say("Renaming htaccess.txt to .htaccess");
			$this->_copy('./htaccess.txt', $this->cmsPath . '/.htaccess');
			$this->_exec('sed -e "s,# RewriteBase /,RewriteBase /tests/codeception/joomla-cms3/,g" -in-place tests/codeception/joomla-cms3/.htaccess');
		}

		$this->taskExec('curl -I http://localhost/tests/joomla-cms3/')->printed(true)->run();
	}

	/**
	 * Copy the joomla installation excluding folders
	 *
	 * @param   string  $dst       Target folder
	 * @param   array   $exclude   Exclude list of folders
	 *
	 * @throws  Exception
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
	 * @return void
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
	 * @return void
	 */
	public function runSelenium()
	{
		if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')
		{
			$this->_exec($this->testsPath . "vendor/bin/selenium-server-standalone >> selenium.log 2>&1 &");
		}
		else
		{
			$this->_exec("START java.exe -jar .\\tests\\codeception\\vendor\\joomla-projects\\selenium-server-standalone\\bin\\selenium-server-standalone.jar");
		}

		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
		{
			sleep(10);
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
	 * @param   array $opts Array of configuration options:
	 *          - 'use-htaccess': renames and enable embedded Joomla .htaccess file
	 *          - 'env': set a specific environment to get configuration from
	 * @return mixed
	 */
	public function runTests($opts = ['use-htaccess' => false, 'env' => 'desktop'])
	{
		$this->say("Running tests");

		$this->createTestingSite($opts['use-htaccess']);

		$this->getComposer();
		$this->taskComposerInstall('tests/composer.phar')->run();

		$this->runSelenium();

		// Make sure to run the build command to generate AcceptanceTester
		$this->_exec('php ' . $this->testsPath . 'vendor/bin/codecept build');

		$pathToCodeception = $this->testsPath . 'vendor/bin/codecept';

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->arg('--env ' . $opts['env'])
			->arg($this->testsPath . 'acceptance/install/')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->arg('--env ' . $opts['env'])
			->arg($this->testsPath . 'acceptance/content.feature')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->arg('--env ' . $opts['env'])
			->arg($this->testsPath . 'acceptance/users.feature')
			->run()
			->stopOnFail();

        $this->taskCodecept($pathToCodeception)
            ->arg('--steps')
            ->arg('--debug')
            ->arg('--fail-fast')
            ->arg('--env ' . $opts['env'])
            ->arg($this->testsPath . 'acceptance/users_frontend.feature')
            ->run()
            ->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->arg('--env ' . $opts['env'])
			->arg($this->testsPath . 'acceptance/category.feature')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->arg('--env ' . $opts['env'])
			->arg($this->testsPath . 'acceptance/administrator/')
			->run()
			->stopOnFail();

		$this->taskCodecept($pathToCodeception)
			->arg('--steps')
			->arg('--debug')
			->arg('--fail-fast')
			->arg('--env ' . $opts['env'])
			->arg($this->testsPath . 'acceptance/frontend/')
			->run()
			->stopOnFail();

		/*
		// Uncomment this lines if you need to debug selenium errors
		$seleniumErrors = file_get_contents('selenium.log');

		if ($seleniumErrors)
		{
			$this->say('Printing Selenium Log files');
			$this->say('------ selenium.log (start) ---------');
			$this->say($seleniumErrors);
			$this->say('------ selenium.log (end) -----------');
		}
		*/
	}


	/**
	 * Executes a specific Selenium System Tests in your machine
	 *
	 * @param   string  $pathToTestFile  Optional name of the test to be run
	 * @param   string  $suite           Optional name of the suite containing the tests, Acceptance by default.
	 *
	 * @return  mixed
	 */
	public function runTest($pathToTestFile = null, $suite = 'acceptance')
	{
		$this->runSelenium();

		// Make sure to run the build command to generate AcceptanceTester
		$this->_exec('php tests/codeception/vendor/bin/codecept build');

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
			$iterator->rewind();
			$i = 1;

			while ($iterator->valid())
			{
				if (strripos($iterator->getSubPathName(), 'cept.php')
					|| strripos($iterator->getSubPathName(), 'cest.php')
					|| strripos($iterator->getSubPathName(), '.feature'))

				{
					$this->say('[' . $i . '] ' . $iterator->getSubPathName());
					$tests[$i] = $iterator->getSubPathName();
					$i++;
				}

				$iterator->next();
			}

			$this->say('');
			$testNumber	= $this->ask('Type the number of the test  in the list that you want to run...');
			$test = $tests[$testNumber];
		}

		$pathToTestFile = $this->testsPath . $suite . '/' . $test;

		//loading the class to display the methods in the class

		//logic to fetch the class name from the file name
		$fileName = explode("/", $test);

		// If the selected file is cest only then we will give the option to execute individual methods, we don't need this in cept or feature files
		$i = 1;

		if (isset($fileName[1]) && strripos($fileName[1], 'cest'))
		{
			require $this->testsPath . $suite . '/' . $test;

			$className = explode(".", $fileName[1]);
			$class_methods = get_class_methods($className[0]);
			$this->say('[' . $i . '] ' . 'All');
			$methods[$i] = 'All';
			$i++;

			foreach ($class_methods as $method_name)
			{
				$reflect = new ReflectionMethod($className[0], $method_name);
				if(!$reflect->isConstructor())
				{
					if ($reflect->isPublic())
					{
						$this->say('[' . $i . '] ' . $method_name);
						$methods[$i] = $method_name;
						$i++;
					}
				}
			}
			$this->say('');
			$methodNumber = $this->ask('Please choose the method in the test that you would want to run...');
			$method = $methods[$methodNumber];
		}

		if(isset($method) && $method != 'All')
		{
			$pathToTestFile = $pathToTestFile . ':' . $method;
		}

		$this->taskCodecept($this->testsPath . 'vendor/bin/codecept')
			->test($pathToTestFile)
			->arg('--steps')
			->arg('--debug')
			->run()
			->stopOnFail();
	}
}
