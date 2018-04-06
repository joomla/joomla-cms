<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;


defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\Console\AbstractCommand;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Joomla\CMS\User\UserHelper;

/**
 * Console command for adding an admin user
 *
 * @since  4.0.0
 */
class AddAdminCommand extends AbstractCommand
{
	

	
	/**
	 * The username
	 *
	 * @var string
	 *
	 * @since 4.0.0
	 */
	private $adminUser;

	/**
	 * The password
	 *
	 * @var string
	 *
	 * @since 4.0.0
	 */
	private $password;

	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 *
	 * @since   4.0.0
	 */
	public function execute(): int
	{
		$symfonyStyle = new SymfonyStyle($this->getApplication()->getConsoleInput(), $this->getApplication()->getConsoleOutput());
		$this->adminUser = $this->getApplication()->getConsoleInput()->getArgument('username');
		$this->password = $this->getApplication()->getConsoleInput()->getArgument('password');
		$symfonyStyle->title('Add admin user');

		$hashPasword = \JUserHelper::hashPassword($this->password);

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('id')
			->from('#__users')
			->where("username = '" . $this->adminUser . "'");
		$db->setQuery($query);

		if (isset($db->loadColumn()[0]))
		{
			$symfonyStyle->warning('User ' . $this->adminUser . 'already exists! Use admin:chpwd to set new password!');
			return 2;
		}
		else 
		{
			$user = new \stdClass();
			$user->username = $this->adminUser;
			$user->password = $hashPasword;
			$user->params = "";
			$user->name = "Super User";

			$db->insertObject('#__users', $user);
			$userID = UserHelper::getUserId($this->adminUser);
			$userGroup = new \stdClass();
			$userGroup->user_id = $userID;
			$userGroup->group_id = 8;
			$db->insertObject('#__user_usergroup_map', $userGroup);
		}

		$symfonyStyle->success(array('User: ' . $this->adminUser,  'Password: ' . $this->password));
		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function initialise()
	{
		$this->setName('admin:add');
		$this->addArgument('username', InputArgument::REQUIRED, 'Input username');
		$this->addArgument('password', InputArgument::REQUIRED, 'Input pasword');
		$this->setDescription('Adds an admin user');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command adds an admin user

<info>php %command.full_name%</info>
EOF
		);
	}
}
