<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application;

use Joomla\Application\AbstractApplication;
use Joomla\CMS\Application\CLI\CliInput;
use Joomla\CMS\Application\CLI\CliOutput;
use Joomla\CMS\Application\CLI\Output\Stdout;
use Joomla\CMS\Extension\ExtensionManagerTrait;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Session\SessionInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for a Joomla! command line application.
 *
 * @since       2.5.0
 *
 * @deprecated  4.0 will be removed in 6.0
 *              Use the ConsoleApplication instead
 */
abstract class CliApplication extends AbstractApplication implements DispatcherAwareInterface, CMSApplicationInterface
{
    use DispatcherAwareTrait;
    use EventAware;
    use IdentityAware;
    use ContainerAwareTrait;
    use ExtensionManagerTrait;
    use ExtensionNamespaceMapper;

    /**
     * Output object
     *
     * @var    CliOutput
     * @since  4.0.0
     */
    protected $output;

    /**
     * The input.
     *
     * @var    \Joomla\Input\Input
     * @since  4.0.0
     */
    protected $input = null;

    /**
     * CLI Input object
     *
     * @var    CliInput
     * @since  4.0.0
     */
    protected $cliInput;

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
    protected $messages = [];

    /**
     * The application instance.
     *
     * @var    CliApplication
     * @since  1.7.0
     */
    protected static $instance;

    /**
     * Class constructor.
     *
     * @param   Input                $input       An optional argument to provide dependency injection for the application's
     *                                            input object.  If the argument is a JInputCli object that object will become
     *                                            the application's input object, otherwise a default input object is created.
     * @param   Registry             $config      An optional argument to provide dependency injection for the application's
     *                                            config object.  If the argument is a Registry object that object will become
     *                                            the application's config object, otherwise a default config object is created.
     * @param   CliOutput            $output      The output handler.
     * @param   CliInput             $cliInput    The CLI input handler.
     * @param   DispatcherInterface  $dispatcher  An optional argument to provide dependency injection for the application's
     *                                            event dispatcher.  If the argument is a DispatcherInterface object that object will become
     *                                            the application's event dispatcher, if it is null then the default event dispatcher
     *                                            will be created based on the application's loadDispatcher() method.
     * @param   Container            $container   Dependency injection container.
     *
     * @since   1.7.0
     */
    public function __construct(
        Input $input = null,
        Registry $config = null,
        CliOutput $output = null,
        CliInput $cliInput = null,
        DispatcherInterface $dispatcher = null,
        Container $container = null
    ) {
        // Close the application if we are not executed from the command line.
        if (!\defined('STDOUT') || !\defined('STDIN') || !isset($_SERVER['argv'])) {
            $this->close();
        }

        $container = $container ?: Factory::getContainer();
        $this->setContainer($container);
        $this->setDispatcher($dispatcher ?: $container->get(\Joomla\Event\DispatcherInterface::class));

        if (!$container->has('session')) {
            $container->alias('session', 'session.cli')
                ->alias('JSession', 'session.cli')
                ->alias(\Joomla\CMS\Session\Session::class, 'session.cli')
                ->alias(\Joomla\Session\Session::class, 'session.cli')
                ->alias(\Joomla\Session\SessionInterface::class, 'session.cli');
        }

        $this->input    = new \Joomla\CMS\Input\Cli();
        $this->language = Factory::getLanguage();
        $this->output   = $output ?: new Stdout();
        $this->cliInput = $cliInput ?: new CliInput();

        parent::__construct($config);

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
     *              This is a B/C proxy for deprecated read accesses
     *              Example: Factory::getApplication()->getInput();
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
                    sprintf(
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
     * Returns a reference to the global CliApplication object, only creating it if it doesn't already exist.
     *
     * This method must be invoked as: $cli = CliApplication::getInstance();
     *
     * @param   string  $name  The name (optional) of the Application Cli class to instantiate.
     *
     * @return  CliApplication
     *
     * @since       1.7.0
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Load the app through the container or via the Factory
     *              Example: Factory::getContainer()->get(CliApplication::class)
     *
     * @throws  \RuntimeException
     */
    public static function getInstance($name = null)
    {
        // Only create the object if it doesn't exist.
        if (empty(static::$instance)) {
            if (!class_exists($name)) {
                throw new \RuntimeException(sprintf('Unable to load application: %s', $name), 500);
            }

            static::$instance = new $name();
        }

        return static::$instance;
    }

    /**
     * Execute the application.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function execute()
    {
        $this->createExtensionNamespaceMap();

        // Trigger the onBeforeExecute event
        $this->triggerEvent('onBeforeExecute');

        // Perform application routines.
        $this->doExecute();

        // Trigger the onAfterExecute event.
        $this->triggerEvent('onAfterExecute');
    }

    /**
     * Get an output object.
     *
     * @return  CliOutput
     *
     * @since   4.0.0
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * Get a CLI input object.
     *
     * @return  CliInput
     *
     * @since   4.0.0
     */
    public function getCliInput()
    {
        return $this->cliInput;
    }

    /**
     * Write a string to standard output.
     *
     * @param   string   $text  The text to display.
     * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
     *
     * @return  $this
     *
     * @since   4.0.0
     */
    public function out($text = '', $nl = true)
    {
        $this->getOutput()->out($text, $nl);

        return $this;
    }

    /**
     * Get a value from standard input.
     *
     * @return  string  The input string from standard input.
     *
     * @codeCoverageIgnore
     * @since   4.0.0
     */
    public function in()
    {
        return $this->getCliInput()->in();
    }

    /**
     * Set an output object.
     *
     * @param   CliOutput  $output  CliOutput object
     *
     * @return  $this
     *
     * @since   3.3
     */
    public function setOutput(CliOutput $output)
    {
        $this->output = $output;

        return $this;
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
        return $identifier === 'cli';
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
        return $this->container->get(SessionInterface::class);
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
     * Flag if the application instance is a CLI or web based application.
     *
     * Helper function, you should use the native PHP functions to detect if it is a CLI application.
     *
     * @return  boolean
     *
     * @since       4.0.0
     * @deprecated  4.0 will be removed in 6.0
     *              Will be removed without replacements
     */
    public function isCli()
    {
        return true;
    }
}
