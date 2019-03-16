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
use Joomla\CMS\User\UserHelper;
use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\ChoiceQuestion;


/**
 * Console command to remove an user from group
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

		$user = $this->getUser($this->username);
		$this->userGroups = $this->getGroups($user);

		foreach ($this->userGroups as $userGroup)
		{
			$db = Factory::getDbo();
			$querry = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__usergroups'))
				->where($db->quoteName('id') . ' = ' . $userGroup);
			$db->setQuery($querry);

			$result = $db->loadResult();

			if (UserHelper::removeUserFromGroup($user->id, $userGroup))
			{
				$this->ioStyle->success("Remove '" . $user->username . "' successfully from group '" . $result . "'!");
			}
			else
			{
				$this->ioStyle->error("Can't remove '" . $user->username . "' successfully from group '" . $result . "'!");

				return 1;
			}
		}

		return 0;
	}

	/**
	 * Method to get a value from option
	 *
	 * @param   object $user user object
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getGroups($user): array
	{
		$option = $this->getApplication()->getConsoleInput()->getOption('group');
		$list = array();
		$currentGroups = array();

		if (!isset($option[0]))
		{
			foreach ($user->groups as $groupId)
			{
				$db = Factory::getDbo();
				$query = $db->getQuery(true)
					->select($db->quoteName('title'))
					->from($db->quoteName('#__usergroups'))
					->where($db->quoteName('id') . ' = ' . $groupId);
				$db->setQuery($query);

				$result = $db->loadObject();

				array_push($currentGroups, $result);
			}

			$prefix = $currentTitle = '';

			foreach ($currentGroups as $group)
			{
				print_r($group->title);
				$currentTitle .= $prefix . "'" . $group->title . "'";
				$prefix = ', ';
			}

			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__usergroups'))
				->where($db->quoteName('title') . 'IN(' . $currentTitle . ')');
			$db->setQuery($query);

			$result = $db->loadObjectList();

			foreach ($result as $key => $value)
			{
				$list[$key] = $value->title;
			}

			$choice = new ChoiceQuestion(
				'Please select a usergroup (multiple select comma separated)',
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
			->where($db->quoteName('title') . '=' . $db->quote($groupName));
		$db->setQuery($query);

		$groupID = $db->loadResult();

		return $groupID;
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
	protected function getUser($username)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('username') . '=' . $db->quote($username));
		$db->setQuery($query);

		$userId = $db->loadResult();
		$user = User::getInstance($userId);

		return $user;
	}

	/**
	 * Method to get an value from option
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
			$answer = (string) $this->ioStyle->ask($question);

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
		$this->setDescription('Remove an user from group');
		$this->addOption('username', null, InputOption::VALUE_OPTIONAL, 'username');
		$this->addOption('group', null, InputOption::VALUE_OPTIONAL, 'group');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command remove an user from group

<info>php %command.full_name%</info>
EOF
		);
	}
}
