<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application;

use Joomla\Application\SessionAwareWebApplicationTrait;
use Joomla\Application\Web\WebClient;
use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Event\Application\AfterCompressEvent;
use Joomla\CMS\Event\Application\AfterInitialiseEvent;
use Joomla\CMS\Event\Application\AfterRenderEvent;
use Joomla\CMS\Event\Application\AfterRespondEvent;
use Joomla\CMS\Event\Application\AfterRouteEvent;
use Joomla\CMS\Event\Application\BeforeRenderEvent;
use Joomla\CMS\Event\Application\BeforeRespondEvent;
use Joomla\CMS\Event\ErrorEvent;
use Joomla\CMS\Event\User\AfterLoginEvent;
use Joomla\CMS\Event\User\AfterLogoutEvent;
use Joomla\CMS\Event\User\AuthorisationFailureEvent;
use Joomla\CMS\Event\User\LoginEvent;
use Joomla\CMS\Event\User\LoginFailureEvent;
use Joomla\CMS\Event\User\LogoutEvent;
use Joomla\CMS\Event\User\LogoutFailureEvent;
use Joomla\CMS\Exception\ExceptionHandler;
use Joomla\CMS\Extension\ExtensionManagerTrait;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\LanguageFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Menu\MenuFactoryInterface;
use Joomla\CMS\Pathway\Pathway;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Session\MetadataManager;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\DI\Container;
use Joomla\DI\ContainerAwareInterface;
use Joomla\DI\ContainerAwareTrait;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! CMS Application class
 *
 * @since  3.2
 */
abstract class CMSApplication extends WebApplication implements ContainerAwareInterface, CMSWebApplicationInterface
{
    use ContainerAwareTrait;
    use ExtensionManagerTrait;
    use ExtensionNamespaceMapper;
    use SessionAwareWebApplicationTrait;

    /**
     * Array of options for the \JDocument object
     *
     * @var    array
     * @since  3.2
     */
    protected $docOptions = [];

    /**
     * Application instances container.
     *
     * @var    CmsApplication[]
     * @since  3.2
     */
    protected static $instances = [];

    /**
     * The scope of the application.
     *
     * @var    string
     * @since  3.2
     */
    public $scope = null;

    /**
     * The client identifier.
     *
     * @var    integer
     * @since  4.0.0
     */
    protected $clientId = null;

    /**
     * The application message queue.
     *
     * @var    array
     * @since  4.0.0
     */
    protected $messageQueue = [];

    /**
     * The name of the application.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $name = null;

    /**
     * The profiler instance
     *
     * @var    Profiler
     * @since  3.2
     */
    protected $profiler = null;

    /**
     * Currently active template
     *
     * @var    object
     * @since  3.2
     */
    protected $template = null;

    /**
     * The pathway object
     *
     * @var    Pathway
     * @since  4.0.0
     */
    protected $pathway = null;

    /**
     * The authentication plugin type
     *
     * @var   string
     * @since  4.0.0
     */
    protected $authenticationPluginType = 'authentication';

    /**
     * Menu instances container.
     *
     * @var    AbstractMenu[]
     * @since  4.2.0
     */
    protected $menus = [];

    /**
     * The menu factory
     *
     * @var   MenuFactoryInterface
     *
     * @since  4.2.0
     */
    private $menuFactory;

    /**
     * Class constructor.
     *
     * @param   ?Input      $input      An optional argument to provide dependency injection for the application's input
     *                                  object.  If the argument is a JInput object that object will become the
     *                                  application's input object, otherwise a default input object is created.
     * @param   ?Registry   $config     An optional argument to provide dependency injection for the application's config
     *                                  object.  If the argument is a Registry object that object will become the
     *                                  application's config object, otherwise a default config object is created.
     * @param   ?WebClient  $client     An optional argument to provide dependency injection for the application's client
     *                                  object.  If the argument is a WebClient object that object will become the
     *                                  application's client object, otherwise a default client object is created.
     * @param   ?Container  $container  Dependency injection container.
     *
     * @since   3.2
     */
    public function __construct(?Input $input = null, ?Registry $config = null, ?WebClient $client = null, ?Container $container = null)
    {
        $container = $container ?: new Container();
        $this->setContainer($container);

        parent::__construct($input, $config, $client);

        // If JDEBUG is defined, load the profiler instance
        if (\defined('JDEBUG') && JDEBUG) {
            $this->profiler = Profiler::getInstance('Application');
        }

        // Enable sessions by default.
        if ($this->config->get('session') === null) {
            $this->config->set('session', true);
        }

        // Set the session default name.
        if ($this->config->get('session_name') === null) {
            $this->config->set('session_name', $this->getName());
        }
    }

