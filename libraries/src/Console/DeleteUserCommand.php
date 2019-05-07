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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;


/**
 * Console command for deleting a user
 *
 * @since  __DEPLOY_VERSION__
 */
class DeleteUserCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'user:delete';

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
		$this->ioStyle->title('Delete users');

		$userId = UserHelper::getUserId($this->username);
		$db = Factory::getDbo();

		if (empty($userId))
		{
			$this->ioStyle->error($this->username . ' does not exist!');

			return 1;
		}

		$user = User::getInstance($userId);
		Factory::getApplication()->triggerEvent('onUserBeforeDelete', array($user));
		$groups = UserHelper::getUserGroups($userId);
		$queryUserGroupMap = $db->getQuery(true);
		$queryUserGroupMap->select($db->quoteName('user_id'));
		$queryUserGroupMap->from($db->quoteName('#__user_usergroup_map'));
		$queryUserGroupMap->where($db->quoteName('group_id') . " = :groupId");




		foreach ($groups as $groupId)
		{
			if (Access::checkGroup($groupId, 'core.admin'))
			{
				$queryUserGroupMap->bind(':groupId', $groupId);
				$db->setQuery($queryUserGroupMap);
				$users = $db->loadColumn();

				$queryUser = $db->getQuery(true);
				$queryUser->select('COUNT(*)')
					->from($db->quoteName('#__users'))
					->whereIn($db->quoteName('id'), $users, ParameterType::INTEGER)
					->where($db->quoteName('block') . " = 0");

				$db->setQuery($queryUser);
				$activeSuperUser = $db->loadResult();

				if ($activeSuperUser < 2 && $user->block == 0)
				{
					$this->ioStyle->error("Last active super user can't be deleted! At least one active super user needs to exist!");

					return 1;
				}
			}

			$removed = UserHelper::removeUserFromGroup($userId, $groupId);

			if ($removed == false)
			{
				$this->ioStyle->error("Can't remove " . $this->username . ' from group ' . $groupId);

				return 1;
			}
		}

		$conditions = array(
			$db->quoteName('id') . ' = ' . $userId,
		);

		$query = $db->getQuery(true)
			->delete('#__users')
			->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();

		if ($result == false)
		{
			$this->ioStyle->error("Can't remove " . $this->username . ' form usertable');

			return 1;
		}

		$conditions = array(
			$db->quoteName('user_id') . ' = ' . $userId,
		);

		$query = $db->getQuery(true)
			->delete('#__user_usergroup_map')
			->where($conditions);
		$db->setQuery($query);
		$result = $db->execute();

		if ($result == false)
		{
			$this->ioStyle->error("Can't remove " . $this->username . ' from usergroup map');

			return 1;
		}

		$this->ioStyle->success('Delete ' . $this->username . '!');
		Factory::getApplication()->triggerEvent('onUserAfterDelete', array(User::getInstance($userId)));

		return 0;
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
		$this->setDescription('Delete a user');
		$this->addOption('username', null, InputOption::VALUE_OPTIONAL, 'username');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command deletes a user

<info>php %command.full_name%</info>
EOF
		);
	}
}
