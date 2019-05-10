<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\ParameterType;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;


/**
 * Console command to remove a user from group
 *
 * @since  __DEPLOY_VERSION__
 */
class RemoveUserFromGroupCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'user:removefromgroup';

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
		$this->username = $this->getStringFromOption('username', 'Please enter a username');
		$this->ioStyle->title('Remove user from group');

		$userId = UserHelper::getUserId($this->username);

		if (empty($userId))
		{
			$this->ioStyle->error("The user " . $this->username . " does not exist!");

			return 1;
		}

		$user = User::getInstance($userId);

		$this->userGroups = $this->getGroups($user);

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('title'))
			->from($db->quoteName('#__usergroups'))
			->where($db->quoteName('id') . ' = :userGroup');

		foreach ($this->userGroups as $userGroup)
		{
			$query->bind(':userGroup', $userGroup);
			$db->setQuery($query);

			$result = $db->loadResult();

			if (Access::checkGroup($userGroup, 'core.admin'))
			{
				$queryUser = $db->getQuery(true);
				$queryUser->select('COUNT(*)')
					->from($db->quoteName('#__users', 'u'))
					->leftJoin($db->quoteName('#__user_usergroup_map', 'g'),
						'(' . $db->quoteName('u.id') . ' = ' . $db->quoteName('g.user_id') . ')'
					)
					->where($db->quoteName('g.group_id') . " = :groupId")
					->where($db->quoteName('u.block') . " = 0")
					->bind(':groupId', $userGroup);

				$db->setQuery($queryUser);
				$activeSuperUser = $db->loadResult();

				if ($activeSuperUser < 2)
				{
					$this->ioStyle->error("Can't remove user '" . $user->username . "' from group '" . $result . "'! "
						. $result . " needs at least one active user!"
					);

					return 1;
				}
			}

			if (count($user->groups) < 2)
			{
				$this->ioStyle->error("Can't remove '" . $user->username . "' from group '" . $result . "'! Every user needs at least one group");

				return 1;
			}

			if (!UserHelper::removeUserFromGroup($user->id, $userGroup))
			{
				$this->ioStyle->error("Can't remove '" . $user->username . "' from group '" . $result . "'!");

				return 1;
			}

			$this->ioStyle->success("Remove '" . $user->username . "' from group '" . $result . "'!");
		}


		return 0;
	}

	/**
	 * Method to get a value from option
	 *
	 * @param   object  $user  user object
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getGroups($user): array
	{
		$option = $this->getApplication()->getConsoleInput()->getOption('group');
		$db = Factory::getDbo();

		if (!$option)
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__usergroups'))
				->whereIn($db->quoteName('id'), $user->groups);
			$db->setQuery($query);

			$result = $db->loadColumn();

			$choice = new ChoiceQuestion(
				'Please select a usergroup (separate multiple groups with a comma)',
				$result
			);
			$choice->setMultiselect(true);

			$answer = (array) $this->ioStyle->askQuestion($choice);

			$groupList = [];

			foreach ($answer as $group)
			{
				$groupList[] = $this->getGroupId($group);
			}

			return $groupList;
		}

		$groupList = [];
		$option = explode(',', $option);

		foreach ($option as $group)
		{
			$groupId = $this->getGroupId($group);

			if (empty($groupId))
			{
				throw new InvalidOptionException("Invalid group name " . $group);
			}

			$groupList[] = $this->getGroupId($group);
		}

		return $groupList;
	}

	/**
	 * Method to get groupId by groupName
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
			->where($db->quoteName('title') . '= :groupName')
			->bind(':groupName', $groupName);
		$db->setQuery($query);

		return $db->loadResult();
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
		$value = (string) $this->getApplication()->getConsoleInput()->getOption($option);

		if (!$value)
		{
			return (string) $this->ioStyle->ask($question);
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
		$this->setDescription('Remove a user from a group');
		$this->addOption('username', null, InputOption::VALUE_OPTIONAL, 'username');
		$this->addOption('group', null, InputOption::VALUE_OPTIONAL, 'group');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command removes a user from a group

<info>php %command.full_name%</info>
EOF
		);
	}
}
