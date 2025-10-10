<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

use Joomla\Console\Command\AbstractCommand;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Console command for displaying configuration options
 *
 * @since  4.0.0
 */
class GetConfigurationCommand extends AbstractCommand
{
    /**
     * The default command name
     *
     * @var    string
     * @since  4.0.0
     */
    protected static $defaultName = 'config:get';

    /**
     * Stores the Input Object
     * @var Input
     * @since 4.0.0
     */
    private $cliInput;

    /**
     * SymfonyStyle Object
     * @var SymfonyStyle
     * @since 4.0.0
     */
    private $ioStyle;

    /**
     * Constant defining the Database option group
     * @var array
     * @since 4.0.0
     */
    public const DB_GROUP = [
        'name'    => 'db',
        'options' => [
            'dbtype',
            'host',
            'user',
            'password',
            'dbprefix',
            'db',
            'dbencryption',
            'dbsslverifyservercert',
            'dbsslkey',
            'dbsslcert',
            'dbsslca',
            'dbsslcipher',
        ],
    ];

    /**
     * Constant defining the Session option group
     * @var array
     * @since 4.0.0
     */
    public const SESSION_GROUP = [
        'name'    => 'session',
        'options' => [
            'session_handler',
            'shared_session',
            'session_metadata',
        ],
    ];

    /**
     * Constant defining the Mail option group
     * @var array
     * @since 4.0.0
     */
    public const MAIL_GROUP = [
        'name'    => 'mail',
        'options' => [
            'mailonline',
            'mailer',
            'mailfrom',
            'fromname',
            'sendmail',
            'smtpauth',
            'smtpuser',
            'smtppass',
            'smtphost',
            'smtpsecure',
            'smtpport',
        ],
    ];

    /**
     * Return code if configuration is get successfully
     * @since 4.0.0
     */
    public const CONFIG_GET_SUCCESSFUL = 0;

    /**
     * Return code if configuration group option is not found
     * @since 4.0.0
     */
    public const CONFIG_GET_GROUP_NOT_FOUND = 1;

    /**
     * Return code if configuration option is not found
     * @since 4.0.0
     */
    public const CONFIG_GET_OPTION_NOT_FOUND = 2;

    /**
     * Return code if the command has been invoked with wrong options
     * @since 4.0.0
     */
    public const CONFIG_GET_OPTION_FAILED = 3;

    /**
     * Configures the IO
     *
     * @param   InputInterface   $input   Console Input
     * @param   OutputInterface  $output  Console Output
     *
     * @return void
     *
     * @since 4.0.0
     *
     */
    private function configureIO(InputInterface $input, OutputInterface $output)
    {
        $this->cliInput = $input;
        $this->ioStyle  = new SymfonyStyle($input, $output);
    }


    /**
     * Displays logically grouped options
     *
     * @param   string  $group  The group to be processed
     *
     * @return integer
     *
     * @since 4.0.0
     */
    public function processGroupOptions($group): int
    {
        $configs = $this->getApplication()->getConfig()->toArray();
        $configs = $this->formatConfig($configs);

        $groups = $this->getGroups();

        $foundGroup = false;

        foreach ($groups as $key => $value) {
            if ($value['name'] === $group) {
                $foundGroup = true;
                $options    = [];

                foreach ($value['options'] as $option) {
                    $options[] = [$option, $configs[$option]];
                }

                $this->ioStyle->table(['Option', 'Value'], $options);
            }
        }

        if (!$foundGroup) {
            $this->ioStyle->error("Group *$group* not found");

            return self::CONFIG_GET_GROUP_NOT_FOUND;
        }

        return self::CONFIG_GET_SUCCESSFUL;
    }

    /**
     * Gets the defined option groups
     *
     * @return array
     *
     * @since 4.0.0
     */
    public function getGroups()
    {
        return [
            self::DB_GROUP,
            self::MAIL_GROUP,
            self::SESSION_GROUP,
        ];
    }

    /**
     * Formats the configuration array into desired format
     *
     * @param   array  $configs  Array of the configurations
     *
     * @return array
     *
     * @since 4.0.0
     */
    public function formatConfig(array $configs): array
    {
        $newConfig = [];

        foreach ($configs as $key => $config) {
            $config = $config === false ? "false" : $config;
            $config = $config === true ? "true" : $config;

            if (!\in_array($key, ['cwd', 'execution'])) {
                $newConfig[$key] = $config;
            }
        }

        return $newConfig;
    }

    /**
     * Handles the command when a single option is requested
     *
     * @param   string  $option  The option we want to get its value
     *
     * @return integer
     *
     * @since 4.0.0
     */
    public function processSingleOption($option): int
    {
        $configs = $this->getApplication()->getConfig()->toArray();

        if (!\array_key_exists($option, $configs)) {
            $this->ioStyle->error("Can't find option *$option* in configuration list");

            return self::CONFIG_GET_OPTION_NOT_FOUND;
        }

        $value = $this->formatConfigValue($this->getApplication()->get($option));

        $this->ioStyle->table(['Option', 'Value'], [[$option, $value]]);

        return self::CONFIG_GET_SUCCESSFUL;
    }

    /**
     * Formats the Configuration value
     *
     * @param   mixed  $value  Value to be formatted
     *
     * @return string
     *
     * @since 4.0.0
     */
    protected function formatConfigValue($value): string
    {
        if ($value === false) {
            return 'false';
        }

        if ($value === true) {
            return 'true';
        }

        if ($value === null) {
            return 'Not Set';
        }

        if (\is_array($value)) {
            return json_encode($value);
        }

        if (\is_object($value)) {
            return json_encode(get_object_vars($value));
        }

        return $value;
    }

    /**
     * Initialise the command.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function configure(): void
    {
        $groups = $this->getGroups();

        foreach ($groups as $key => $group) {
            $groupNames[] = $group['name'];
        }

        $groupNames = implode(', ', $groupNames);

        $this->addArgument('option', null, 'Name of the option');
        $this->addOption('group', 'g', InputOption::VALUE_REQUIRED, 'Name of the option');

        $help = "<info>%command.name%</info> displays the current value of a configuration option
				\nUsage: <info>php %command.full_name%</info> <option>
				\nGroup usage: <info>php %command.full_name%</info> --group <groupname>
				\nAvailable group names: $groupNames";

        $this->setDescription('Display the current value of a configuration option');
        $this->setHelp($help);
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

        $configs = $this->formatConfig($this->getApplication()->getConfig()->toArray());

        $option = $this->cliInput->getArgument('option');
        $group  = $this->cliInput->getOption('group');

        if ($group) {
            return $this->processGroupOptions($group);
        }

        if ($option) {
            return $this->processSingleOption($option);
        }

        if (!$option && !$group) {
            $options = [];

            array_walk(
                $configs,
                function ($value, $key) use (&$options) {
                    $options[] = [$key, $this->formatConfigValue($value)];
                }
            );

            $this->ioStyle->title("Current options in Configuration");
            $this->ioStyle->table(['Option', 'Value'], $options);

            return self::CONFIG_GET_SUCCESSFUL;
        }

        return self::CONFIG_GET_OPTION_NOT_FOUND;
    }
}