    /**
     * Checks the user session.
     *
     * If the session record doesn't exist, initialise it.
     * If session is new, create session variables
     *
     * @return  void
     *
     * @since   3.2
     * @throws  \RuntimeException
     */
    public function checkSession()
    {
        $this->getContainer()->get(MetadataManager::class)->createOrUpdateRecord($this->getSession(), $this->getIdentity());
    }

    /**
     * Enqueue a system message.
     *
     * @param   string  $msg   The message to enqueue.
     * @param   string  $type  The message type. Default is message.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function enqueueMessage($msg, $type = self::MSG_INFO)
    {
        // Don't add empty messages.
        if ($msg === null || trim($msg) === '') {
            return;
        }

        $inputFilter = InputFilter::getInstance(
            [],
            [],
            InputFilter::ONLY_BLOCK_DEFINED_TAGS,
            InputFilter::ONLY_BLOCK_DEFINED_ATTRIBUTES
        );

        // Build the message array and apply the HTML InputFilter with the default blacklist to the message
        $message = [
            'message' => $inputFilter->clean($msg, 'html'),
            'type'    => $inputFilter->clean(strtolower($type), 'cmd'),
        ];

        // Get the messages of the session and add the new message if it is not already in the queue.
        if (!\in_array($message, $this->getMessageQueue())) {
            // Enqueue the message.
            $this->messageQueue[] = $message;
        }
    }

    /**
     * Ensure several core system input variables are not arrays.
     *
     * @return  void
     *
     * @since   3.9
     */
    private function sanityCheckSystemVariables()
    {
        $input = $this->input;

        // Get invalid input variables
        $invalidInputVariables = array_filter(
            ['option', 'view', 'format', 'lang', 'Itemid', 'template', 'templateStyle', 'task'],
            function ($systemVariable) use ($input) {
                return $input->exists($systemVariable) && \is_array($input->getRaw($systemVariable));
            }
        );

        // Unset invalid system variables
        foreach ($invalidInputVariables as $systemVariable) {
            $input->set($systemVariable, null);
        }

        // Stop when there are invalid variables
        if ($invalidInputVariables) {
            throw new \RuntimeException('Invalid input, aborting application.');
        }
    }

    /**
     * Execute the application.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function execute()
    {
        try {
            $this->sanityCheckSystemVariables();
            $this->setupLogging();
            $this->createExtensionNamespaceMap();

            // Perform application routines.
            $this->doExecute();

            // If we have an application document object, render it.
            if ($this->document instanceof \Joomla\CMS\Document\Document) {
                // Render the application output.
                $this->render();
            }

            // If gzip compression is enabled in configuration and the server is compliant, compress the output.
            if ($this->get('gzip') && !\ini_get('zlib.output_compression') && \ini_get('output_handler') !== 'ob_gzhandler') {
                $this->compress();

                // Trigger the onAfterCompress event.
                $this->dispatchEvent(
                    'onAfterCompress',
                    new AfterCompressEvent('onAfterCompress', ['subject' => $this])
                );
            }
        } catch (\Throwable $throwable) {
            $event = new ErrorEvent(
                'onError',
                [
                    'subject'     => $throwable,
                    'application' => $this,
                ]
            );

            // Trigger the onError event.
            $this->dispatchEvent('onError', $event);

            ExceptionHandler::handleException($event->getError());
        }

        // Trigger the onBeforeRespond event.
        $this->dispatchEvent(
            'onBeforeRespond',
            new BeforeRespondEvent('onBeforeRespond', ['subject' => $this])
        );

        // Send the application response.
        $this->respond();

        // Trigger the onAfterRespond event.
        $this->dispatchEvent(
            'onAfterRespond',
            new AfterRespondEvent('onAfterRespond', ['subject' => $this])
        );
    }

    /**
     * Check if the user is required to reset their password.
     *
     * If the user is required to reset their password will be redirected to the page that manage the password reset.
     *
     * @param   string  $option  The option that manage the password reset
     * @param   string  $view    The view that manage the password reset
     * @param   string  $layout  The layout of the view that manage the password reset
     * @param   string  $tasks   Permitted tasks
     *
     * @return  void
     *
     * @throws  \Exception
     * @deprecated  5.2.3  will be removed in 7.0
     *              Use $this->checkUserRequiresReset() instead.
     */
    protected function checkUserRequireReset($option, $view, $layout, $tasks)
    {
        $name = $this->getName();
        $urls = [];

        if ($this->get($name . '_reset_password_override', 0)) {
            $tasks = $this->get($name . '_reset_password_tasks', '');
        }

        // Check task
        if (!empty($tasks)) {
            $tasks = explode(',', $tasks);

            foreach ($tasks as $task) {
                [$option, $t] = explode('/', $task);
                $urls[]       = ['option' => $option, 'task' => $t];
            }
        }

        $this->checkUserRequiresReset($option, $view, $layout, $urls);
    }

