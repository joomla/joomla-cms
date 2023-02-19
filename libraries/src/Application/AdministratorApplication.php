<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application;

use Joomla\Application\Web\WebClient;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\DI\Container;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Administrator Application class
 *
 * @since  3.2
 */
class AdministratorApplication extends CMSApplication
{
    use MultiFactorAuthenticationHandler;

    /**
     * List of allowed components for guests and users which do not have the core.login.admin privilege.
     *
     * By default we allow two core components:
     *
     * - com_login   Absolutely necessary to let users log into the backend of the site. Do NOT remove!
     * - com_ajax    Handle AJAX requests or other administrative callbacks without logging in. Required for
     *               passwordless authentication using WebAuthn.
     *
     * @var array
     */
    protected $allowedUnprivilegedOptions = [
        'com_login',
        'com_ajax',
    ];

    /**
     * Class constructor.
     *
     * @param   Input      $input      An optional argument to provide dependency injection for the application's input
     *                                 object.  If the argument is a JInput object that object will become the
     *                                 application's input object, otherwise a default input object is created.
     * @param   Registry   $config     An optional argument to provide dependency injection for the application's config
     *                                 object.  If the argument is a Registry object that object will become the
     *                                 application's config object, otherwise a default config object is created.
     * @param   WebClient  $client     An optional argument to provide dependency injection for the application's
     *                                 client object.  If the argument is a WebClient object that object will become the
     *                                 application's client object, otherwise a default client object is created.
     * @param   Container  $container  Dependency injection container.
     *
     * @since   3.2
     */
    public function __construct(Input $input = null, Registry $config = null, WebClient $client = null, Container $container = null)
    {
        // Register the application name
        $this->name = 'administrator';

        // Register the client ID
        $this->clientId = 1;

        // Execute the parent constructor
        parent::__construct($input, $config, $client, $container);

        // Set the root in the URI based on the application name
        Uri::root(null, rtrim(\dirname(Uri::base(true)), '/\\'));
    }

    /**
     * Dispatch the application
     *
     * @param   string  $component  The component which is being rendered.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function dispatch($component = null)
    {
        if ($component === null) {
            $component = $this->findOption();
        }

        // Load the document to the API
        $this->loadDocument();

        // Set up the params
        $document = Factory::getDocument();

        // Register the document object with Factory
        Factory::$document = $document;

        switch ($document->getType()) {
            case 'html':
                // Get the template
                $template = $this->getTemplate(true);
                $clientId = $this->getClientId();

                // Store the template and its params to the config
                $this->set('theme', $template->template);
                $this->set('themeParams', $template->params);

                // Add Asset registry files
                $wr = $document->getWebAssetManager()->getRegistry();

                if ($component) {
                    $wr->addExtensionRegistryFile($component);
                }

                if (!empty($template->parent)) {
                    $wr->addTemplateRegistryFile($template->parent, $clientId);
                }

                $wr->addTemplateRegistryFile($template->template, $clientId);

                break;

            default:
                break;
        }

        $document->setTitle($this->get('sitename') . ' - ' . Text::_('JADMINISTRATION'));
        $document->setDescription($this->get('MetaDesc'));
        $document->setGenerator('Joomla! - Open Source Content Management');

        $contents = ComponentHelper::renderComponent($component);
        $document->setBuffer($contents, 'component');

        // Trigger the onAfterDispatch event.
        PluginHelper::importPlugin('system');
        $this->triggerEvent('onAfterDispatch');
    }

    /**
     * Method to run the Web application routines.
     *
     * @return  void
     *
     * @since   3.2
     */
    protected function doExecute()
    {
        // Get the language from the (login) form or user state
        $login_lang = ($this->input->get('option') === 'com_login') ? $this->input->get('lang') : '';
        $options    = ['language' => $login_lang ?: $this->getUserState('application.lang')];

        // Initialise the application
        $this->initialiseApp($options);

        // Mark afterInitialise in the profiler.
        JDEBUG ? $this->profiler->mark('afterInitialise') : null;

        // Route the application
        $this->route();

        // Mark afterRoute in the profiler.
        JDEBUG ? $this->profiler->mark('afterRoute') : null;

        /*
         * Check if the user is required to reset their password
         *
         * Before $this->route(); "option" and "view" can't be safely read using:
         * $this->input->getCmd('option'); or $this->input->getCmd('view');
         * ex: due of the sef urls
         */
        $this->checkUserRequireReset('com_users', 'user', 'edit', 'com_users/user.edit,com_users/user.save,com_users/user.apply,com_login/logout');

        // Dispatch the application
        $this->dispatch();

        // Mark afterDispatch in the profiler.
        JDEBUG ? $this->profiler->mark('afterDispatch') : null;
    }

