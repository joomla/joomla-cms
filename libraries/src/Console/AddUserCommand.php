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
use Joomla\CMS\User\User;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for adding an user
 *
 * @since  __DEPLOY_VERSION__
 */
class AddUserCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'user:add';

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
	private $user;

	/**
	 * The password
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $password;

	/**
	 *  The name
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $name;

	/**
	 * The email address
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $email;

	/**
	 * The usergroups
	 *
	 * @var    array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $userGroups = array();

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
		$this->user = $this->getStringFromOption('username', 'Please enter a username');
		$this->name = $this->getStringFromOption('name', 'Please enter a name (full name of user)');
		$this->email = $this->getStringFromOption('email', 'Please enter a email address');
		$this->password = $this->getStringFromOption('password', 'Please enter a password');
		$this->userGroups = $this->getUserGroups();
		$this->ioStyle->title('Add user');

		if (in_array("error", $this->userGroups))
		{
			$this->ioStyle->error("'" . $this->userGroups[1] . "' user group doesn't exit!");

			return 1;
		}

		$user['username'] = $this->user;
		$user['password'] = $this->password;
		$user['name'] = $this->name;
		$user['email'] = $this->email;
		$user['groups'] = $this->userGroups;
		$userObj = User::getInstance();
		$userObj->bind($user);

		if (!$userObj->save())
		{
			switch ($userObj->getError())
			{
				case "LIB_DATABASE_ERROR_USERNAME_INUSE":
					$this->ioStyle->error("The username is already exists!");
				case "JLIB_DATABASE_ERROR_EMAIL_INUSE  ":
					$this->ioStyle->error("The email address is already exists!");
				case "JLIB_DATABASE_ERROR_VALID_MAIL":
					$this->ioStyle->error("The email address is invalid!");
			}

			return 1;
		}

		$this->ioStyle->success("create user successfully!");
		$this->ioStyle->table(['user', 'password'],  [array($this->user, $this->password)]);

		return 0;
	}

	/**
	 * Method to get groupId by groupNme
	 *
	 * @param   string  $groupName  name of group
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getGroupId($groupName)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__usergroups'))
			->where($db->quoteName('title') . ' = :groupName')
			->bind(':groupName', $groupName);
		$db->setQuery($query);

		$groupID = $db->loadResult();

		return $groupID;
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
	public function getStringFromOption($option, $question): string
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
	 * Method to get a value from option
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getUserGroups(): array
	{
		$option = $this->getApplication()->getConsoleInput()->getOption('usergroup');
		$db = Factory::getDbo();

		if (!isset($option[0]))
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__usergroups'))
				->order('id ASC');
			$db->setQuery($query);

			$result = $db->loadObjectList();
			$list = array();

			foreach ($result as $key => $value)
			{
				$list[$key] = $value->title;
			}

			$choice = new ChoiceQuestion(
				'Please select a usergroup (separate multiple groups with a comma)',
				$list
			);
			$choice->setMultiselect(true);

			$answer = (array) $this->ioStyle->askQuestion($choice);

			$groupList = array();

			foreach ($answer as $group)
			{
				array_push($groupList, $this->getGroupId($group));
			}

			return $groupList;
		}
		else
		{
			$groupList = array();
			$option = explode(',', $option);

			foreach ($option as $group)
			{
				$groupId = $this->getGroupId($group);

				if (empty($groupId))
				{
					$groupList = array(
						"error",
						$group,
					);

					return 	$groupList;
				}

				array_push($groupList, $this->getGroupId($group));
			}

			return $groupList;
		}
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
		$this->addOption('name', null, InputOption::VALUE_OPTIONAL, 'full name of user');
		$this->addOption('password', null, InputOption::VALUE_OPTIONAL, 'password');
		$this->addOption('email', null, InputOption::VALUE_OPTIONAL, 'email address');
		$this->addOption('usergroup', null, InputOption::VALUE_OPTIONAL, 'usergroup (separate multiple groups with comma ",")');
		$this->setDescription('Add a user');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command adds a user

<info>php %command.full_name%</info>
EOF
		);
	}
}
