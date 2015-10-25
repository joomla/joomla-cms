<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 08.06.13 22:28 $
* @package CBLib
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Controller;

use CBLib\Application\ApplicationContainerInterface;
use CBLib\Input\InputInterface;

defined('CBLIB') or die();

/**
 * CBLib\Core\Dispatcher Class implementation
 * 
 */
class Dispatcher
{
	/**
	 * @var InputInterface
	 */
	protected $input;
	/**
	 * @var RouterInterface
	 */
	protected $router;
	/**
	 * @var ApplicationContainerInterface
	 */
	protected $di;

	/**
	 * Constructor
	 *
	 * @param  InputInterface                 $input   Input object
	 * @param  RouterInterface                $router  Router
	 * @param  ApplicationContainerInterface  $di      Application container dependency injector
	 */
	public function __construct( InputInterface $input, RouterInterface $router, ApplicationContainerInterface $di )
	{
		$this->input	=	$input;
		$this->router	=	$router;
		$this->di		=	$di;
	}
	/**
	 * Routes and executes the request:
	 * Handles a CBLib\input request and returns the corresponding CBLib\Output\Output in the Container
	 * using CBLib\RouterInterface and invoking a CBLib\Controller
	 * Raises an CBLib\Exception if task to do is not known or not authorized.
	 *
	 * @return  void
	 *
	 * @throws  \DomainException
	 */
	public function dispatch( )
	{
		// Get array( 'classname', 'methodname' ) of the Controller to call:
		$callable	=	$this->router->parseRoute( $this->input );

		if ( ! is_array( $callable ) )
		{
			throw new \DomainException( 'No route found for this CBLib request.', 404 );
		}

		$mainRoutingArgs	=	$this->router->getMainRoutingArgs();

		// Check if class exists, and if not, use default generic one:
		if ( ! class_exists( $callable[0] ) )
		{
			$callable[0]	=	'CBLib\Controller\Controller';
		}

		// Instantiate the Controller for that task:
		/** @var Controller  $controllerInstance */
		$controllerInstance	=	$this->di->get( $callable[0], array( 'options' => $mainRoutingArgs ) );

		if ( ! is_callable( array( $controllerInstance, 'dispatch' ) ) )
		{
			throw new \DomainException( 'No route found for this CBLib request.', 404 );
		}

		$controllerInstance->dispatch( $callable[1] );
	}
}