    /**
     * Check if the user is required to reset their password.
     *
     * If the user is required to reset their password will be redirected to the page that manage the password reset.
     *
     * @param   string  $option  The option that manage the password reset
     * @param   string  $view    The view that manage the password reset
     * @param   string  $layout  The layout of the view that manage the password reset
     * @param   array   $urls    Multi-dimensional array of permitted urls. Ex: [['option' => 'com_users', 'view' => 'profile'],...]
     *
     * @return  void
     *
     * @throws  \Exception
     */
    protected function checkUserRequiresReset($option, $view, $layout, $urls = [])
    {
        // Password reset is not required for the user, no need to check it further
        if (!$this->getIdentity()->requireReset) {
            return;
        }

        /*
         * By default user profile edit page is used.
         * That page allows you to change more than just the password and might not be the desired behavior.
         * This allows a developer to override the page that manage the password reset.
         * (can be configured using the file: configuration.php, or if extended, through the global configuration form)
         */
        $name = $this->getName();

        if ($this->get($name . '_reset_password_override', 0)) {
            $option = $this->get($name . '_reset_password_option', '');
            $view   = $this->get($name . '_reset_password_view', '');
            $layout = $this->get($name . '_reset_password_layout', '');
            $urls   = $this->get($name . '_reset_password_urls', $urls);
        }

        /**
         * The page which manage password reset always need to accessible, so if the current page
         * is managing password reset page, no need to check it further
         */
        if (
            $this->input->getCmd('option', '') === $option
            && $this->input->getCmd('view', '') === $view
            && $this->input->getCmd('layout', '') == $layout
        ) {
            return;
        }

        // If the current URL matches an entry in $urls, we do not redirect
        foreach ($urls as $url) {
            $match = true;

            foreach ($url as $key => $value) {
                if ($this->input->getCmd($key) !== $value) {
                    /**
                     * The current URL does not meet this entry, get out of this loop
                     * and check next entry
                     */
                    $match = false;
                    break;
                }
            }

            // The current URL meet the entry, no redirect is needed, just return early
            if ($match) {
                return;
            }
        }

        // Redirect to the profile edit page
        $this->enqueueMessage(Text::_('JGLOBAL_PASSWORD_RESET_REQUIRED'), 'notice');

        $url = Route::_('index.php?option=' . $option . '&view=' . $view . '&layout=' . $layout, false);

        // In the administrator we need a different URL
        if ($this->isClient('administrator')) {
            $user = $this->getIdentity();
            $url  = Route::_(
                'index.php?option=' . $option . '&task=' . $view . '.' . $layout . '&id=' . $user->id,
                false
            );
        }

        $this->redirect($url);
    }

    /**
     * Gets a configuration value.
     *
     * @param   string  $varname  The name of the value to get.
     * @param   string  $default  Default value to return
     *
     * @return  mixed  The user state.
     *
     * @since   3.2
     *
     * @deprecated  3.2 will be removed in 6.0
     *              Use get() instead
     *              Example: Factory::getApplication()->get($varname, $default);
     */
    public function getCfg($varname, $default = null)
    {
        try {
            Log::add(
                \sprintf('%s() is deprecated and will be removed in 6.0. Use Factory->getApplication()->get() instead.', __METHOD__),
                Log::WARNING,
                'deprecated'
            );
        } catch (\RuntimeException) {
            // Informational log only
        }

        return $this->get($varname, $default);
    }

    /**
     * Gets the client id of the current running application.
     *
     * @return  integer  A client identifier.
     *
     * @since   3.2
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Returns a reference to the global CmsApplication object, only creating it if it doesn't already exist.
     *
     * This method must be invoked as: $web = CmsApplication::getInstance();
     *
     * @param   string      $name       The name (optional) of the CmsApplication class to instantiate.
     * @param   string      $prefix     The class name prefix of the object.
     * @param   ?Container  $container  An optional dependency injection container to inject into the application.
     *
     * @return  CmsApplication
     *
     * @since       3.2
     * @throws      \RuntimeException
     * @deprecated  4.0 will be removed in 6.0
     *              Use the application service from the DI container instead
     *              Example: Factory::getContainer()->get($name);
     */
    public static function getInstance($name = null, $prefix = '\JApplication', ?Container $container = null)
    {
        if (empty(static::$instances[$name])) {
            // Create a CmsApplication object.
            $classname = $prefix . ucfirst($name);

            if (!$container) {
                $container = Factory::getContainer();
            }

            if ($container->has($classname)) {
                static::$instances[$name] = $container->get($classname);
            } elseif (class_exists($classname)) {
                // @todo This creates an implicit hard requirement on the ApplicationCms constructor
                static::$instances[$name] = new $classname(null, null, null, $container);
            } else {
                throw new \RuntimeException(Text::sprintf('JLIB_APPLICATION_ERROR_APPLICATION_LOAD', $name), 500);
            }

            static::$instances[$name]->loadIdentity(Factory::getUser());
        }

        return static::$instances[$name];
    }

