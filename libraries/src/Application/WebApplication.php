<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application;

use Joomla\Application\AbstractWebApplication;
use Joomla\Application\Web\WebClient;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Input\Input;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\Version;
use Joomla\Registry\Registry;
use Joomla\Session\SessionEvent;
use Psr\Http\Message\ResponseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for a Joomla! Web application.
 *
 * @since  2.5.0
 */
abstract class WebApplication extends AbstractWebApplication
{
    use EventAware;
    use IdentityAware;

    /**
     * The application component title.
     *
     * @var    string
     * @since  4.2.7
     */
    public $JComponentTitle;

    /**
     * The item associations
     *
     * @var    integer
     * @since  4.2.9
     */
    public $item_associations;

    /**
     * The application document object.
     *
     * @var    Document
     * @since  1.7.3
     */
    protected $document;

    /**
     * The application language object.
     *
     * @var    Language
     * @since  1.7.3
     */
    protected $language;

    /**
     * The application instance.
     *
     * @var    static
     * @since  1.7.3
     */
    protected static $instance;

    /**
     * Class constructor.
     *
     * @param   Input              $input     An optional argument to provide dependency injection for the application's
     *                                        input object.  If the argument is a JInput object that object will become
     *                                        the application's input object, otherwise a default input object is created.
     * @param   Registry           $config    An optional argument to provide dependency injection for the application's
     *                                        config object.  If the argument is a Registry object that object will become
     *                                        the application's config object, otherwise a default config object is created.
     * @param   WebClient          $client    An optional argument to provide dependency injection for the application's
     *                                        client object.  If the argument is a WebClient object that object will become
     *                                        the application's client object, otherwise a default client object is created.
     * @param   ResponseInterface  $response  An optional argument to provide dependency injection for the application's
     *                                        response object.  If the argument is a ResponseInterface object that object
     *                                        will become the application's response object, otherwise a default response
     *                                        object is created.
     *
     * @since   1.7.3
     */
    public function __construct(Input $input = null, Registry $config = null, WebClient $client = null, ResponseInterface $response = null)
    {
        // Ensure we have a CMS Input object otherwise the DI for \Joomla\CMS\Session\Storage\JoomlaStorage fails
        $input = $input ?: new Input();

        parent::__construct($input, $config, $client, $response);

        // Set the execution datetime and timestamp;
        $this->set('execution.datetime', gmdate('Y-m-d H:i:s'));
        $this->set('execution.timestamp', time());

        // Set the system URIs.
        $this->loadSystemUris();
    }

