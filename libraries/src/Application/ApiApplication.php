<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Application;

\defined('JPATH_PLATFORM') or die;

use Joomla\Application\Web\WebClient;
use Joomla\CMS\Access\Exception\AuthenticationFailed;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\ApiRouter;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\CMS\Uri\Uri;
use Joomla\DI\Container;
use Joomla\Input\Json as JInputJson;
use Joomla\Registry\Registry;
use Negotiation\Accept;
use Negotiation\Exception\InvalidArgument;
use Negotiation\Negotiator;

/**
 * Joomla! API Application class
 *
 * @since  4.0.0
 */
final class ApiApplication extends CMSApplication
{
	/**
	 * Maps extension types to their
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $formatMapper = array();

	/**
	 * The authentication plugin type
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $authenticationPluginType = 'api-authentication';

	/**
	 * Class constructor.
	 *
	 * @param   JInputJson  $input      An optional argument to provide dependency injection for the application's input
	 *                                  object.  If the argument is a JInput object that object will become the
	 *                                  application's input object, otherwise a default input object is created.
	 * @param   Registry    $config     An optional argument to provide dependency injection for the application's config
	 *                                  object.  If the argument is a Registry object that object will become the
	 *                                  application's config object, otherwise a default config object is created.
	 * @param   WebClient   $client     An optional argument to provide dependency injection for the application's client
	 *                                  object.  If the argument is a WebClient object that object will become the
	 *                                  application's client object, otherwise a default client object is created.
	 * @param   Container   $container  Dependency injection container.
	 *
	 * @since   4.0.0
	 */
	public function __construct(JInputJson $input = null, Registry $config = null, WebClient $client = null, Container $container = null)
	{
		// Register the application name
		$this->name = 'api';

		// Register the client ID
		$this->clientId = 3;

		// Execute the parent constructor
		parent::__construct($input, $config, $client, $container);

		$this->addFormatMap('application/json', 'json');
		$this->addFormatMap('application/vnd.api+json', 'jsonapi');

		// Set the root in the URI based on the application name
		Uri::root(null, str_ireplace('/' . $this->getName(), '', Uri::base(true)));
	}


	/**
	 * Method to run the application routines.
	 *
	 * Most likely you will want to instantiate a controller and execute it, or perform some sort of task directly.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function doExecute()
	{
		// Initialise the application
		$this->initialiseApp();

		// Mark afterInitialise in the profiler.
		JDEBUG ? $this->profiler->mark('afterInitialise') : null;

		// Route the application
		$this->route();

		// Mark afterApiRoute in the profiler.
		JDEBUG ? $this->profiler->mark('afterApiRoute') : null;

		// Dispatch the application
		$this->dispatch();

		// Mark afterDispatch in the profiler.
		JDEBUG ? $this->profiler->mark('afterDispatch') : null;
	}

	/**
	 * Adds a mapping from a content type to the format stored. Note the format type cannot be overwritten.
	 *
	 * @param   string  $contentHeader  The content header
	 * @param   string  $format         The content type format
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function addFormatMap($contentHeader, $format)
	{
		if (!\array_key_exists($contentHeader, $this->formatMapper))
		{
			$this->formatMapper[$contentHeader] = $format;
		}
	}

	/**
	 * Rendering is the process of pushing the document buffers into the template
	 * placeholders, retrieving data from the document and pushing it into
	 * the application response buffer.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 *
	 * @note    Rendering should be overridden to get rid of the theme files.
	 */
	protected function render()
	{
		// Render the document
		$this->setBody($this->document->render($this->allowCache()));
	}

	/**
	 * Method to send the application response to the client.  All headers will be sent prior to the main application output data.
	 *
	 * @param   array  $options  An optional argument to enable CORS. (Temporary)
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function respond($options = array())
	{
		// Set the Joomla! API signature
		$this->setHeader('X-Powered-By', 'JoomlaAPI/1.0', true);

		$forceCORS = (int) $this->get('cors');

		if ($forceCORS)
		{
			/**
			* Enable CORS (Cross-origin resource sharing)
			* Obtain allowed CORS origin from Global Settings.
			* Set to * (=all) if not set.
			*/
			$allowedOrigin = $this->get('cors_allow_origin', '*');
			$this->setHeader('Access-Control-Allow-Origin', $allowedOrigin, true);
			$this->setHeader('Access-Control-Allow-Headers', 'Authorization');