    /**
     * Returns the application \JMenu object.
     *
     * @param   string  $name     The name of the application/client.
     * @param   array   $options  An optional associative array of configuration settings.
     *
     * @return  AbstractMenu
     *
     * @since   3.2
     */
    public function getMenu($name = null, $options = [])
    {
        if (!isset($name)) {
            $name = $this->getName();
        }

        // Inject this application object into the \JMenu tree if one isn't already specified
        if (!isset($options['app'])) {
            $options['app'] = $this;
        }

        if (\array_key_exists($name, $this->menus)) {
            return $this->menus[$name];
        }

        if ($this->menuFactory === null) {
            @trigger_error('Menu factory must be set in 5.0', E_USER_DEPRECATED);
            $this->menuFactory = $this->getContainer()->get(MenuFactoryInterface::class);
        }

        $this->menus[$name] = $this->menuFactory->createMenu($name, $options);

        // Make sure the abstract menu has the instance too, is needed for BC and will be removed with version 5
        AbstractMenu::$instances[$name] = $this->menus[$name];

        return $this->menus[$name];
    }

    /**
     * Get the system message queue.
     *
     * @param   boolean  $clear  Clear the messages currently attached to the application object
     *
     * @return  array  The system message queue.
     *
     * @since   3.2
     */
    public function getMessageQueue($clear = false)
    {
        // For empty queue, if messages exists in the session, enqueue them.
        if (!\count($this->messageQueue)) {
            $sessionQueue = $this->getSession()->get('application.queue', []);

            if ($sessionQueue) {
                $this->messageQueue = $sessionQueue;
                $this->getSession()->set('application.queue', []);
            }
        }

        $messageQueue = $this->messageQueue;

        if ($clear) {
            $this->messageQueue = [];
        }

        return $messageQueue;
    }

    /**
     * Gets the name of the current running application.
     *
     * @return  string  The name of the application.
     *
     * @since   3.2
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the application Pathway object.
     *
     * @return  Pathway
     *
     * @since   3.2
     */
    public function getPathway()
    {
        if (!$this->pathway) {
            $resourceName = ucfirst($this->getName()) . 'Pathway';

            if (!$this->getContainer()->has($resourceName)) {
                throw new \RuntimeException(
                    Text::sprintf('JLIB_APPLICATION_ERROR_PATHWAY_LOAD', $this->getName()),
                    500
                );
            }

            $this->pathway = $this->getContainer()->get($resourceName);
        }

        return $this->pathway;
    }

    /**
     * Returns the application Router object.
     *
     * @param   string  $name     The name of the application.
     * @param   array   $options  An optional associative array of configuration settings.
     *
     * @return  Router
     *
     * @since      3.2
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Inject the router or load it from the dependency injection container
     *              Example: Factory::getContainer()->get($name);
     */
    public static function getRouter($name = null, array $options = [])
    {
        $app = Factory::getApplication();

        if (!isset($name)) {
            $name = $app->getName();
        }

        $options['mode'] = $app->get('sef');

        return Router::getInstance($name, $options);
    }

    /**
     * Gets the name of the current template.
     *
     * @param   boolean  $params  An optional associative array of configuration settings
     *
     * @return  string|\stdClass  The name of the template if the params argument is false. The template object if the params argument is true.
     *
     * @since   3.2
     */
    public function getTemplate($params = false)
    {
        if ($params) {
            $template = new \stdClass();

            $template->template    = 'system';
            $template->params      = new Registry();
            $template->inheritable = 0;
            $template->parent      = '';

            return $template;
        }

        return 'system';
    }