    /**
     * Return a reference to the Router object.
     *
     * @param   string  $name     The name of the application.
     * @param   array   $options  An optional associative array of configuration settings.
     *
     * @return  Router
     *
     * @since      3.2
     * @deprecated 5.0 Inject the router or load it from the dependency injection container
     */
    public static function getRouter($name = 'administrator', array $options = [])
    {
        return parent::getRouter($name, $options);
    }

    /**
     * Gets the name of the current template.
     *
     * @param   boolean  $params  True to return the template parameters
     *
     * @return  string  The name of the template.
     *
     * @since   3.2
     * @throws  \InvalidArgumentException
     */
    public function getTemplate($params = false)
    {
        if (\is_object($this->template)) {
            if ($params) {
                return $this->template;
            }

            return $this->template->template;
        }

        $adminStyle = $this->getIdentity() ? (int) $this->getIdentity()->getParam('admin_style') : 0;
        $template   = $this->bootComponent('templates')->getMVCFactory()
            ->createModel('Style', 'Administrator')->getAdminTemplate($adminStyle);

        $template->template = InputFilter::getInstance()->clean($template->template, 'cmd');
        $template->params = new Registry($template->params);

        // Fallback template
        if (
            !is_file(JPATH_THEMES . '/' . $template->template . '/index.php')
            && !is_file(JPATH_THEMES . '/' . $template->parent . '/index.php')
        ) {
            $this->getLogger()->error(Text::_('JERROR_ALERTNOTEMPLATE'), ['category' => 'system']);
            $template->params = new Registry();
            $template->template = 'atum';

            // Check, the data were found and if template really exists
            if (!is_file(JPATH_THEMES . '/' . $template->template . '/index.php')) {
                throw new \InvalidArgumentException(Text::sprintf('JERROR_COULD_NOT_FIND_TEMPLATE', $template->template));
            }
        }

        // Cache the result
        $this->template = $template;

        // Pass the parent template to the state
        $this->set('themeInherits', $template->parent);

        if ($params) {
            return $template;
        }

        return $template->template;
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
        $user = Factory::getUser();

        // If the user is a guest we populate it with the guest user group.
        if ($user->guest) {
            $guestUsergroup = ComponentHelper::getParams('com_users')->get('guest_usergroup', 1);
            $user->groups = [$guestUsergroup];
        }

        // If a language was specified it has priority, otherwise use user or default language settings
        if (empty($options['language'])) {
            $lang = $user->getParam('admin_language');

            // Make sure that the user's language exists
            if ($lang && LanguageHelper::exists($lang)) {
                $options['language'] = $lang;
            } else {
                $params = ComponentHelper::getParams('com_languages');
                $options['language'] = $params->get('administrator', $this->get('language', 'en-GB'));
            }
        }

        // One last check to make sure we have something
        if (!LanguageHelper::exists($options['language'])) {
            $lang = $this->get('language', 'en-GB');

            if (LanguageHelper::exists($lang)) {
                $options['language'] = $lang;
            } else {
                // As a last ditch fail to english
                $options['language'] = 'en-GB';
            }
        }

        // Finish initialisation
        parent::initialiseApp($options);
    }

