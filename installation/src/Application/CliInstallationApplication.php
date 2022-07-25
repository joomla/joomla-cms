<?php

/**
 * @package         Joomla.Installation
 * @subpackage      Application
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Application;

use Joomla\Application\Web\WebClient;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Installation\Console\InstallCommand;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\Version;
use Joomla\Console\Application;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\Registry\Registry;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Joomla! Installation Application class.
 *
 * @since  __DEPLOY_VERSION__
 */
final class CliInstallationApplication extends Application
{
    use \Joomla\CMS\Application\ExtensionNamespaceMapper;

    /**
     * @var MVCFactory
     * @since __DEPLOY_VERSION__
     */
    protected $MVCFactory;

    protected $session;

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
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(
        ?InputInterface $input = null,
        ?OutputInterface $output = null,
        ?Registry $config = null
    ) {
        // Register the application name.
        $this->setName('Joomla CLI installation');
        $version = new Version();
        $this->setVersion($version->getShortVersion());

        // Register the client ID.
        $this->clientId = 2;

        // Run the parent constructor.
        parent::__construct($input, $output, $config);

        // Store the debug value to config based on the JDEBUG flag.
        $this->config->set('debug', JDEBUG);

        \define('JPATH_COMPONENT', JPATH_BASE);
        \define('JPATH_COMPONENT_SITE', JPATH_SITE);
        \define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR);

        // Register the config to Factory.
        Factory::$config   = $this->config;
        Factory::$language = Language::getInstance('en-GB');
    }

    /**
     * Method to display errors in language parsing.
     *
     * @return  string  Language debug output.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function debugLanguage()
    {
        if ($this->getDocument()->getType() != 'html') {
            return '';
        }

        $lang   = Factory::getLanguage();
        $output = '<h4>' . Text::_('JDEBUG_LANGUAGE_FILES_IN_ERROR') . '</h4>';

        $errorfiles = $lang->getErrorFiles();

        if (count($errorfiles)) {
            $output .= '<ul>';

            foreach ($errorfiles as $error) {
                $output .= "<li>$error</li>";
            }

            $output .= '</ul>';
        } else {
            $output .= '<pre>' . Text::_('JNONE') . '</pre>';
        }

        $output  .= '<h4>' . Text::_('JDEBUG_LANGUAGE_UNTRANSLATED_STRING') . '</h4>';
        $output  .= '<pre>';
        $orphans = $lang->getOrphans();

        if (count($orphans)) {
            ksort($orphans, SORT_STRING);

            $guesses = array();

            foreach ($orphans as $key => $occurrence) {
                $guess = str_replace('_', ' ', $key);

                $parts = explode(' ', $guess);

                if (count($parts) > 1) {
                    array_shift($parts);
                    $guess = implode(' ', $parts);
                }

                $guess = trim($guess);

                $key = strtoupper(trim($key));
                $key = preg_replace('#\s+#', '_', $key);
                $key = preg_replace('#\W#', '', $key);

                // Prepare the text.
                $guesses[] = $key . '="' . $guess . '"';
            }

            $output .= implode("\n", $guesses);
        } else {
            $output .= '<pre>' . Text::_('JNONE') . '</pre>';
        }

        $output .= '</pre>';

        return $output;
    }

    /**
     * Dispatch the application.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dispatch()
    {
        // Load the document to the API.
        $this->loadDocument();

        // Set up the params
        $document = $this->getDocument();

        // Register the document object with Factory.
        Factory::$document = $document;

        // Define component path.
        \define('JPATH_COMPONENT', JPATH_BASE);
        \define('JPATH_COMPONENT_SITE', JPATH_SITE);
        \define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR);

        // Execute the task.
        ob_start();
        $this->executeController();
        $contents = ob_get_clean();

        // If debug language is set, append its output to the contents.
        if ($this->config->get('debug_lang')) {
            $contents .= $this->debugLanguage();
        }

        // Set the content on the document
        $this->getDocument()->setBuffer($contents, 'component');

        // Set the document title
        $document->setTitle(Text::_('INSTL_PAGE_TITLE'));
    }

    /**
     * Enqueue a system message.
     *
     * @param   string  $msg   The message to enqueue.
     * @param   string  $type  The message type. Default is message.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function enqueueMessage($msg, $type = 'info')
    {
        throw new \Exception($msg);
    }

    /**
     * Executed a controller from the input task.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function executeController()
    {
        $task = $this->input->getCmd('task', '');

        // The name of the controller
        $controllerName = 'display';

        // Parse task in format controller.task
        if ($task !== '') {
            list($controllerName, $task) = explode('.', $task, 2);
        }

        // Compile the class name
        $class = 'Joomla\\CMS\\Installation\\Controller\\' . ucfirst($controllerName) . 'Controller';

        // Create the instance
        $controller = new $class([], new MVCFactory('Joomla\\CMS', $this), $this, $this->input);

        // Execute the task
        $controller->execute($task);
    }

    /**
     * Retrieve the application configuration object.
     *
     * @return  Registry
     *
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
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
     * This is a dummy method, forcing to en-GB on CLI installation
     *
     * @return  boolean  False on failure, array on success.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getLocalise()
    {
        return false;
    }

    /**
     * Returns the installed language files in the administrative and frontend area.
     *
     * @param   DatabaseInterface|null  $db  Database driver.
     *
     * @return  array  Array with installed language packs in admin and site area.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getLocaliseAdmin(DatabaseInterface $db = null)
    {
        $langfiles = array();

        // If db connection, fetch them from the database.
        if ($db) {
            foreach (LanguageHelper::getInstalledLanguages() as $clientId => $language) {
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
     * Get the MVC factory for the installation application
     *
     * @return  MVCFactory  MVC Factory of the installation application
     *
     * @since __DEPLOY_VERSION__
     */
    public function getMVCFactory()
    {
        if (!$this->MVCFactory) {
            $this->MVCFactory = new MVCFactory('Joomla\\CMS', $this);
        }

        return $this->MVCFactory;
    }

    /**
     * We need to imitate the session object
     *
     * @return  Registry  Object imitating the session object
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getSession()
    {
        if (!$this->session) {
            $this->session = new Registry();
        }

        return $this->session;
    }

    /**
     * Check the client interface by name.
     *
     * @param   string  $identifier  String identifier for the application interface
     *
     * @return  boolean  True if this application is of the given type client interface.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function isClient($identifier)
    {
        return 'cli_installation' === $identifier;
    }

    /**
     * Set configuration values.
     *
     * @param   array   $vars       Array of configuration values
     * @param   string  $namespace  The namespace
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function setCfg(array $vars = array(), $namespace = 'config')
    {
        $this->config->loadArray($vars, $namespace);
    }
}
