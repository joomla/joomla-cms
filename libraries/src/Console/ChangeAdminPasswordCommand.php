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
class ChangeAdminPasswordCommand extends AbstractCommand
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
		$symfonyStyle->title('Change password');

		$hashPasword = \JUserHelper::hashPassword($this->password);

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
            ->select('id')
            ->from('#__users')
            ->where("username = '" . $this->adminUser . "'");
		$db->setQuery($query);

        if (!isset($db->loadColumn()[0]))
		{
		    $symfonyStyle->error('User ' . $this->adminUser . ' does not exist!');
		    return 1;
		}
		elseif(!in_array(8, UserHelper::getUserGroups(UserHelper::getUserId($this->adminUser))))
		{
		    $symfonyStyle->error('User ' . $this->adminUser . ' is not an admin');
		    return 1;
        }
        else
        {
            $user = new \stdClass();
            $user->id = UserHelper::getUserId($this->adminUser);
            $user->password = $hashPasword;

            $db->updateObject('#__users', $user, 'id');

            $symfonyStyle->success(array('User: ' . $this->adminUser,  'Password: ' . $this->password));
            return 0;
        }
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
		$this->setName('admin:chpwd');
		$this->addArgument('username', InputArgument::REQUIRED, 'Input username');
		$this->addArgument('password', InputArgument::REQUIRED, 'Input pasword');
		$this->setDescription('Changes the password of an admin user');
		$this->setHelp(
<<<EOF
The <info>%command.name%</info> command changes the password of an admin user

<info>php %command.full_name%</info>
EOF
		);
	}
}