			if ($this->input->server->getString('HTTP_ORIGIN', null) !== null)
			{
				$this->setHeader('Access-Control-Allow-Origin', $this->input->server->getString('HTTP_ORIGIN'), true);
				$this->setHeader('Access-Control-Allow-Credentials', 'true', true);
			}
		}

		// Parent function can be overridden later on for debugging.
		parent::respond();
	}

	/**
	 * Gets the name of the current template.
	 *
	 * @param   boolean  $params  True to return the template parameters
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function getTemplate($params = false)
	{
		// The API application should not need to use a template
		if ($params)
		{
			$template              = new \stdClass;
			$template->template    = 'system';
			$template->params      = new Registry;
			$template->inheritable = 0;
			$template->parent      = '';

			return $template;
		}

		return 'system';
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
	 * @since   4.0.0
	 */
	protected function route()
	{
		$router = $this->getApiRouter();

		// Trigger the onBeforeApiRoute event.
		PluginHelper::importPlugin('webservices');
		$this->triggerEvent('onBeforeApiRoute', array(&$router, $this));
		$caught404 = false;
		$method    = $this->input->getMethod();

		try
		{
			$this->handlePreflight($method, $router);

			$route = $router->parseApiRoute($method);
		}
		catch (RouteNotFoundException $e)
		{
			$caught404 = true;
		}

		/**
		 * Now we have an API perform content negotiation to ensure we have a valid header. Assume if the route doesn't
		 * tell us otherwise it uses the plain JSON API
		 */
		$priorities = array('application/vnd.api+json');

		if (!$caught404 && \array_key_exists('format', $route['vars']))
		{
			$priorities = $route['vars']['format'];
		}

		$negotiator = new Negotiator;

		try
		{
			$mediaType = $negotiator->getBest($this->input->server->getString('HTTP_ACCEPT'), $priorities);
		}
		catch (InvalidArgument $e)
		{
			$mediaType = null;
		}

		// If we can't find a match bail with a 406 - Not Acceptable
		if ($mediaType === null)
		{
			throw new Exception\NotAcceptable('Could not match accept header', 406);
		}

		/** @var $mediaType Accept */
		$format = $mediaType->getValue();

		if (\array_key_exists($mediaType->getValue(), $this->formatMapper))
		{
			$format = $this->formatMapper[$mediaType->getValue()];
		}

		$this->input->set('format', $format);

		if ($caught404)
		{
			throw $e;
		}

		$this->input->set('option', $route['vars']['component']);
		$this->input->set('controller', $route['controller']);
		$this->input->set('task', $route['task']);

		foreach ($route['vars'] as $key => $value)
		{
			if ($key !== 'component')
			{
				if ($this->input->getMethod() === 'POST')
				{
					$this->input->post->set($key, $value);
				}
				else
				{
					$this->input->set($key, $value);
				}
			}
		}

		$this->triggerEvent('onAfterApiRoute', array($this));

		if (!isset($route['vars']['public']) || $route['vars']['public'] === false)
		{
			if (!$this->login(array('username' => ''), array('silent' => true, 'action' => 'core.login.api')))
			{
				throw new AuthenticationFailed;
			}
		}
	}

	/**
	 * Handles preflight requests.
	 *
	 * @param   String     $method  The REST verb
	 *
	 * @param   ApiRouter  $router  The API Routing object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function handlePreflight($method, $router)
	{
		/**
		* If not an OPTIONS request or CORS is not enabled,
		* there's nothing useful to do here.
		*/
		if ($method !== 'OPTIONS' || !(int) $this->get('cors'))
		{
			return;
		}

		// Extract routes matching current route from all known routes.
		$matchingRoutes = $router->getMatchingRoutes();

		// Extract exposed methods from matching routes.
		$matchingRoutesMethods = array_unique(
			array_reduce($matchingRoutes,
				function ($carry, $route) {
					return array_merge($carry, $route->getMethods());
				},
				[]
			)
		);

		/**
		* Obtain allowed CORS origin from Global Settings.
		* Set to * (=all) if not set.
		*/
		$allowedOrigin = $this->get('cors_allow_origin', '*');

		/**
		* Obtain allowed CORS headers from Global Settings.
		* Set to sensible default if not set.
		*/
		$allowedHeaders = $this->get('cors_allowed_headers', 'Content-Type,X-Joomla-Token');

		/**
		* Obtain allowed CORS methods from Global Settings.
		* Set to methods exposed by current route if not set.
		*/
		$allowedMethods = $this->get('cors_allowed_headers', implode(',', $matchingRoutesMethods));

		// No use to go through the regular route handling hassle,
		// so let's simply output the headers and exit.
		$this->setHeader('status', '204');
		$this->setHeader('Access-Control-Allow-Origin', $allowedOrigin);
		$this->setHeader('Access-Control-Allow-Headers', $allowedHeaders);
		$this->setHeader('Access-Control-Allow-Methods', $allowedMethods);
		$this->sendHeaders();

		$this->close();
	}

	/**
	 * Returns the application Router object.
	 *
	 * @return  ApiRouter
	 *
	 * @since   4.0.0
	 */
	public function getApiRouter()
	{
		return $this->getContainer()->get('ApiRouter');
	}

	/**
	 * Dispatch the application
	 *
	 * @param   string  $component  The component which is being rendered.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function dispatch($component = null)
	{
		// Get the component if not set.
		if (!$component)
		{
			$component = $this->input->get('option', null);
		}

		// Load the document to the API
		$this->loadDocument();

		// Set up the params
		$document = Factory::getDocument();

		// Register the document object with Factory
		Factory::$document = $document;

		$contents = ComponentHelper::renderComponent($component);
		$document->setBuffer($contents, 'component');

		// Trigger the onAfterDispatch event.
		PluginHelper::importPlugin('system');
		$this->triggerEvent('onAfterDispatch');
	}
}
