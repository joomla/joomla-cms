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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command to list existing users
 *
 * @since  __DEPLOY_VERSION__
 */
class ListUserCommand extends AbstractCommand
{
	/**
	 * The default command name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $defaultName = 'user:list';

	/**
	 * SymfonyStyle Object
	 * @var object
	 * @since __DEPLOY_VERSION__
	 */
	private $ioStyle;

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
		$this->ioStyle->title('List users');

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('id', 'username', 'name', 'email', 'block')))
			->from($db->quoteName('#__users'));
		$db->setQuery($query);

		$users[] = array();

		$queryUserGroupMap = $db->getQuery(true)
			->select($db->quoteName('group_id'))
			->from($db->quoteName('#__user_usergroup_map'))
			->where($db->quoteName('user_id') . ' = :userId');

		$queryUserGroup = $db->getQuery(true)
			->select($db->quoteName('title'))
			->from($db->quoteName('#__usergroups'))
			->where($db->quoteName('id') . ' = :groupId');

		foreach ($db->loadObjectList() as $user)
		{
			$user = json_decode(json_encode($user), true);
			$queryUserGroupMap->bind(':userId', $user['id']);
			$db->setQuery($queryUserGroupMap);

			$allGroups = '';

			foreach ($db->loadObjectList() as $group)
			{
				$group = json_decode(json_encode($group), true);
				$queryUserGroup->bind(':groupId', $group['group_id']);
				$db->setQuery($queryUserGroup);

				$groupName = $db->loadResult();

				if (preg_match_all('/\s/', $groupName))
				{
					$groupName = "'" . $groupName . "'";
				}

				if (empty($allGroups))
				{
					$allGroups = $groupName;
				}
				else
				{
					$allGroups = $allGroups . ', ' . $groupName;
				}
			}

			array_push($user, $allGroups);
			array_push($users, $user);
		}

		$this->ioStyle->table(['id', 'username', 'name', 'email', 'blocked', 'groups'], $users);

		return 0;
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
		$this->setDescription('List all users');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command lists all users

<info>php %command.full_name%</info>
EOF
		);
	}
}
