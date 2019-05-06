<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;


defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Joomla\CMS\User\User;

/**
 * Console command for change a users password
 *
 * @since  __DEPLOY_VERSION__
 */
class ChangeUserPasswordCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'user:reset-password';

	/**
	 * SymfonyStyle Object
	 * @var   object
	 * @since __DEPLOY_VERSION__
	 */
	private $ioStyle;

	/**
	 * Stores the Input Object
	 * @var   object
	 * @since __DEPLOY_VERSION__
	 */
	private $cliInput;

	/**
	 * The username
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $username;

	/**
	 * The password
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doExecute(InputInterface $input, OutputInterface $output): int
	{
		$this->configureIO($input, $output);
		$this->username = $this->getStringFromOption('username', 'Please enter a username');
		$this->ioStyle->title('Change password');

		$userId = $this->getUserId($this->username);

		if (empty($userId))
		{
			$this->ioStyle->error("The user " . $this->username . " does not exist!");

			return 1;
		}
		else
		{
			$oldUserObj = User::getInstance($userId);
			$this->password = $this->getStringFromOption('password', 'Please enter a password');
		}

		$user['username'] = $this->username;
		$user['password'] = $this->password;
		$user['name'] = $oldUserObj->name;
		$user['email'] = $oldUserObj->email;
		$user['groups'] = $oldUserObj->groups;
		$user['id'] = $oldUserObj->id;
		$userObj = User::getInstance();
		$userObj->bind($user);

		if (!$userObj->save(true))
		{
			$this->ioStyle->error($userObj->getError());

			return 1;
		}

		$this->ioStyle->success("set password!");
		$this->ioStyle->table(['user', 'password'],  [array($oldUserObj->username, $this->password)]);

		return 0;
	}

	/**
	 * Method to get a user object
	 *
	 * @param   string  $username  username
	 *
	 * @return  object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getUserId($username)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('username') . '= :username')
			->bind(':username', $username);
		$db->setQuery($query);

		$userId = $db->loadResult();

		return $userId;
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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getStringFromOption($option, $question): string
	{
		$value = (string) $this->cliInput->getOption($option);

		if (!$value)
		{
			if ($option === 'password')
			{
				$answer = (string) $this->ioStyle->askHidden($question);
			}
			else
			{
				$answer = (string) $this->ioStyle->ask($question);
			}

			return $answer;
		}

		return $value;
	}

	/**
	 * Configure the IO.
	 *
	 * @param   InputInterface   $input   The input to inject into the command.
	 * @param   OutputInterface  $output  The output to inject into the command.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function configure()
	{
		$this->addOption('username', null, InputOption::VALUE_OPTIONAL, 'username');
		$this->addOption('password', null, InputOption::VALUE_OPTIONAL, 'password');
		$this->setDescription("Changes a user's password");
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command changes the user's password

<info>php %command.full_name%</info>
EOF
		);
	}
}