    /**
     * Gets a user state.
     *
     * @param   string  $key      The path of the state.
     * @param   mixed   $default  Optional default value, returned if the internal value is null.
     *
     * @return  mixed  The user state or null.
     *
     * @since   3.2
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
     * @param   string  $type     Filter for the variable. Optional.
     *                  @see      \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  mixed  The request user state.
     *
     * @since   3.2
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

    /**
     * Initialise the application.
     *
     * @param   array  $options  An optional associative array of configuration settings.
     *
     * @return  void
     *
     * @since   3.2
     */
    protected function initialiseApp($options = [])
    {
        // Check that we were given a language in the array (since by default may be blank).
        if (isset($options['language'])) {
            $this->set('language', $options['language']);
        }

        // Build our language object
        $lang = $this->getContainer()->get(LanguageFactoryInterface::class)->createLanguage($this->get('language'), $this->get('debug_lang'));

        // Load the language to the API
        $this->loadLanguage($lang);

        // Register the language object with Factory
        Factory::$language = $this->getLanguage();

        // Load the library language files
        $this->loadLibraryLanguage();

        // Set user specific editor.
        $user   = Factory::getUser();
        $editor = $user->getParam('editor', $this->get('editor'));

        if (!PluginHelper::isEnabled('editors', $editor)) {
            $editor = $this->get('editor');

            if (!PluginHelper::isEnabled('editors', $editor)) {
                $editor = 'none';
            }
        }

        $this->set('editor', $editor);

        // Load the behaviour plugins
        PluginHelper::importPlugin('behaviour', null, true, $this->getDispatcher());

        // Trigger the onAfterInitialise event.
        PluginHelper::importPlugin('system', null, true, $this->getDispatcher());
        $this->dispatchEvent(
            'onAfterInitialise',
            new AfterInitialiseEvent('onAfterInitialise', ['subject' => $this])
        );
    }

    /**
     * Checks if HTTPS is forced in the client configuration.
     *
     * @param   integer  $clientId  An optional client id (defaults to current application client).
     *
     * @return  boolean  True if is forced for the client, false otherwise.
     *
     * @since   3.7.3
     */
    public function isHttpsForced($clientId = null)
    {
        $clientId = (int) ($clientId ?? $this->getClientId());
        $forceSsl = (int) $this->get('force_ssl');

        if ($clientId === 0 && $forceSsl === 2) {
            return true;
        }

        if ($clientId === 1 && $forceSsl >= 1) {
            return true;
        }

        return false;
    }

    /**
     * Check the client interface by name.
     *
     * @param   string  $identifier  String identifier for the application interface
     *
     * @return  boolean  True if this application is of the given type client interface.
     *
     * @since   3.7.0
     */
    public function isClient($identifier)
    {
        return $this->getName() === $identifier;
    }

    /**
     * Load the library language files for the application
     *
     * @return  void
     *
     * @since   3.6.3
     */
    protected function loadLibraryLanguage()
    {
        $this->getLanguage()->load('lib_joomla', JPATH_ADMINISTRATOR);
    }

