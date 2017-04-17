<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 07.06.13 22:39 $
* @package CBLib\AhaWow
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


namespace CBLib\Controller;

use CBLib\Cms\CmsInterface;
use CBLib\Application\ApplicationContainerInterface;
use CBLib\Input\InputInterface;
use CBLib\Output\OutputInterface;
use CBLib\Registry\GetterInterface;

defined('CBLIB') or die();

/**
 * CBLib\HMVC\Controller Class implementation
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
	 * @var array
	 */
	protected $options;

	/**
	 * Constructor, sets the dependency-injection Container
	 *
	 * @param  InputInterface                 $input    Input object
	 * @param  OutputInterface                $output   Output object
	 * @param  CmsInterface                   $cms      Cms object
	 * @param  ApplicationContainerInterface  $di       DI Container
	 * @param  array                          $options  Main request options for dispatching
	 */
	public function __construct( InputInterface $input, OutputInterface $output, CmsInterface $cms, ApplicationContainerInterface $di, array $options )
	{
		$this->input			=	$input;
		$this->output			=	$output;
		$this->cms				=	$cms;
		$this->di				=	$di;
		$this->options			=	$options;

	}

	/**
	 * Dispatches the execution (and sets Output in Container $di)
	 *
	 * @param   string              $method
	 * @return  void
	 */
	public function dispatch( $method )
	{
		if ( is_callable( array( $this, $method ) ) ) {
			$this->$method();
		} else {
			$this->execute( $method );
		}
	}

	/**
	 * Handles an InputInterface\input request and returns the corresponding Output in the container
	 * Raises an Core\Exception if task to do is not known or not authorized.
	 *
	 * @param   string              $method
	 * @return  void
	 *
	 * @throws  \Exception
	 */
	public function execute( $method )
	{
		$route	=	array( 'option' => 'com_comprofiler',
						   'view' => $this->options['view'],
						   'action' => $method,
						   'method' => $this->input->get( 'act', 'edit', GetterInterface::COMMAND )
						 );

		/** @var \CBLib\AhaWow\Controller\Controller $ahaWowController */
		$ahaWowController	=	$this->di->get( 'CBLib\AhaWow\Controller\Controller', array( 'options' => $this->options ) );
		$ahaWowController->dispatchRoute( $route );
	}
}
