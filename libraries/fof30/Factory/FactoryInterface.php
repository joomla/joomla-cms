<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Factory;

defined('_JEXEC') || die;

use FOF30\Container\Container;
use FOF30\Controller\Controller;
use FOF30\Dispatcher\Dispatcher;
use FOF30\Model\Model;
use FOF30\Toolbar\Toolbar;
use FOF30\TransparentAuthentication\TransparentAuthentication;
use FOF30\View\View;

/**
 * Interface for the MVC object factory
 */
interface FactoryInterface
{
	/**
	 * Public constructor for the factory object
	 *
	 * @param   Container  $container  The container we belong to
	 */
	function __construct(Container $container);

	/**
	 * Create a new Controller object
	 *
	 * @param   string  $viewName  The name of the view we're getting a Controller for.
	 * @param   array   $config    Optional MVC configuration values for the Controller object.
	 *
	 * @return  Controller
	 */
	function controller($viewName, array $config = []);

	/**
	 * Create a new Model object
	 *
	 * @param   string  $viewName  The name of the view we're getting a Model for.
	 * @param   array   $config    Optional MVC configuration values for the Model object.
	 *
	 * @return  Model
	 */
	function model($viewName, array $config = []);

	/**
	 * Create a new View object
	 *
	 * @param   string  $viewName  The name of the view we're getting a View object for.
	 * @param   string  $viewType  The type of the View object. By default it's "html".
	 * @param   array   $config    Optional MVC configuration values for the View object.
	 *
	 * @return  View
	 */
	function view($viewName, $viewType = 'html', array $config = []);

	/**
	 * Creates a new Toolbar
	 *
	 * @param   array  $config  The configuration values for the Toolbar object
	 *
	 * @return  Toolbar
	 */
	function toolbar(array $config = []);

	/**
	 * Creates a new Dispatcher
	 *
	 * @param   array  $config  The configuration values for the Dispatcher object
	 *
	 * @return  Dispatcher
	 */
	function dispatcher(array $config = []);

	/**
	 * Creates a new TransparentAuthentication handler
	 *
	 * @param   array  $config  The configuration values for the TransparentAuthentication object
	 *
	 * @return  TransparentAuthentication
	 */
	function transparentAuthentication(array $config = []);

	/**
	 * Creates a view template finder object for a specific View
	 *
	 * @param   View   $view    The view this view template finder will be attached to
	 * @param   array  $config  Configuration variables for the object
	 *
	 * @return  mixed
	 */
	function viewFinder(View $view, array $config = []);

	/**
	 * @return string
	 */
	public function getSection();

	/**
	 * @param   string  $section
	 */
	public function setSection($section);
}
