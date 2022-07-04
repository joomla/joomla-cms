<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Console;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Installation\Model\SetupModel;
use Joomla\CMS\Installation\Application\CliInstallationApplication;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Registry\Registry;
use phpDocumentor\GraphViz\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for installing Joomla
 *
 * @since  __DEPLOY_VERSION__
 */

class InstallCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'install';

	/**
	 * @var  SymfonyStyle
	 * @since  __DEPLOY_VERSION__
	 */
	protected $ioStyle;

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$this->configureIO($input, $output);

		$this->ioStyle->title('Install Joomla');

		$cfg = $this->getCLIOptions();

		if ($cfg instanceof \Exception)
		{
			return Command::FAILURE;
		}



		var_dump($cfg);


		$this->ioStyle->success('Joomla has been successfully installed');

		return Command::SUCCESS;
	}

	protected function getCLIOptions()
	{
		/* @var CliInstallationApplication $app */
		$app = $this->getApplication();

		/* @var SetupModel $setupmodel */
		$setupmodel = $app->getMVCFactory()->createModel('Setup', 'Installation');
		$form = $setupmodel->getForm('setup');

		$cfg['site_name'] = $this->getStringFromOption('site_name', 'Please provide the sites name', $form->getField('site_name'));
		$cfg['admin_user'] = $this->getStringFromOption('admin_user', 'Please provide a name for the main administrator user', $form->getField('admin_user'));
		$cfg['admin_username'] = $this->getStringFromOption('admin_username', 'Please provide a username for the main administrator user');
		$cfg['admin_email'] = $this->getStringFromOption('admin_email', 'Please provide an e-mail adress for the main administrator user');
		$cfg['admin_password'] = $this->getStringFromOption('admin_password', 'Please provide a password for the main administrator user');
		$cfg['db_type'] = $this->getStringFromOption('db_type', 'Please select the type of database you want to use. [mysql, mysqli, pgsql, postgresql]');
		$cfg['db_host'] = $this->getStringFromOption('db_host', 'Please provide the host of the database');
		$cfg['db_user'] = $this->getStringFromOption('db_user', 'Please provide the username for the database');
		$cfg['db_pass'] = $this->getStringFromOption('db_pass', 'Please provide the password for the database user');
		$cfg['db_name'] = $this->getStringFromOption('db_name', 'Please provide the name of the database to be used');
		$cfg['db_prefix'] = $this->getStringFromOption('db_prefix', 'Please provide the prefix for the database tables');
		$cfg['db_encryption'] = $this->getStringFromOption('db_encryption', 'Should the connection to the database be encrypted? Values: 0=None, 1=One way, 2=Two way');

		if ($cfg['db_encryption'] == 2)
		{
			$cfg['db_sslkey'] = $this->getStringFromOption('db_sslkey', 'Please provide the SSL key for the database connection');
			$cfg['db_sslcert'] = $this->getStringFromOption('db_sslcert', 'Please provide the path to SSL certificate for the database connection');
		}

		if ($cfg['db_encryption'])
		{
			$cfg['db_sslverifyservercert'] = $this->getStringFromOption('db_sslverifyservercert', 'Should Joomla verify the SSL certificates for database conection. Values: 0=No, 1=Yes');
			$cfg['db_sslca'] = $this->getStringFromOption('db_sslca', 'Path to CA file to verify encryption against');
			$cfg['db_sslcipher'] = $this->getStringFromOption('db_sslcipher', 'Supported Cipher Suite (optional)');
		}

		return $cfg;
	}

	/**
	 * Configure the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function configure(): void
	{
		$help = "<info>%command.name%</info> will install Joomla
		\nUsage: <info>php %command.full_name%</info>";

		$this->setDescription('Install the Joomla CMS');
		$this->addOption('site_name', null, InputOption::VALUE_REQUIRED, 'Name of the website');
		$this->addOption('admin_user', null, InputOption::VALUE_OPTIONAL, 'Name of the administrator account');
		$this->addOption('admin_username', null, InputOption::VALUE_OPTIONAL, 'Username of the administrator account');
		$this->addOption('admin_email', null, InputOption::VALUE_OPTIONAL, 'Email of the administrator account');
		$this->addOption('admin_password', null, InputOption::VALUE_OPTIONAL, 'Password of the administrator account');
		$this->addOption('db_type', null, InputOption::VALUE_OPTIONAL, 'Database type. Supported: mysql, mysqli, pgsql, postgresql', 'mysqli');
		$this->addOption('db_host', null, InputOption::VALUE_OPTIONAL, 'Database host');
		$this->addOption('db_user', null, InputOption::VALUE_OPTIONAL, 'Database username');
		$this->addOption('db_pass', null, InputOption::VALUE_OPTIONAL, 'Database password');
		$this->addOption('db_name', null, InputOption::VALUE_OPTIONAL, 'Database name');
		$this->addOption('db_prefix', null, InputOption::VALUE_OPTIONAL, 'Prefix for the database tables');
		$this->addOption('db_encryption', null, InputOption::VALUE_OPTIONAL, 'Encryption for the connection the database. Values: 0=None, 1=One way, 2=Two way', 0);
		$this->addOption('db_sslkey', null, InputOption::VALUE_OPTIONAL, 'SSL key for the database connection. Requires encryption to be set to 2');
		$this->addOption('db_sslcert', null, InputOption::VALUE_OPTIONAL, 'Path to SSL certificate for the database connection. Requires encryption to be set to 2');
		$this->addOption('db_sslverifyservercert', null, InputOption::VALUE_OPTIONAL, 'Verify SSL certificates for database conection. Values: 0=No, 1=Yes. Requires encryption to be set to 1 or 2');
		$this->addOption('db_sslca', null, InputOption::VALUE_OPTIONAL, 'Path to CA file to verify encryption against.');
		$this->addOption('db_sslcipher', null, InputOption::VALUE_OPTIONAL, 'Supported Cipher Suite (optional)');

		$this->setHelp($help);
	}

	/**
	 * Method to get a value from option
	 *
	 * @param   string     $option    set the option name
	 * @param   string     $question  set the question if user enters no value to option
	 * @param   FormField  $field     Field to validate against
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getStringFromOption($option, $question, $field): string
	{
		$answer = (string) $this->getApplication()->getConsoleInput()->getOption($option);

		if ($answer)
		{
			$valid = $field->validate($answer);

			if ($valid instanceof \Exception)
			{
				throw new Exception('Value for ' . $option . ' is wrong: ' . $valid->getMessage());
			}
		}


		while (!$answer)
		{
			$answer = (string) $this->ioStyle->ask($question);

			if ($answer)
			{
				$valid = $field->validate($answer);

				if ($valid instanceof \Exception)
				{
					$this->ioStyle->warning('Value for ' . $option . ' is wrong: ' . $valid->getMessage());
					$answer = false;
				}
			}
		}

		return $answer;
	}

	/**
	 * Configure the IO.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function configureIO(InputInterface $input, OutputInterface $output)
	{
		$this->cliInput = $input;
		$this->ioStyle = new SymfonyStyle($input, $output);
	}
}