    /**
     * Login authentication function.
     *
     * Username and encoded password are passed the onUserLogin event which
     * is responsible for the user validation. A successful validation updates
     * the current session record with the user's details.
     *
     * Username and encoded password are sent as credentials (along with other
     * possibilities) to each observer (authentication plugin) for user
     * validation.  Successful validation will update the current session with
     * the user details.
     *
     * @param   array  $credentials  Array('username' => string, 'password' => string)
     * @param   array  $options      Array('remember' => boolean)
     *
     * @return  boolean|\Exception  True on success, false if failed or silent handling is configured, or a \Exception object on authentication error.
     *
     * @since   3.2
     */
    public function login($credentials, $options = [])
    {
        // Get the global Authentication object.
        $authenticate = Authentication::getInstance($this->authenticationPluginType);
        $response     = $authenticate->authenticate($credentials, $options);
        $dispatcher   = $this->getDispatcher();

        // Import the user plugin group.
        PluginHelper::importPlugin('user', null, true, $dispatcher);

        if ($response->status === Authentication::STATUS_SUCCESS) {
            /*
             * Validate that the user should be able to login (different to being authenticated).
             * This permits authentication plugins blocking the user.
             */
            $authorisations = $authenticate->authorise($response, $options);
            $denied_states  = Authentication::STATUS_EXPIRED | Authentication::STATUS_DENIED;

            foreach ($authorisations as $authorisation) {
                if ((int) $authorisation->status & $denied_states) {
                    // Trigger onUserAuthorisationFailure Event.
                    $dispatcher->dispatch('onUserAuthorisationFailure', new AuthorisationFailureEvent('onUserAuthorisationFailure', [
                        'subject' => (array) $authorisation,
                        'options' => $options,
                    ]));

                    // If silent is set, just return false.
                    if (isset($options['silent']) && $options['silent']) {
                        return false;
                    }

                    // Return the error.
                    switch ($authorisation->status) {
                        case Authentication::STATUS_EXPIRED:
                            $this->enqueueMessage(Text::_('JLIB_LOGIN_EXPIRED'), 'error');

                            return false;

                        case Authentication::STATUS_DENIED:
                            $this->enqueueMessage(Text::_('JLIB_LOGIN_DENIED'), 'error');

                            return false;

                        default:
                            $this->enqueueMessage(Text::_('JLIB_LOGIN_AUTHORISATION'), 'error');

                            return false;
                    }
                }
            }

            // OK, the credentials are authenticated and user is authorised.  Let's fire the onLogin event.
            $loginEvent = new LoginEvent('onUserLogin', ['subject' => (array) $response, 'options' => $options]);
            $dispatcher->dispatch('onUserLogin', $loginEvent);
            $results = $loginEvent['result'] ?? [];

            /*
             * If any of the user plugins did not successfully complete the login routine
             * then the whole method fails.
             *
             * Any errors raised should be done in the plugin as this provides the ability
             * to provide much more information about why the routine may have failed.
             */
            $user = Factory::getUser();

            if ($response->type === 'Cookie') {
                $user->cookieLogin = true;
            }

            if (\in_array(false, $results, true) == false) {
                $options['user']         = $user;
                $options['responseType'] = $response->type;

                // The user is successfully logged in. Run the after login events
                $dispatcher->dispatch('onUserAfterLogin', new AfterLoginEvent('onUserAfterLogin', [
                    'options' => $options,
                    'subject' => (array) $response,
                ]));

                return true;
            }
        }

        // Trigger onUserLoginFailure Event.
        $dispatcher->dispatch('onUserLoginFailure', new LoginFailureEvent('onUserLoginFailure', [
            'subject' => (array) $response,
            'options' => $options,
        ]));

        // If silent is set, just return false.
        if (isset($options['silent']) && $options['silent']) {
            return false;
        }

        // If status is success, any error will have been raised by the user plugin
        if ($response->status !== Authentication::STATUS_SUCCESS) {
            $this->getLogger()->warning($response->error_message, ['category' => 'jerror']);
        }

        return false;
    }

    /**
     * Logout authentication function.
     *
     * Passed the current user information to the onUserLogout event and reverts the current
     * session record back to 'anonymous' parameters.
     * If any of the authentication plugins did not successfully complete
     * the logout routine then the whole method fails. Any errors raised
     * should be done in the plugin as this provides the ability to give
     * much more information about why the routine may have failed.
     *
     * @param   integer  $userid   The user to load - Can be an integer or string - If string, it is converted to ID automatically
     * @param   array    $options  Array('clientid' => array of client id's)
     *
     * @return  boolean  True on success
     *
     * @since   3.2
     */
    public function logout($userid = null, $options = [])
    {
        // Get a user object from the Application.
        $user       = Factory::getUser($userid);
        $dispatcher = $this->getDispatcher();

        // Build the credentials array.
        $parameters = [
            'username' => $user->username,
            'id'       => $user->id,
        ];

        // Set clientid in the options array if it hasn't been set already and shared sessions are not enabled.
        if (!$this->get('shared_session', '0') && !isset($options['clientid'])) {
            $options['clientid'] = $this->getClientId();
        }

        // Import the user plugin group.
        PluginHelper::importPlugin('user', null, true, $dispatcher);

        // OK, the credentials are built. Lets fire the onLogout event.
        $logoutEvent = new LogoutEvent('onUserLogout', ['subject' => $parameters, 'options' => $options]);
        $dispatcher->dispatch('onUserLogout', $logoutEvent);
        $results = $logoutEvent['result'] ?? [];

        // Check if any of the plugins failed. If none did, success.
        if (!\in_array(false, $results, true)) {
            $options['username'] = $user->username;
            $dispatcher->dispatch('onUserAfterLogout', new AfterLogoutEvent('onUserAfterLogout', [
                'options' => $options,
                'subject' => $parameters,
            ]));

            return true;
        }

        // Trigger onUserLogoutFailure Event.
        $dispatcher->dispatch('onUserLogoutFailure', new LogoutFailureEvent('onUserLogoutFailure', [
            'subject' => $parameters,
            'options' => $options,
        ]));

        return false;
    }