    /**
     * Login authentication function
     *
     * @param   array  $credentials  Array('username' => string, 'password' => string)
     * @param   array  $options      Array('remember' => boolean)
     *
     * @return  boolean  True on success.
     *
     * @since   3.2
     */
    public function login($credentials, $options = [])
    {
        // The minimum group
        $options['group'] = 'Public Backend';

        // Make sure users are not auto-registered
        $options['autoregister'] = false;

        // Set the application login entry point
        if (!\array_key_exists('entry_url', $options)) {
            $options['entry_url'] = Uri::base() . 'index.php?option=com_users&task=login';
        }

        // Set the access control action to check.
        $options['action'] = 'core.login.admin';

        $result = parent::login($credentials, $options);

        if (!($result instanceof \Exception)) {
            $lang = $this->input->getCmd('lang', '');
            $lang = preg_replace('/[^A-Z-]/i', '', $lang);

            if ($lang) {
                $this->setUserState('application.lang', $lang);
            }

            $this->bootComponent('messages')->getMVCFactory()
                ->createModel('Messages', 'Administrator')->purge($this->getIdentity() ? $this->getIdentity()->id : 0);
        }

        return $result;
    }

    /**
     * Purge the jos_messages table of old messages
     *
     * @return  void
     *
     * @since   3.2
     *
     * @deprecated  5.0 Purge the messages through the model
     */
    public static function purgeMessages()
    {
        Factory::getApplication()->bootComponent('messages')->getMVCFactory()
            ->createModel('Messages', 'Administrator')->purge(Factory::getUser()->id);
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
        // Get the \JInput object
        $input = $this->input;

        $component = $input->getCmd('option', 'com_login');
        $file      = $input->getCmd('tmpl', 'index');

        if ($component === 'com_login') {
            $file = 'login';
        }

        $this->set('themeFile', $file . '.php');

        // Safety check for when configuration.php root_user is in use.
        $rootUser = $this->get('root_user');

        if (property_exists('\JConfig', 'root_user')) {
            if (Factory::getUser()->get('username') === $rootUser || Factory::getUser()->id === (string) $rootUser) {
                $this->enqueueMessage(
                    Text::sprintf(
                        'JWARNING_REMOVE_ROOT_USER',
                        'index.php?option=com_config&task=application.removeroot&' . Session::getFormToken() . '=1'
                    ),
                    'warning'
                );
            } elseif (Factory::getUser()->authorise('core.admin')) {
                // Show this message to superusers too
                $this->enqueueMessage(
                    Text::sprintf(
                        'JWARNING_REMOVE_ROOT_USER_ADMIN',
                        $rootUser,
                        'index.php?option=com_config&task=application.removeroot&' . Session::getFormToken() . '=1'
                    ),
                    'warning'
                );
            }
        }

        parent::render();
    }

    /**
     * Route the application.
     *
     * Routing is the process of examining the request environment to determine which
     * component should receive the request. The component optional parameters
     * are then set in the request object to be processed when the application is being
     * dispatched.
     *
     * @return  void
     *
     * @since   3.2
     */
    protected function route()
    {
        $uri = Uri::getInstance();

        if ($this->get('force_ssl') >= 1 && strtolower($uri->getScheme()) !== 'https') {
            // Forward to https
            $uri->setScheme('https');
            $this->redirect((string) $uri, 301);
        }

        $this->isHandlingMultiFactorAuthentication();

        // Trigger the onAfterRoute event.
        PluginHelper::importPlugin('system');
        $this->triggerEvent('onAfterRoute');
    }

    /**
     * Return the application option string [main component].
     *
     * @return  string  The component to access.
     *
     * @since   4.0.0
     */
    public function findOption(): string
    {
        /** @var self $app */
        $app    = Factory::getApplication();
        $option = strtolower($app->input->get('option', ''));
        $user   = $app->getIdentity();

        /**
         * Special handling for guest users and authenticated users without the Backend Login privilege.
         *
         * If the component they are trying to access is in the $this->allowedUnprivilegedOptions array we allow the
         * request to go through. Otherwise we force com_login to be loaded, letting the user (re)try authenticating
         * with a user account that has the Backend Login privilege.
         */
        if ($user->get('guest') || !$user->authorise('core.login.admin')) {
            $option = in_array($option, $this->allowedUnprivilegedOptions) ? $option : 'com_login';
        }

        /**
         * If no component is defined in the request we will try to load com_cpanel, the administrator Control Panel
         * component. This allows the /administrator URL to display something meaningful after logging in instead of an
         * error.
         */
        if (empty($option)) {
            $option = 'com_cpanel';
        }

        /**
         * Force the option to the input object. This is necessary because we might have force-changed the component in
         * the two if-blocks above.
         */
        $app->input->set('option', $option);

        return $option;
    }
}
