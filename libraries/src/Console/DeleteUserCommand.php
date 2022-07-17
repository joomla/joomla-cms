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
use Joomla\Database\ParameterType;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for deleting a user
 *
 * @since  4.0.0
 */
class DeleteUserCommand extends AbstractCommand
{
    use DatabaseAwareTrait;

    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'user:delete';

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

        $this->ioStyle->title('Delete User');

        $this->username = $this->getStringFromOption('username', 'Please enter a username');

        $userId = UserHelper::getUserId($this->username);
        $db     = $this->getDatabase();

        if (empty($userId)) {
            $this->ioStyle->error($this->username . ' does not exist!');

            return Command::FAILURE;
        }

        if ($input->isInteractive() && !$this->ioStyle->confirm('Are you sure you want to delete this user?', false)) {
            $this->ioStyle->note('User not deleted');

            return Command::SUCCESS;
        }

        $groups = UserHelper::getUserGroups($userId);
        $user = User::getInstance($userId);

        if ($user->block == 0) {
            foreach ($groups as $groupId) {
                if (Access::checkGroup($groupId, 'core.admin')) {
                    $queryUser = $db->getQuery(true);
                    $queryUser->select('COUNT(*)')
                        ->from($db->quoteName('#__users', 'u'))
                        ->leftJoin(
                            $db->quoteName('#__user_usergroup_map', 'g'),
                            '(' . $db->quoteName('u.id') . ' = ' . $db->quoteName('g.user_id') . ')'
                        )
                        ->where($db->quoteName('g.group_id') . " = :groupId")
                        ->where($db->quoteName('u.block') . " = 0")
                        ->bind(':groupId', $groupId, ParameterType::INTEGER);

                    $db->setQuery($queryUser);
                    $activeSuperUser = $db->loadResult();

                    if ($activeSuperUser < 2) {
                        $this->ioStyle->error("You can't delete the last active Super User");

                        return Command::FAILURE;
                    }
                }
            }
        }

        // Trigger delete of user
        $result = $user->delete();

        if (!$result) {
            $this->ioStyle->error("Can't remove " . $this->username . ' from usertable');

            return Command::FAILURE;
        }

        $this->ioStyle->success('User ' . $this->username . ' deleted!');

        return Command::SUCCESS;
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
        $this->ioStyle = new SymfonyStyle($input, $output);
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
        $help = "<info>%command.name%</info> deletes a user
		\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('Delete a user');
        $this->addOption('username', null, InputOption::VALUE_OPTIONAL, 'username');
        $this->setHelp($help);
    }
}