    /**
     * Redirect to another URL.
     *
     * If the headers have not been sent the redirect will be accomplished using a "301 Moved Permanently"
     * or "303 See Other" code in the header pointing to the new location. If the headers have already been
     * sent this will be accomplished using a JavaScript statement.
     *
     * @param   string   $url     The URL to redirect to. Can only be http/https URL
     * @param   integer  $status  The HTTP 1.1 status code to be provided. 303 is assumed by default.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function redirect($url, $status = 303)
    {
        // Persist messages if they exist.
        if (\count($this->messageQueue)) {
            $this->getSession()->set('application.queue', $this->messageQueue);
        }

        // Hand over processing to the parent now
        parent::redirect($url, $status);
    }

    /**
     * Rendering is the process of pushing the document buffers into the template
     * placeholders, retrieving data from the document and pushing it into
     * the application response buffer.
     *
     * @return  void
     *
     * @since   3.2
     */
    protected function render()
    {
        // Setup the document options.
        $this->docOptions['template']         = $this->get('theme');
        $this->docOptions['file']             = $this->get('themeFile', 'index.php');
        $this->docOptions['params']           = $this->get('themeParams');
        $this->docOptions['csp_nonce']        = $this->get('csp_nonce');
        $this->docOptions['templateInherits'] = $this->get('themeInherits');

        if ($this->get('themes.base')) {
            $this->docOptions['directory'] = $this->get('themes.base');
        } else {
            // Fall back to constants.
            $this->docOptions['directory'] = \defined('JPATH_THEMES') ? JPATH_THEMES : (\defined('JPATH_BASE') ? JPATH_BASE : __DIR__) . '/themes';
        }

        // Parse the document.
        $this->document->parse($this->docOptions);

        // Trigger the onBeforeRender event.
        PluginHelper::importPlugin('system', null, true, $this->getDispatcher());
        $this->dispatchEvent(
            'onBeforeRender',
            new BeforeRenderEvent('onBeforeRender', ['subject' => $this])
        );

        $caching = false;

        if ($this->isClient('site') && $this->get('caching') && $this->get('caching', 2) == 2 && !$this->getIdentity()->id) {
            $caching = true;
        }

        // Render the document.
        $data = $this->document->render($caching, $this->docOptions);

        // Set the application output data.
        $this->setBody($data);

        // Trigger the onAfterRender event.
        $this->dispatchEvent(
            'onAfterRender',
            new AfterRenderEvent('onAfterRender', ['subject' => $this])
        );

        // Mark afterRender in the profiler.
        JDEBUG ? $this->profiler->mark('afterRender') : null;
    }

    /**
     * Route the application.
     *
     * Routing is the process of examining the request environment to determine which
     * component should receive the request. The component optional parameters
     * are then set in the request object to be processed when the application is being
     * dispatched.
     *
     * @return     void
     *
     * @since      3.2
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Implement the route functionality in the extending class, this here will be removed without replacement
     */
    protected function route()
    {
        // Get the full request URI.
        $uri = clone Uri::getInstance();

        $router = static::getRouter();
        $result = $router->parse($uri, true);

        $active = $this->getMenu()->getActive();

        if (
            $active !== null
            && $active->type === 'alias'
            && $active->getParams()->get('alias_redirect')
            && \in_array($this->input->getMethod(), ['GET', 'HEAD'], true)
        ) {
            $item = $this->getMenu()->getItem($active->getParams()->get('aliasoptions'));

            if ($item !== null) {
                $oldUri = clone Uri::getInstance();

                if ($oldUri->getVar('Itemid') == $active->id) {
                    $oldUri->setVar('Itemid', $item->id);
                }

                $base             = Uri::base(true);
                $oldPath          = StringHelper::strtolower(substr($oldUri->getPath(), \strlen($base) + 1));
                $activePathPrefix = StringHelper::strtolower($active->route);

                $position = strpos($oldPath, $activePathPrefix);

                if ($position !== false) {
                    $oldUri->setPath($base . '/' . substr_replace($oldPath, $item->route, $position, \strlen($activePathPrefix)));

                    $this->setHeader('Expires', 'Wed, 17 Aug 2005 00:00:00 GMT', true);
                    $this->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT', true);
                    $this->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate', false);
                    $this->sendHeaders();

                    $this->redirect((string) $oldUri, 301);
                }
            }
        }

        foreach ($result as $key => $value) {
            $this->input->def($key, $value);
        }

        // Trigger the onAfterRoute event.
        PluginHelper::importPlugin('system', null, true, $this->getDispatcher());
        $this->dispatchEvent(
            'onAfterRoute',
            new AfterRouteEvent('onAfterRoute', ['subject' => $this])
        );
    }

