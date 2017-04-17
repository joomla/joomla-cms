<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10/29/13 8:51 PM $
* @package CBLib\AhaWow
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\AhaWow\Controller;

use CBLib\AhaWow\AutoLoaderXml;
use CBLib\Cms\CmsInterface;
use CBLib\Controller\ControllerInterface;
use CBLib\Application\ApplicationContainerInterface;
use CBLib\Database\Table\TableInterface;
use CBLib\Input\InputInterface;
use CBLib\Output\OutputInterface;
use CBLib\Registry\ParamsInterface;

defined('CBLIB') or die();

/**
 * CBLib\AhaWow\Controller Class implementation
 * 
 */
class Controller implements ControllerInterface
{
	/**
	 * @var InputInterface
	 */
	protected $input;
	/**
	 * @var OutputInterface
	 */
	protected $output;
	/**
	 * @var CmsInterface
	 */
	protected $cms;
	/**
	 * @var ApplicationContainerInterface
	 */
	protected $di;
	/**
	 * @var string[]
	 */
	protected $options;
	/**
	 * @var string[]
	 */
	protected $getParams;
	/**
	 * @var TableInterface|ParamsInterface
	 */
	protected $data;

	/**
	 * Constructor, sets the dependency-injection Container
	 *
	 * @param  InputInterface                  $input      Input object
	 * @param  OutputInterface                 $output     Output object
	 * @param  CmsInterface                    $cms        Cms object
	 * @param  ApplicationContainerInterface   $di         DI Container
	 * @param  array                           $options    Main request options for dispatching
	 * @param  array                           $getParams  [optional] Get params for form target of form
	 * @param  TableInterface|ParamsInterface  $data       The data to render
	 */
	public function __construct( InputInterface $input, OutputInterface $output, CmsInterface $cms, ApplicationContainerInterface $di, array $options, array $getParams = array(), $data = null )
	{
		$this->input			=	$input;
		$this->output			=	$output;
		$this->cms				=	$cms;
		$this->di				=	$di;
		$this->options			=	$options;
		$this->getParams		=	$getParams;
		$this->data				=	$data;

		$extensionName			=	$this->cms->getExtensionName();
		$extensionPath			=	$this->cms->getFolderPath( $this->cms->getClientId() );

		// Make sure it's shared:
		/** @see AutoLoaderXml */
		$this->di->set( 'CBLib\AhaWow\AutoLoaderXml', null, true );
		/** @var AutoLoaderXml $autoLoaderXml::_construct() */
		$autoLoaderXml			=	$this->di->get( 'CBLib\AhaWow\AutoLoaderXml' );
		$autoLoaderXml->registerMap( $extensionName, $extensionPath . '/xmlcb/controllers/frontcontroller.xml' );
	}


	/**
	 * Dispatches the execution (and sets Output in Container $di)
	 *
	 * @param   string  $method
	 * @return  void
	 *
	 * @throws  \Exception
	 */
	public function dispatch( $method )
	{
		throw new \Exception( 'Classic controller dispatch method not yet implemented for XML' );
	}
	/**
	 * Dispatches the execution (and sets Output in Container $di)
	 *
	 * @param   array  $route  Route of method to execute
	 * @return  void
	 */
	public final function dispatchRoute( array $route )
	{
		$this->execute( $route, $this->output );
	}

	/**
	 * Handles an InputInterface\input request and returns the corresponding Output in the container
	 * Raises an Core\Exception if task to do is not known or not authorized.
	 *
	 * @param  array            $route   Route of method to execute
	 * @param  OutputInterface  $output  Output object
	 * @return void
	 */
	public function execute( array $route, OutputInterface $output )
	{
		/** @var ActionController $actionController */
		/** @see CBLib\AhaWow\Controller\ActionController::__construct() */
		$actionController				=	$this->di->get( 'CBLib\AhaWow\Controller\ActionController',
			array( 'input' => $this->input, 'output' => $output, 'options' => $this->options, 'getParams' => $this->getParams, 'data' => $this->data ) );
		$actionController->setGetParams( $this->getParams );
		$actionController->setOptions( $this->options );
		$actionController->setData( $this->data );
		$outputString					=	$actionController->handleAction( $route );

		$output->append( $outputString );
	}
}
