<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command  to change a user's password
 *
 * @since  4.0.0
 */
class ChangeUserPasswordCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected static $defaultName = 'user:reset-password';

	/**
	 * SymfonyStyle Object
	 * @var   object
	 * @since 4.0.0
	 */
	private $ioStyle;

	/**
	 * Stores the Input Object
	 * @var   object
	 * @since 4.0.0
	 */
	private $cliInput;

	/**
	 * The username
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	private $username;

	/**
	 * The password
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	private $password;

	/**
	 * Internal function to execute the command.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  integer  The command exit code
	 *
	 * @since   4.0.0
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$this->configureIO($input, $output);
		$this->username = $this->getStringFromOption('username', 'Please enter a username');
		$this->ioStyle->title('Change password');

		$userId = UserHelper::getUserId($this->username);

		if (empty($userId))
		{
			$this->ioStyle->error("The user " . $this->username . " does not exist!");

			return Command::FAILURE;
		}

		$user = User::getInstance($userId);
		$this->password = $this->getStringFromOption('password', 'Please enter a new password');

		$user->password = UserHelper::hashPassword($this->password);

		if (!$user->save(true))
		{
			$this->ioStyle->error($user->getError());

			return Command::FAILURE;
		}

		$this->ioStyle->success("Password changed!");

		return Command::SUCCESS;
	}

	/**
	 * Method to get a value from option
	 *
	 * @param   string  $option    set the option name
	 *
	 * @param   string  $question  set the question if user enters no value to option
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	protected function getStringFromOption($option, $question): string
	{
		$answer = (string) $this->cliInput->getOption($option);

		while (!$answer)
		{
			if ($option === 'password')
			{
				$answer = (string) $this->ioStyle->askHidden($question);
			}
			else
			{
				$answer = (string) $this->ioStyle->ask($question);
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

	/**
	 * Configure the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function configure(): void
	{
		$help = "<info>%command.name%</info> will change a user's password
		\nUsage: <info>php %command.full_name%</info>";

		$this->addOption('username', null, InputOption::VALUE_OPTIONAL, 'username');
		$this->addOption('password', null, InputOption::VALUE_OPTIONAL, 'password');
		$this->setDescription("Change a user's password");
		$this->setHelp($help);
	}
}
