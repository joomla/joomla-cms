<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application;

use Joomla\CMS\Console;
use Joomla\CMS\Extension\ExtensionManagerTrait;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Version;
use Joomla\Console\Application;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Session\SessionInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The Joomla! CMS Console Application
 *
 * @since  4.0.0
 */
class ConsoleApplication extends Application implements CMSApplicationInterface
{
    use EventAware;
    use IdentityAware;
    use ContainerAwareTrait;
    use ExtensionManagerTrait;
    use ExtensionNamespaceMapper;
    use DatabaseAwareTrait;

    /**
     * The input.
     *
     * @var    Input
     * @since  4.0.0
     */
    protected $input = null;

    /**
     * The name of the application.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $name = null;

    /**
     * The application language object.
     *
     * @var    Language
     * @since  4.0.0
     */
    protected $language;

    /**
     * The application message queue.
     *
     * @var    array
     * @since  4.0.0
     */
    private $messages = [];

    /**
     * The application session object.
     *
     * @var    SessionInterface
     * @since  4.0.0
     */
    private $session;

    /**
     * Class constructor.
     *
     * @param   Registry              $config      An optional argument to provide dependency injection for the application's config object. If the
     *                                             argument is a Registry object that object will become the application's config object,
     *                                             otherwise a default config object is created.
     * @param   DispatcherInterface   $dispatcher  An optional argument to provide dependency injection for the application's event dispatcher. If the
     *                                             argument is a DispatcherInterface object that object will become the application's event dispatcher,
     *                                             if it is null then the default event dispatcher will be created based on the application's
     *                                             loadDispatcher() method.
     * @param   Container             $container   Dependency injection container.
     * @param   Language              $language    The language object provisioned for the application.
     * @param   InputInterface|null   $input       An optional argument to provide dependency injection for the application's input object. If the
     *                                             argument is an InputInterface object that object will become the application's input object,
     *                                             otherwise a default input object is created.
     * @param   OutputInterface|null  $output      An optional argument to provide dependency injection for the application's output object. If the
     *                                             argument is an OutputInterface object that object will become the application's output object,
     *                                             otherwise a default output object is created.
     *
     * @since   4.0.0
     */
    public function __construct(
        Registry $config,
        DispatcherInterface $dispatcher,
        Container $container,
        Language $language,
        ?InputInterface $input = null,
        ?OutputInterface $output = null
    ) {
        // Close the application if it is not executed from the command line.
        if (!\defined('STDOUT') || !\defined('STDIN') || !isset($_SERVER['argv'])) {
            $this->close();
        }

        // Set up a Input object for Controllers etc to use
        $this->input    = new \Joomla\CMS\Input\Cli();
        $this->language = $language;

        parent::__construct($input, $output, $config);

        $this->setVersion(JVERSION);

        // Register the client name as cli
        $this->name = 'cli';

        $this->setContainer($container);
        $this->setDispatcher($dispatcher);

        // Set the execution datetime and timestamp;
        $this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
        $this->set('execution.timestamp', time());
        $this->set('execution.microtimestamp', microtime(true));

        // Set the current directory.
        $this->set('cwd', getcwd());

        // Set up the environment
        $this->input->set('format', 'cli');
    }

    /**
     * Magic method to access properties of the application.
     *
     * @param   string  $name  The name of the property.
     *
     * @return  mixed   A value if the property name is valid, null otherwise.
     *
     * @since       4.0.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              This is a B/C proxy for deprecated read accesses, use getInput() method instead
     *              Example:
     *              $app->getInput();
     */
    public function __get($name)
    {
        switch ($name) {
            case 'input':
                @trigger_error(
                    'Accessing the input property of the application is deprecated, use the getInput() method instead.',
                    E_USER_DEPRECATED
                );

                return $this->getInput();

            default:
                $trace = debug_backtrace();
                trigger_error(
                    \sprintf(
                        'Undefined property via __get(): %1$s in %2$s on line %3$s',
                        $name,
                        $trace[0]['file'],
                        $trace[0]['line']
                    ),
                    E_USER_NOTICE
                );
        }
    }

    /**
     * Method to run the application routines.
     *
     * @return  integer  The exit code for the application
     *
     * @since   4.0.0
     * @throws  \Throwable
     */
    protected function doExecute(): int
    {
        $exitCode = parent::doExecute();

        $style = new SymfonyStyle($this->getConsoleInput(), $this->getConsoleOutput());

        $methodMap = [
            self::MSG_ALERT     => 'error',
            self::MSG_CRITICAL  => 'caution',
            self::MSG_DEBUG     => 'comment',
            self::MSG_EMERGENCY => 'caution',
            self::MSG_ERROR     => 'error',
            self::MSG_INFO      => 'note',
            self::MSG_NOTICE    => 'note',
            self::MSG_WARNING   => 'warning',
        ];

        // Output any enqueued messages before the app exits
        foreach ($this->getMessageQueue() as $type => $messages) {
            $method = $methodMap[$type] ?? 'comment';

            $style->$method($messages);
        }

        return $exitCode;
    }

