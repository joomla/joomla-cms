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
use Joomla\CMS\User\User;

/**
 * Console command for adding an admin user
 *
 * @since  __DEPLOY_VERSION__
 */
class ChangeUserPasswordCommand extends AbstractCommand
{
	/**
	 * The username 
	 *
	 * @var string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private $username;

	/**
	 * The password
	 *
	 * @var string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private $password;

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
		$this->username = $this->getStringFromOption('username', 'Please enter a username');
		$this->password = $this->getStringFromOption('password', 'Please enter a password');
		$symfonyStyle->title('Change password');

		$oldUserObj = $this->getUser($this->username);
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
			$symfonyStyle->error($userObj->getError());

			return 1;
		}

		$symfonyStyle->success(array('User: ' . $this->username,  'Password: ' . $this->password));

		return 0;
	}

	/**
	 * Method to get a user object
	 *
	 * @param   string  $username  username
	 *
	 * @return object
	 *
	 * @since __DEPLOY_VERSION__
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
	 * @param   string  $question  set the question if user gives no value to option
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getStringFromOption($option, $question): string
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
	 * Initialise the command.
	 *
	 * @return   void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function initialise()
	{
		$this->setName('user:reset-password');
		$this->addOption('username', null, InputOption::VALUE_OPTIONAL);
		$this->addOption('password', null, InputOption::VALUE_OPTIONAL);
		$this->setDescription('Changes a users password');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command changes the user password

<info>php %command.full_name%</info>
EOF
		);
	}
}