    /**
     * Returns a reference to the global WebApplication object, only creating it if it doesn't already exist.
     *
     * This method must be invoked as: $web = WebApplication::getInstance();
     *
     * @param   string  $name  The name (optional) of the WebApplication class to instantiate.
     *
     * @return  WebApplication
     *
     * @since       1.7.3
     * @throws      \RuntimeException
     * @deprecated  5.0 Use \Joomla\CMS\Factory::getContainer()->get($name) instead
     */
    public static function getInstance($name = null)
    {
        // Only create the object if it doesn't exist.
        if (empty(static::$instance)) {
            if (!is_subclass_of($name, '\\Joomla\\CMS\\Application\\WebApplication')) {
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
     * @since   1.7.3
     */
    public function execute()
    {
        // Trigger the onBeforeExecute event.
        $this->triggerEvent('onBeforeExecute');

        // Perform application routines.
        $this->doExecute();

        // Trigger the onAfterExecute event.
        $this->triggerEvent('onAfterExecute');

        // If we have an application document object, render it.
        if ($this->document instanceof Document) {
            // Trigger the onBeforeRender event.
            $this->triggerEvent('onBeforeRender');

            // Render the application output.
            $this->render();

            // Trigger the onAfterRender event.
            $this->triggerEvent('onAfterRender');
        }

        // If gzip compression is enabled in configuration and the server is compliant, compress the output.
        if ($this->get('gzip') && !ini_get('zlib.output_compression') && (ini_get('output_handler') !== 'ob_gzhandler')) {
            $this->compress();
        }

        // Trigger the onBeforeRespond event.
        $this->triggerEvent('onBeforeRespond');

        // Send the application response.
        $this->respond();

        // Trigger the onAfterRespond event.
        $this->triggerEvent('onAfterRespond');
    }

    /**
     * Rendering is the process of pushing the document buffers into the template
     * placeholders, retrieving data from the document and pushing it into
     * the application response buffer.
     *
     * @return  void
     *
     * @since   1.7.3
     */
    protected function render()
    {
        // Setup the document options.
        $options = [
            'template'         => $this->get('theme'),
            'file'             => $this->get('themeFile', 'index.php'),
            'params'           => $this->get('themeParams'),
            'templateInherits' => $this->get('themeInherits'),
        ];

        if ($this->get('themes.base')) {
            $options['directory'] = $this->get('themes.base');
        } else {
            // Fall back to constants.
            $options['directory'] = \defined('JPATH_THEMES') ? JPATH_THEMES : (\defined('JPATH_BASE') ? JPATH_BASE : __DIR__) . '/themes';
        }

        // Parse the document.
        $this->document->parse($options);

        // Render the document.
        $data = $this->document->render($this->get('cache_enabled'), $options);

        // Set the application output data.
        $this->setBody($data);
    }

    /**
     * Method to get the application document object.
     *
     * @return  Document  The document object
     *
     * @since   1.7.3
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Method to get the application language object.
     *
     * @return  Language  The language object
     *
     * @since   1.7.3
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Flush the media version to refresh versionable assets
     *
     * @return  void
     *
     * @since   3.2
     */
    public function flushAssets()
    {
        (new Version())->refreshMediaVersion();
    }

    /**
     * Allows the application to load a custom or default document.
     *
     * The logic and options for creating this object are adequately generic for default cases
     * but for many applications it will make sense to override this method and create a document,
     * if required, based on more specific needs.
     *
     * @param   Document  $document  An optional document object. If omitted, the factory document is created.
     *
     * @return  WebApplication This method is chainable.
     *
     * @since   1.7.3
     */
    public function loadDocument(Document $document = null)
    {
        $this->document = $document ?? Factory::getDocument();

        return $this;
    }

    /**
     * Allows the application to load a custom or default language.
     *
     * The logic and options for creating this object are adequately generic for default cases
     * but for many applications it will make sense to override this method and create a language,
     * if required, based on more specific needs.
     *
     * @param   Language  $language  An optional language object. If omitted, the factory language is created.
     *
     * @return  WebApplication This method is chainable.
     *
     * @since   1.7.3
     */
    public function loadLanguage(Language $language = null)
    {
        $this->language = $language ?? Factory::getLanguage();

        return $this;
    }

    /**
     * Allows the application to load a custom or default session.
     *
     * The logic and options for creating this object are adequately generic for default cases
     * but for many applications it will make sense to override this method and create a session,
     * if required, based on more specific needs.
     *
     * @param   Session  $session  An optional session object. If omitted, the session is created.
     *
     * @return  WebApplication This method is chainable.
     *
     * @since   1.7.3
     * @deprecated  5.0  The session should be injected as a service.
     */
    public function loadSession(Session $session = null)
    {
        $this->getLogger()->warning(__METHOD__ . '() is deprecated.  Inject the session as a service instead.', ['category' => 'deprecated']);

        return $this;
    }

    /**
     * After the session has been started we need to populate it with some default values.
     *
     * @param   SessionEvent  $event  Session event being triggered
     *
     * @return  void
     *
     * @since   3.0.1
     */
    public function afterSessionStart(SessionEvent $event)
    {
        $session = $event->getSession();

        if ($session->isNew()) {
            $session->set('registry', new Registry());
            $session->set('user', new User());
        }

        // Ensure the identity is loaded
        if (!$this->getIdentity()) {
            $this->loadIdentity($session->get('user'));
        }
    }

    /**
     * Method to load the system URI strings for the application.
     *
     * @param   string  $requestUri  An optional request URI to use instead of detecting one from the
     *                               server environment variables.
     *
     * @return  void
     *
     * @since   1.7.3
     */
    protected function loadSystemUris($requestUri = null)
    {
        // Set the request URI.
        if (!empty($requestUri)) {
            $this->set('uri.request', $requestUri);
        } else {
            $this->set('uri.request', $this->detectRequestUri());
        }

        // Check to see if an explicit base URI has been set.
        $siteUri = trim($this->get('site_uri', ''));

        if ($siteUri !== '') {
            $uri = Uri::getInstance($siteUri);
            $path = $uri->toString(['path']);
        } else {
            // No explicit base URI was set so we need to detect it.
            // Start with the requested URI.
            $uri = Uri::getInstance($this->get('uri.request'));

            // If we are working from a CGI SAPI with the 'cgi.fix_pathinfo' directive disabled we use PHP_SELF.
            if (strpos(PHP_SAPI, 'cgi') !== false && !ini_get('cgi.fix_pathinfo') && !empty($_SERVER['REQUEST_URI'])) {
                // We aren't expecting PATH_INFO within PHP_SELF so this should work.
                $path = \dirname($_SERVER['PHP_SELF']);
            } else {
                // Pretty much everything else should be handled with SCRIPT_NAME.
                $path = \dirname($_SERVER['SCRIPT_NAME']);
            }
        }

        $host = $uri->toString(['scheme', 'user', 'pass', 'host', 'port']);

        // Check if the path includes "index.php".
        if (strpos($path, 'index.php') !== false) {
            // Remove the index.php portion of the path.
            $path = substr_replace($path, '', strpos($path, 'index.php'), 9);
        }

        $path = rtrim($path, '/\\');

        // Set the base URI both as just a path and as the full URI.
        $this->set('uri.base.full', $host . $path . '/');
        $this->set('uri.base.host', $host);
        $this->set('uri.base.path', $path . '/');

        // Set the extended (non-base) part of the request URI as the route.
        if (stripos($this->get('uri.request'), $this->get('uri.base.full')) === 0) {
            $this->set('uri.route', substr_replace($this->get('uri.request'), '', 0, \strlen($this->get('uri.base.full'))));
        }

        // Get an explicitly set media URI is present.
        $mediaURI = trim($this->get('media_uri', ''));

        if ($mediaURI) {
            if (strpos($mediaURI, '://') !== false) {
                $this->set('uri.media.full', $mediaURI);
                $this->set('uri.media.path', $mediaURI);
            } else {
                // Normalise slashes.
                $mediaURI = trim($mediaURI, '/\\');
                $mediaURI = !empty($mediaURI) ? '/' . $mediaURI . '/' : '/';
                $this->set('uri.media.full', $this->get('uri.base.host') . $mediaURI);
                $this->set('uri.media.path', $mediaURI);
            }
        } else {
            // No explicit media URI was set, build it dynamically from the base uri.
            $this->set('uri.media.full', $this->get('uri.base.full') . 'media/');
            $this->set('uri.media.path', $this->get('uri.base.path') . 'media/');
        }
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
}
