<?php
/**
 * Part of the Joomla Framework Application Package
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Application;

use Joomla\Application\Controller\ControllerResolverInterface;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use Joomla\Router\Router;
use Psr\Http\Message\ResponseInterface;

/**
 * A basic web application class for handing HTTP requests.
 *
 * @since  __DEPLOY_VERSION__
 */
class WebApplication extends AbstractWebApplication
{
	/**
	 * The application's controller resolver.
	 *
	 * @var    ControllerResolverInterface
	 * @since  __DEPLOY_VERSION__
	 */
	protected $controllerResolver;

	/**
	 * The application's router.
	 *
	 * @var    Router
	 * @since  __DEPLOY_VERSION__
	 */
	protected $router;

	/**
	 * Class constructor.
	 *
	 * @param   ControllerResolverInterface  $controllerResolver  The application's controller resolver
	 * @param   Router                       $router              The application's router
	 * @param   Input                        $input               An optional argument to provide dependency injection for the application's
	 *                                                            input object.  If the argument is an Input object that object will become
	 *                                                            the application's input object, otherwise a default input object is
	 *                                                            created.
	 * @param   Registry                     $config              An optional argument to provide dependency injection for the application's
	 *                                                            config object.  If the argument is a Registry object that object will
	 *                                                            become the application's config object, otherwise a default config object
	 *                                                            is created.
	 * @param   Web\WebClient                $client              An optional argument to provide dependency injection for the application's
	 *                                                            client object.  If the argument is a Web\WebClient object that object will
	 *                                                            become the application's client object, otherwise a default client object
	 *                                                            is created.
	 * @param   ResponseInterface            $response            An optional argument to provide dependency injection for the application's
	 *                                                            response object.  If the argument is a ResponseInterface object that object
	 *                                                            will become the application's response object, otherwise a default response
	 *                                                            object is created.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(
		ControllerResolverInterface $controllerResolver,
		Router $router,
		Input $input = null,
		Registry $config = null,
		Web\WebClient $client = null,
		ResponseInterface $response = null
	)
	{
		$this->controllerResolver = $controllerResolver;
		$this->router             = $router;

		// Call the constructor as late as possible (it runs `initialise`).
		parent::__construct($input, $config, $client, $response);
	}

	/**
	 * Method to run the application routines.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function doExecute()
	{
		$route = $this->router->parseRoute($this->get('uri.route'), $this->input->getMethod());

		// Add variables to the input if not already set
		foreach ($route->getRouteVariables() as $key => $value)
		{
			$this->input->def($key, $value);
		}

		call_user_func($this->controllerResolver->resolve($route));
	}
}
