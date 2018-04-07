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
use Joomla\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Joomla\CMS\User\User;

/**
 * Console command for adding an admin user
 *
 * @since  __DEPLOY_VERSION__
 */
class AddUserCommand extends AbstractCommand
{
	/**
	 * The username
	 *
	 * @var string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private $user;

	/**
	 * The password
	 *
	 * @var string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private $password;

	/**
	 *  The name
	 *
	 * @var string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private $name;

	/**
	 * The email address
	 *
	 * @var string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private $email;

	/**
	 * The usergroups
	 *
	 * @var array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private $userGroups = [];

	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function execute(): int
	{
		$symfonyStyle = new SymfonyStyle($this->getApplication()->getConsoleInput(), $this->getApplication()->getConsoleOutput());
		$this->user = $this->getStringFromOption('username', 'Please enter a username');
		$this->name = $this->getStringFromOption('name', 'Please enter a name');
		$this->email = $this->getStringFromOption('email', 'Please enter a email adress');
		$this->password = $this->getStringFromOption('password', 'Please enter a password');
		$this->userGroups = $this->getUserGroups();
		$symfonyStyle->title('Add user');

		$user['username'] = $this->user;
		$user['password'] = $this->password;
		$user['name'] = $this->name;
		$user['email'] = $this->email;
		$user['groups'] = $this->userGroups;
		$userObj = User::getInstance();
		$userObj->bind($user);

		if (!$userObj->save())
		{
			$symfonyStyle->error($userObj->getError());

			return 1;
		}

		$symfonyStyle->success(array('User: ' . $this->user,  'Password: ' . $this->password));

		return 0;
	}

	/**
	 * Method to get groupId by groupNme
	 *
	 * @param   string  $groupName  name of group
	 *
	 * @return int
	 *
	 * since __DEPLOY_VERSION__
	 */
	protected function getGroupId($groupName)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__usergroups'))
			->where($db->quoteName('title') . '=' . $db->quote($groupName));
		$db->setQuery($query);

		$groupID = $db->loadResult();

		return $groupID;
	}

	/**
	 * Method to get an value from option
	 *
	 * @param   string  $option    set the option name
	 *
	 * @param   string  $question  set the question if user gives no value to option
	 *
	 * @return string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getStringFromOption($option, $question): string
	{
		$value = (string) $this->getApplication()->getConsoleInput()->getOption($option);

		if (!$value)
		{
			if ($option === 'password')
			{
				$answer = (string) $this->createSymfonyStyle()->askHidden($question);
			}
			else
			{
				$answer = (string) $this->createSymfonyStyle()->ask($question);
			}

			return $answer;
		}

		return $value;
	}

	/**
	 * Method to get a value from option
	 *
	 * @return array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getUserGroups(): array
	{
		$option = $this->getApplication()->getConsoleInput()->getOption('usergroup');

		if (!isset($option[0]))
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
			->select($db->quoteName('title'))
			->from($db->quoteName('#__usergroups'))
			->where($db->quoteName('title') . '!=' . $db->quote('Super Users'))
			->order('id ASC');
			$db->setQuery($query);

			$result = $db->loadObjectList();
			$list = [];

			foreach ($result as $key => $value)
			{
				$list[$key] = (array) $value;
				$list[$key] = $list[$key]['title'];
			}

			$choice = new ChoiceQuestion(
				'Please select a usergroup',
				$list
			);
			$choice->setMultiselect(true);

			$answer = (array) $this->createSymfonyStyle()->askQuestion($choice);

			$groupList = [];

			foreach ($answer as $group)
			{
				array_push($groupList, $this->getGroupId($group));
			}

			return $groupList;
		}
		else
		{
			$groupList = [];
			$option = explode(',', $option);

			foreach ($option as $group)
			{
				array_push($groupList, $this->getGroupId($group));
			}

			return $groupList;
		}
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function initialise()
	{
		$this->setName('user:add');
		$this->addOption('username', null, InputOption::VALUE_OPTIONAL);
		$this->addOption('name', null, InputOption::VALUE_OPTIONAL);
		$this->addOption('password', null, InputOption::VALUE_OPTIONAL);
		$this->addOption('email', null, InputOption::VALUE_OPTIONAL);
		$this->addOption('usergroup', null, InputOption::VALUE_OPTIONAL);
		$this->setDescription('Adds an user');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command adds an user

<info>php %command.full_name%</info>
EOF
		);
	}
}