    /**
     * Execute the application.
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Throwable
     */
    public function execute()
    {
        // Load extension namespaces
        $this->createExtensionNamespaceMap();

        /**
         * Address issues with instantiating WebApplication descendants under CLI.
         *
         * IMPORTANT! This code must be always be executed **before** the first use of
         * PluginHelper::importPlugin(). Some plugins will attempt to register an MVCFactory for a
         * component in their service provider. This will in turn try to get the SiteRouter service
         * for the component which tries to get an instance of SiteApplication which will fail with
         * a RuntimeException if the populateHttpHost() method has not already executed.
         */
        $this->populateHttpHost();

        // Import CMS plugin groups to be able to subscribe to events
        PluginHelper::importPlugin('behaviour', null, true, $this->getDispatcher());
        PluginHelper::importPlugin('system', null, true, $this->getDispatcher());
        PluginHelper::importPlugin('console', null, true, $this->getDispatcher());

        parent::execute();
    }

    /**
     * Enqueue a system message.
     *
     * @param   string  $msg   The message to enqueue.
     * @param   string  $type  The message type.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function enqueueMessage($msg, $type = self::MSG_INFO)
    {
        if (!\array_key_exists($type, $this->messages)) {
            $this->messages[$type] = [];
        }

        $this->messages[$type][] = $msg;
    }

    /**
     * Gets the name of the current running application.
     *
     * @return  string  The name of the application.
     *
     * @since   4.0.0
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the commands which should be registered by default to the application.
     *
     * @return  \Joomla\Console\Command\AbstractCommand[]
     *
     * @since   4.0.0
     */
    protected function getDefaultCommands(): array
    {
        return array_merge(
            parent::getDefaultCommands(),
            [
                new Console\CleanCacheCommand(),
                new Console\CheckUpdatesCommand(),
                new Console\CheckJoomlaUpdatesCommand(),
                new Console\RemoveOldFilesCommand(),
                new Console\AddUserCommand($this->getDatabase()),
                new Console\AddUserToGroupCommand($this->getDatabase()),
                new Console\RemoveUserFromGroupCommand($this->getDatabase()),
                new Console\DeleteUserCommand($this->getDatabase()),
                new Console\ChangeUserPasswordCommand(),
                new Console\ListUserCommand($this->getDatabase()),
                new Console\SiteCreatePublicFolderCommand(),
            ]
        );
    }

    /**
     * Retrieve the application configuration object.
     *
     * @return  Registry
     *
     * @since   4.0.0
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Method to get the application input object.
     *
     * @return  Input
     *
     * @since   4.0.0
     */
    public function getInput(): Input
    {
        return $this->input;
    }

    /**
     * Method to get the application language object.
     *
     * @return  Language  The language object
     *
     * @since   4.0.0
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Get the system message queue.
     *
     * @return  array  The system message queue.
     *
     * @since   4.0.0
     */
    public function getMessageQueue()
    {
        return $this->messages;
    }

    /**
     * Method to get the application session object.
     *
     * @return  SessionInterface  The session object
     *
     * @since   4.0.0
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Check the client interface by name.
     *
     * @param   string  $identifier  String identifier for the application interface
     *
     * @return  boolean  True if this application is of the given type client interface.
     *
     * @since   4.0.0
     */
    public function isClient($identifier)
    {
        return $this->getName() === $identifier;
    }

    /**
     * Flag if the application instance is a CLI or web based application.
     *
     * Helper function, you should use the native PHP functions to detect if it is a CLI application.
     *
     * @return  boolean
     *
     * @since       4.0.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Will be removed without replacement. CLI will be handled by the joomla/console package instead
     */
    public function isCli()
    {
        return true;
    }

    /**
     * Sets the session for the application to use, if required.
     *
     * @param   SessionInterface  $session  A session object.
     *
     * @return  $this
     *
     * @since   4.0.0
     */
    public function setSession(SessionInterface $session): self
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Flush the media version to refresh versionable assets
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function flushAssets()
    {
        (new Version())->refreshMediaVersion();
    }

