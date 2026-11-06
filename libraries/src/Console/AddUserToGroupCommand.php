<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\Access\Access;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command to add a user to group
 *
 * @since  4.0.0
 */
class AddUserToGroupCommand extends AbstractCommand
{
    use DatabaseAwareTrait;

    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'user:addtogroup';

    /**
     * SymfonyStyle Object
     * @var   object
     * @since 4.0.0
     */
    private $ioStyle;

    /**
     * Stores the Input Object
     * @var   object
     * @since 4.0.0
     */
    private $cliInput;

    /**
     * The username
     *
     * @var    string
     *
     * @since  4.0.0
     */
    private $username;

    /**
     * The usergroups
     *
     * @var    array
     *
     * @since  4.0.0
     */
    private $userGroups = [];

    /**
     * Command constructor.
     *
     * @param   DatabaseInterface  $db  The database
     *
     * @since   4.2.0
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct();

        $this->setDatabase($db);
    }

    /**
     * Internal function to execute the command.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  integer  The command exit code
     *
     * @since   4.0.0
     */
    protected function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $this->configureIO($input, $output);
        $this->ioStyle->title('Add User To Group');
        $this->username = $this->getStringFromOption('username', 'Please enter a username');

        $userId = $this->getUserId($this->username);

        if (empty($userId)) {
            $this->ioStyle->error("The user " . $this->username . " does not exist!");

            return Command::FAILURE;
        }

        // Fetch user
        $user = User::getInstance($userId);

        $this->userGroups = $this->getGroups($user);

        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__usergroups'))
            ->where($db->quoteName('id') . ' = :userGroup');

        foreach ($this->userGroups as $userGroup) {
            $query->bind(':userGroup', $userGroup);
            $db->setQuery($query);

            $result = $db->loadResult();

            if (UserHelper::addUserToGroup($user->id, $userGroup)) {
                $this->ioStyle->success("Added '" . $user->username . "' to group '" . $result . "'!");
            } else {
                $this->ioStyle->error("Can't add '" . $user->username . "' to group '" . $result . "'!");

                return Command::FAILURE;
            }
        }

        return Command::SUCCESS;
    }


    /**
     * Method to get a value from option
     *
     * @param   User  $user  a UserInstance
     *
     * @return  array
     *
     * @since   4.0.0
     */
    protected function getGroups($user): array
    {
        $groups = $this->getApplication()->getConsoleInput()->getOption('group');

        $db = $this->getDatabase();

        $groupList = [];

        // Group names have been supplied as input arguments
        if ($groups) {
            $groups = explode(',', $groups);

            foreach ($groups as $group) {
                $groupId = $this->getGroupId($group);

                if (empty($groupId)) {
                    $this->ioStyle->error("Invalid group name '" . $group . "'");
                    throw new InvalidOptionException("Invalid group name " . $group);
                }

                $groupList[] = $this->getGroupId($group);
            }

            return $groupList;
        }

        $userGroups = Access::getGroupsByUser($user->id, false);

        // Generate select list for user
        $query = $db->createQuery()
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__usergroups'))
            ->whereNotIn($db->quoteName('id'), $userGroups)
            ->order($db->quoteName('id') . ' ASC');
        $db->setQuery($query);

        $list = $db->loadColumn();

        $choice = new ChoiceQuestion(
            'Please select a usergroup (separate multiple groups with a comma)',
            $list
        );
        $choice->setMultiselect(true);

        $answer = (array) $this->ioStyle->askQuestion($choice);

        foreach ($answer as $group) {
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
     * @since   4.0.0
     */
    protected function getGroupId($groupName)
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__usergroups'))
            ->where($db->quoteName('title') . '= :groupName')
            ->bind(':groupName', $groupName);
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Method to get a user object
     *
     * @param   string  $username  username
     *
     * @return  object
     *
     * @since   4.0.0
     */
    protected function getUserId($username)
    {
        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('username') . '= :username')
            ->bind(':username', $username);
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
     * @since   4.0.0
     */
    protected function getStringFromOption($option, $question): string
    {
        $answer = (string) $this->getApplication()->getConsoleInput()->getOption($option);

        while (!$answer) {
            $answer = (string) $this->ioStyle->ask($question);
        }

        return $answer;
    }

    /**
     * Configure the IO.
     *
     * @param   InputInterface   $input   The input to inject into the command.
     * @param   OutputInterface  $output  The output to inject into the command.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    private function configureIO(InputInterface $input, OutputInterface $output)
    {
        $this->cliInput = $input;
        $this->ioStyle  = new SymfonyStyle($input, $output);
    }

    /**
     * Configure the command.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function configure(): void
    {
        $help = "<info>%command.name%</info> adds a user to a group
		\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('Add a user to a group');
        $this->addOption('username', null, InputOption::VALUE_OPTIONAL, 'username');
        $this->addOption('group', null, InputOption::VALUE_OPTIONAL, 'group');
        $this->setHelp($help);
    }
}
