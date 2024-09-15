<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Application
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Application;

use Joomla\Application\Web\WebClient;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\EventAware;
use Joomla\CMS\Application\ExtensionNamespaceMapper;
use Joomla\CMS\Application\IdentityAware;
use Joomla\CMS\Extension\ExtensionManagerTrait;
use Joomla\CMS\Factory;
use Joomla\CMS\Installation\Console\InstallCommand;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\Version;
use Joomla\Console\Application;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Filesystem\Folder;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Session\SessionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Joomla! Installation Application class.
 *
 * @since  4.3.0
 */
final class CliInstallationApplication extends Application implements CMSApplicationInterface
{
    use ExtensionNamespaceMapper;
    use IdentityAware;
    use ContainerAwareTrait;
    use EventAware;
    use ExtensionManagerTrait;

    /**
     * The application input object.
     *
     * @var    Input
     * @since  4.3.0
     */
    public $input;

    /**
     * The application language object.
     *
     * @var    Language
     * @since  4.3.0
     */
    protected $language;

    /**
     * @var MVCFactory
     * @since 4.3.0
     */
    protected $mvcFactory;

    /**
     * Object to imitate the session object
     *
     * @var Registry
     * @since 4.3.0
     */
    protected $session;

    /**
     * The client application Id
     *
     * @var Integer
     * @since 5.0.2
     */
    protected $clientId;

    /**
     * Class constructor.
     *
     * @param   Input|null      $input      An optional argument to provide dependency injection for the application's input
     *                                      object.  If the argument is a JInput object that object will become the
     *                                      application's input object, otherwise a default input object is created.
     * @param   Registry|null   $config     An optional argument to provide dependency injection for the application's
     *                                      config object.  If the argument is a Registry object that object will become
     *                                      the application's config object, otherwise a default config object is created.
     * @param   WebClient|null  $client     An optional argument to provide dependency injection for the application's
     *                                      client object.  If the argument is a WebClient object that object will become the
     *                                      application's client object, otherwise a default client object is created.
     * @param   Container|null  $container  Dependency injection container.
     *
     * @since   4.3.0
     */
    public function __construct(
        ?InputInterface $input = null,
        ?OutputInterface $output = null,
        ?Registry $config = null,
        ?Language $language = null
    ) {
        // Register the application name.
        $this->setName('Joomla CLI installation');
        $version = new Version();
        $this->setVersion($version->getShortVersion());

        // Register the client ID.
        $this->clientId = 2;
        $this->language = $language;

        // Run the parent constructor.
        parent::__construct($input, $output, $config);

        // Store the debug value to config based on the JDEBUG flag.
        $this->config->set('debug', JDEBUG);

        \define('JPATH_COMPONENT', JPATH_BASE);
        \define('JPATH_COMPONENT_SITE', JPATH_SITE);
        \define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR);

        // Register the config to Factory.
        Factory::$config   = $this->config;
        Factory::$language = $language;
    }

    /**
     * Enqueue a system message.
     *
     * @param   string  $msg   The message to enqueue.
     * @param   string  $type  The message type. Default is message.
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function enqueueMessage($msg, $type = 'info')
    {
        throw new \Exception($msg);
    }

    /**
     * Retrieve the application configuration object.
     *
     * @return  Registry
     *
     * @since   4.3.0
     */
    public function getConfig()
    {
        return new Registry();
    }

    /**
     * Get the commands which should be registered by default to the application.
     *
     * @return  \Joomla\Console\Command\AbstractCommand[]
     *
     * @since   4.3.0
     */
    protected function getDefaultCommands(): array
    {
        return array_merge(
            parent::getDefaultCommands(),
            [
                new InstallCommand(),
            ]
        );
    }

    /**
     * Method to get the application input object.
     *
     * @return  \Joomla\Input\Input
     *
     * @since   4.0.0
     */
    public function getInput(): Input
    {
        return new Input();
    }

    /**
     * Method to get the application language object.
     *
     * @return  Language  The language object
     *
     * @since   4.3.0
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * This is a dummy method, forcing to en-GB on CLI installation
     *
     * @return  boolean  False on failure, array on success.
     *
     * @since   4.3.0
     */
    public function getLocalise()
    {
        return false;
    }

    /**
     * Returns the installed language files in the administrative and frontend area.
     *
     * @param   ?DatabaseInterface  $db  Database driver.
     *
     * @return  array  Array with installed language packs in admin and site area.
     *
     * @since   4.3.0
     */
    public function getLocaliseAdmin(?DatabaseInterface $db = null)
    {
        $langfiles = [];

        // If db connection, fetch them from the database.
        if ($db) {
            foreach (LanguageHelper::getInstalledLanguages(null, null, null, null, null, null, $db) as $clientId => $language) {
                $clientName = $clientId === 0 ? 'site' : 'admin';

                foreach ($language as $languageCode => $lang) {
                    $langfiles[$clientName][] = $lang->element;
                }
            }
        } else {
            // Read the folder names in the site and admin area.
            $langfiles['site']  = Folder::folders(LanguageHelper::getLanguagePath(JPATH_SITE));
            $langfiles['admin'] = Folder::folders(LanguageHelper::getLanguagePath(JPATH_ADMINISTRATOR));
        }

        return $langfiles;
    }

    /**
     * Get the system message queue. This is a mock
     * to fulfill the interface requirements and is not functional.
     *
     * @return  array  The system message queue.
     *
     * @since   4.3.0
     */
    public function getMessageQueue()
    {
        return [];
    }

    /**
     * Get the MVC factory for the installation application
     *
     * @return  MVCFactory  MVC Factory of the installation application
     *
     * @since 4.3.0
     */
    public function getMVCFactory()
    {
        if (!$this->mvcFactory) {
            $this->mvcFactory = new MVCFactory('Joomla\\CMS');
        }

        return $this->mvcFactory;
    }

    /**
     * We need to imitate the session object
     *
     * @return  SessionInterface  Object imitating the session object
     *
     * @since  4.3.0
     */
    public function getSession()
    {
        return $this->session;
    }

    /**
     * Sets the session for the application to use, if required.
     *
     * @param   SessionInterface  $session  A session object.
     *
     * @return  $this
     *
     * @since   4.3.0
     */
    public function setSession(SessionInterface $session): self
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Check the client interface by name.
     *
     * @param   string  $identifier  String identifier for the application interface
     *
     * @return  boolean  True if this application is of the given type client interface.
     *
     * @since   4.3.0
     */
    public function isClient($identifier)
    {
        return 'cli_installation' === $identifier;
    }

    /**
     * Flag if the application instance is a CLI or web based application.
     *
     * Helper function, you should use the native PHP functions to detect if it is a CLI application.
     *
     * @return  boolean
     *
     * @since       4.3.0
     *
     * @deprecated   4.3 will be removed in 5.0
     *               Use $app->isClient('cli_installation') instead
     */
    public function isCli()
    {
        return $this->isClient('cli_installation');
    }
}