    /**
     * Sets the value of a user state variable.
     *
     * @param   string  $key    The path of the state.
     * @param   mixed   $value  The value of the variable.
     *
     * @return  mixed  The previous state, if one existed. Null otherwise.
     *
     * @since   3.2
     */
    public function setUserState($key, $value)
    {
        $session  = $this->getSession();
        $registry = $session->get('registry');

        if ($registry !== null) {
            return $registry->set($key, $value);
        }

        return null;
    }

    /**
     * Sends all headers prior to returning the string
     *
     * @param   boolean  $compress  If true, compress the data
     *
     * @return  string
     *
     * @since   3.2
     */
    public function toString($compress = false)
    {
        // Don't compress something if the server is going to do it anyway. Waste of time.
        if ($compress && !\ini_get('zlib.output_compression') && \ini_get('output_handler') !== 'ob_gzhandler') {
            $this->compress();
        }

        if ($this->allowCache() === false) {
            $this->setHeader('Cache-Control', 'no-cache', false);
        }

        $this->sendHeaders();

        return $this->getBody();
    }

    /**
     * Method to determine a hash for anti-spoofing variable names
     *
     * @param   boolean  $forceNew  If true, force a new token to be created
     *
     * @return  string  Hashed var name
     *
     * @since   4.0.0
     */
    public function getFormToken($forceNew = false)
    {
        /** @var Session $session */
        $session = $this->getSession();

        return $session::getFormToken($forceNew);
    }

    /**
     * Checks for a form token in the request.
     *
     * Use in conjunction with getFormToken.
     *
     * @param   string  $method  The request method in which to look for the token key.
     *
     * @return  boolean  True if found and valid, false otherwise.
     *
     * @since   4.0.0
     */
    public function checkToken($method = 'post')
    {
        /** @var Session $session */
        $session = $this->getSession();

        return $session::checkToken($method);
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
     *              Will be removed without replacements
     */
    public function isCli()
    {
        return false;
    }

    /**
     * No longer used
     *
     * @return  boolean
     *
     * @since   4.0.0
     *
     * @throws \Exception
     *
     * @deprecated  4.2 will be removed in 6.0
     *              Will be removed without replacements
     */
    protected function isTwoFactorAuthenticationRequired(): bool
    {
        return false;
    }

    /**
     * No longer used
     *
     * @return  boolean
     *
     * @since   4.0.0
     *
     * @throws \Exception
     *
     * @deprecated  4.2 will be removed in 6.0
     *              Will be removed without replacements
     */
    private function hasUserConfiguredTwoFactorAuthentication(): bool
    {
        return false;
    }

    /**
     * Setup logging functionality.
     *
     * @return void
     *
     * @since   4.0.0
     */
    private function setupLogging(): void
    {
        // Add InMemory logger that will collect all log entries to allow to display them later by extensions
        if ($this->get('debug')) {
            Log::addLogger(['logger' => 'inmemory']);
        }

        // Log the deprecated API.
        if ($this->get('log_deprecated')) {
            Log::addLogger(['text_file' => 'deprecated.php'], Log::ALL, ['deprecated']);
        }

        // We only log errors unless Site Debug is enabled
        $logLevels = Log::ERROR | Log::CRITICAL | Log::ALERT | Log::EMERGENCY;

        if ($this->get('debug')) {
            $logLevels = Log::ALL;
        }

        Log::addLogger(['text_file' => 'joomla_core_errors.php'], $logLevels, ['system']);

        // Log everything (except deprecated APIs, these are logged separately with the option above).
        if ($this->get('log_everything')) {
            Log::addLogger(['text_file' => 'everything.php'], Log::ALL, ['deprecated', 'deprecation-notes', 'databasequery'], true);
        }

        if ($this->get('log_categories')) {
            $priority = 0;

            foreach ($this->get('log_priorities', ['all']) as $p) {
                $const = '\\Joomla\\CMS\\Log\\Log::' . strtoupper($p);

                if (\defined($const)) {
                    $priority |= \constant($const);
                }
            }

            // Split into an array at any character other than alphabet, numbers, _, ., or -
            $categories = preg_split('/[^\w.-]+/', $this->get('log_categories', ''), -1, PREG_SPLIT_NO_EMPTY);
            $mode       = (bool) $this->get('log_category_mode', false);

            if (!$categories) {
                return;
            }

            Log::addLogger(['text_file' => 'custom-logging.php'], $priority, $categories, $mode);
        }
    }

    /**
     * Sets the internal menu factory.
     *
     * @param  MenuFactoryInterface  $menuFactory  The menu factory
     *
     * @return void
     *
     * @since  4.2.0
     */
    public function setMenuFactory(MenuFactoryInterface $menuFactory): void
    {
        $this->menuFactory = $menuFactory;
    }
}
