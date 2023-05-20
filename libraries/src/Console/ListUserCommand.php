<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\Console\Command\AbstractCommand;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\DatabaseInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command to list existing users
 *
 * @since  4.0.0
 */
class ListUserCommand extends AbstractCommand
{
    use DatabaseAwareTrait;

    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'user:list';

    /**
     * SymfonyStyle Object
     * @var object
     * @since 4.0.0
     */
    private $ioStyle;

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
        $db = $this->getDatabase();

        $this->configureIO($input, $output);
        $this->ioStyle->title('List Users');

        $groupsQuery = $db->getQuery(true)
            ->select($db->quoteName(['title', 'id']))
            ->from($db->quoteName('#__usergroups'));

        $groups = $db->setQuery($groupsQuery)->loadAssocList('id', 'title');

        $query = $db->getQuery(true);
        $query->select($db->quoteName(['u.id', 'u.username', 'u.name', 'u.email', 'u.block']))
            ->select($query->groupConcat($query->castAs('CHAR', $db->quoteName('g.group_id'))) . ' AS ' . $db->quoteName('groups'))
            ->innerJoin($db->quoteName('#__user_usergroup_map', 'g'), $db->quoteName('g.user_id') . ' = ' . $db->quoteName('u.id'))
            ->from($db->quoteName('#__users', 'u'))
            ->group($db->quoteName('u.id'));
        $db->setQuery($query);

        $users = [];

        foreach ($db->loadAssocList() as $user) {
            $user["groups"] = array_map(
                function ($groupId) use ($groups) {
                    return $groups[$groupId];
                },
                explode(",", $user["groups"])
            );

            $user["groups"] = implode(", ", $user["groups"]);
            $users[]        = $user;
        }

        $this->ioStyle->table(['ID', 'Username', 'Name', 'Email', 'Blocked', 'Groups'], $users);

        return Command::SUCCESS;
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
        $help = "<info>%command.name%</info> will list all users
		\nUsage: <info>php %command.full_name%</info>";

        $this->setDescription('List all users');
        $this->setHelp($help);
    }
}