    /**
     * Get the long version string for the application.
     *
     * Overrides the parent method due to conflicting use of the getName method between the console application and
     * the CMS application interface.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function getLongVersion(): string
    {
        return \sprintf('Joomla! <info>%s</info> (debug: %s)', (new Version())->getShortVersion(), (\defined('JDEBUG') && JDEBUG ? 'Yes' : 'No'));
    }

    /**
     * Set the name of the application.
     *
     * @param   string  $name  The new application name.
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \RuntimeException because the application name cannot be changed
     */
    public function setName(string $name): void
    {
        throw new \RuntimeException('The console application name cannot be changed');
    }

    /**
     * Returns the application Router object.
     *
     * @param   string  $name     The name of the application.
     * @param   array   $options  An optional associative array of configuration settings.
     *
     * @return  Router
     *
     * @since      4.0.6
     *
     * @throws     \InvalidArgumentException
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Inject the router or load it from the dependency injection container
     *              Example: Factory::getContainer()->get(ApiRouter::class);
     */
    public static function getRouter($name = null, array $options = [])
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('A router name must be set in console application.');
        }

        $options['mode'] = Factory::getApplication()->get('sef');

        return Router::getInstance($name, $options);
    }

    /**
     * Populates the HTTP_HOST and REQUEST_URI from the URL provided in the --live-site parameter.
     *
     * If the URL provided is empty or invalid we will use the URL
     * https://joomla.invalid/set/by/console/application just so that the CLI application doesn't
     * crash when a WebApplication descendant is instantiated in it.
     *
     * This is a practical workaround for using any service depending on a WebApplication
     * descendant under CLI.
     *
     * Practical example: using a component's MVCFactory which instantiates the SiteRouter
     * service for that component which in turn relies on an instance of SiteApplication.
     *
     * @return  void
     * @since   4.2.1
     * @link    https://github.com/joomla/joomla-cms/issues/38518
     */
    protected function populateHttpHost()
    {
        // First check for the --live-site command line option.
        $input    = $this->getConsoleInput();
        $liveSite = '';

        if ($input->hasParameterOption(['--live-site', false])) {
            $liveSite = $input->getParameterOption(['--live-site'], '');
        }

        // Fallback to the $live_site global configuration option in configuration.php
        $liveSite = $liveSite ?: $this->get('live_site', 'https://joomla.invalid/set/by/console/application');

        /**
         * Try to use the live site URL we were given. If all else fails, fall back to
         * https://joomla.invalid/set/by/console/application.
         */
        try {
            $uri = Uri::getInstance($liveSite);
        } catch (\RuntimeException) {
            $uri = Uri::getInstance('https://joomla.invalid/set/by/console/application');
        }

        /**
         * Yes, this is icky but it is the only way to trick WebApplication into compliance.
         *
         * @see \Joomla\Application\AbstractWebApplication::detectRequestUri
         */
        $_SERVER['HTTP_HOST']   = $uri->toString(['host', 'port']);
        $_SERVER['REQUEST_URI'] = $uri->getPath();
        $_SERVER['HTTPS']       = $uri->getScheme() === 'https' ? 'on' : 'off';
    }

    /**
     * Builds the default input definition.
     *
     * @return  InputDefinition
     *
     * @since   4.2.1
     */
    protected function getDefaultInputDefinition(): InputDefinition
    {
        $inputDefinition = parent::getDefaultInputDefinition();
        $inputDefinition->addOption(
            new InputOption(
                '--live-site',
                null,
                InputOption::VALUE_OPTIONAL,
                'The URL to your site, e.g. https://www.example.com'
            )
        );

        return $inputDefinition;
    }

    /**
     * Gets a user state.
     *
     * @param   string  $key      The path of the state.
     * @param   mixed   $default  Optional default value, returned if the internal value is null.
     *
     * @return  mixed  The user state or null.
     *
     * @since   4.4.0
     */
    public function getUserState($key, $default = null)
    {
        $registry = $this->getSession()->get('registry');

        if ($registry !== null) {
            return $registry->get($key, $default);
        }

        return $default;
    }

    /**
     * Gets the value of a user state variable.
     *
     * @param   string  $key      The key of the user state variable.
     * @param   string  $request  The name of the variable passed in a request.
     * @param   string  $default  The default value for the variable if not found. Optional.
     * @param   string  $type     Filter for the variable, for valid values see {@link InputFilter::clean()}. Optional.
     *
     * @return  mixed  The request user state.
     *
     * @since   4.4.0
     */
    public function getUserStateFromRequest($key, $request, $default = null, $type = 'none')
    {
        $cur_state = $this->getUserState($key, $default);
        $new_state = $this->input->get($request, null, $type);

        if ($new_state === null) {
            return $cur_state;
        }

        // Save the new value only if it was set in this request.
        $this->setUserState($key, $new_state);

        return $new_state;
    }
}
