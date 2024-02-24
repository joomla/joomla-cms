<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\CMS\User\User;
use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Filter\InputFilter;
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
 * Console command for adding a user
 *
 * @since  4.0.0
 */
class AddUserCommand extends AbstractCommand
{
    use DatabaseAwareTrait;

    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'user:add';

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
    private $user;

    /**
     * The password
     *
     * @var    string
     *
     * @since  4.0.0
     */
    private $password;

    /**
     *  The name
     *
     * @var    string
     *
     * @since  4.0.0
     */
    private $name;

    /**
     * The email address
     *
     * @var    string
     *
     * @since  4.0.0
     */
    private $email;

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
        $this->ioStyle->title('Add User');
        $this->user       = $this->getStringFromOption('username', 'Please enter a username');
        $this->name       = $this->getStringFromOption('name', 'Please enter a name (full name of user)');
        $this->email      = $this->getStringFromOption('email', 'Please enter an email address');
        $this->password   = $this->getStringFromOption('password', 'Please enter a password');
        $this->userGroups = $this->getUserGroups();

        if (\in_array("error", $this->userGroups)) {
            $this->ioStyle->error("'" . $this->userGroups[1] . "' user group doesn't exist!");

            return Command::FAILURE;
        }

        // Get filter to remove invalid characters
        $filter = new InputFilter();

        $user = [
            'username' => $filter->clean($this->user, 'USERNAME'),
            'password' => $this->password,
            'name'     => $filter->clean($this->name, 'STRING'),
            'email'    => $this->email,
            'groups'   => $this->userGroups,
        ];

        $userObj = User::getInstance();
        $userObj->bind($user);

        if (!$userObj->save()) {
            switch ($userObj->getError()) {
                case "JLIB_DATABASE_ERROR_USERNAME_INUSE":
                    $this->ioStyle->error("The username already exists!");
                    break;
                case "JLIB_DATABASE_ERROR_EMAIL_INUSE":
                    $this->ioStyle->error("The email address already exists!");
                    break;
                case "JLIB_DATABASE_ERROR_VALID_MAIL":
                    $this->ioStyle->error("The email address is invalid!");
                    break;
            }

            return 1;
        }

        $this->ioStyle->success("User created!");

        return Command::SUCCESS;
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
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__usergroups'))
            ->where($db->quoteName('title') . ' = :groupName')
            ->bind(':groupName', $groupName);
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Method to get a value from option
     *
     * @param   string  $option    set the option name
     * @param   string  $question  set the question if user enters no value to option
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function getStringFromOption($option, $question): string
    {
        $answer = (string) $this->cliInput->getOption($option);

        while (!$answer) {
            if ($option === 'password') {
                $answer = (string) $this->ioStyle->askHidden($question);
            } else {
                $answer = (string) $this->ioStyle->ask($question);
            }
        }

        return $answer;
    }

    /**
     * Method to get a value from option
     *
     * @return  array
     *
     * @since   4.0.0
     */
    protected function getUserGroups(): array
    {
        $groups = $this->getApplication()->getConsoleInput()->getOption('usergroup');
        $db     = $this->getDatabase();

        $groupList = [];

        // Group names have been supplied as input arguments
        if (!\is_null($groups) && $groups[0]) {
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

        // Generate select list for user
        $query = $db->getQuery(true)
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__usergroups'))
            ->order($db->quoteName('id') . 'ASC');
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
        $help = "<info>%command.name%</info> will add a user
		\nUsage: <info>php %command.full_name%</info>";

        $this->addOption('username', null, InputOption::VALUE_OPTIONAL, 'username');
        $this->addOption('name', null, InputOption::VALUE_OPTIONAL, 'full name of user');
        $this->addOption('password', null, InputOption::VALUE_OPTIONAL, 'password');
        $this->addOption('email', null, InputOption::VALUE_OPTIONAL, 'email address');
        $this->addOption('usergroup', null, InputOption::VALUE_OPTIONAL, 'usergroup (separate multiple groups with comma ",")');
        $this->setDescription('Add a user');
        $this->setHelp($help);
    }
}
