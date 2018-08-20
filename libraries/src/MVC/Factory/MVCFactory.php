<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\MVC\Factory;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryAwareInterface;
use Joomla\CMS\Form\FormFactoryAwareTrait;
use Joomla\Input\Input;

/**
 * Factory to create MVC objects based on a namespace.
 *
 * @since  4.0.0
 */
class MVCFactory implements MVCFactoryInterface, FormFactoryAwareInterface
{
	use FormFactoryAwareTrait;

	/**
	 * The namespace to create the objects from.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	private $namespace;

	/**
	 * The application.
	 *
	 * @var    CMSApplicationInterface
	 * @since  4.0.0
	 */
	private $application;

	/**
	 * The namespace must be like:
	 * Joomla\Component\Content
	 *
	 * @param   string                   $namespace    The namespace
	 * @param   CMSApplicationInterface  $application  The application
	 *
	 * @since   4.0.0
	 */
	public function __construct($namespace, CMSApplicationInterface $application)
	{
		$this->namespace   = $namespace;
		$this->application = $application;
	}

	/**
	 * Method to load and return a controller object.
	 *
	 * @param   string                   $name    The name of the view.
	 * @param   string                   $prefix  Optional view prefix.
	 * @param   array                    $config  Optional configuration array for the view.
	 * @param   CMSApplicationInterface  $app     The app
	 * @param   Input                    $input   The input
	 *
	 * @return  \Joomla\CMS\MVC\Controller\ControllerInterface
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function createController($name, $prefix = '', array $config = [], CMSApplicationInterface $app = null, Input $input = null)
	{
		// Clean the parameters
		$name   = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		$className = $this->getClassName('Controller\\' . ucfirst($name) . 'Controller', $prefix);

		if (!$className)
		{
			return null;
		}

		$controller = new $className($config, $this, $app ?: $this->application, $input ?: $this->application->input);
		$this->setFormFactoryOnObject($controller);

		return $controller;
	}

	/**
	 * Method to load and return a model object.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  Optional model prefix.
	 * @param   array   $config  Optional configuration array for the model.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model object
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function createModel($name, $prefix = '', array $config = [])
	{
		// Clean the parameters
		$name   = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		// When the front uses a back end model
		if (!$prefix && !empty($config['base_path']) && strpos($config['base_path'], '/administrator/') !== false)
		{
			$prefix = 'Administrator';
		}

		$className = $this->getClassName('Model\\' . ucfirst($name) . 'Model', $prefix);

		if (!$className)
		{
			return null;
		}

		$model = new $className($config, $this);
		$this->setFormFactoryOnObject($model);

		return $model;
	}

	/**
	 * Method to load and return a view object.
	 *
	 * @param   string  $name    The name of the view.
	 * @param   string  $prefix  Optional view prefix.
	 * @param   string  $type    Optional type of view.
	 * @param   array   $config  Optional configuration array for the view.
	 *
	 * @return  \Joomla\CMS\MVC\View\AbstractView  The view object
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function createView($name, $prefix = '', $type = '', array $config = [])
	{
		// Clean the parameters
		$name   = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);
		$type   = preg_replace('/[^A-Z0-9_]/i', '', $type);

		// When the front uses a back end view
		if (!$prefix && !empty($config['base_path']) && strpos($config['base_path'], '/administrator/') !== false)
		{
			$prefix = 'Administrator';
		}

		$className = $this->getClassName('View\\' . ucfirst($name) . '\\' . ucfirst($type) . 'View', $prefix);

		if (!$className)
		{
			return null;
		}

		$view = new $className($config);
		$this->setFormFactoryOnObject($view);

		return $view;
	}

	/**
	 * Method to load and return a table object.
	 *
	 * @param   string  $name    The name of the table.
	 * @param   string  $prefix  Optional table prefix.
	 * @param   array   $config  Optional configuration array for the table.
	 *
	 * @return  \Joomla\CMS\Table\Table  The table object
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function createTable($name, $prefix = '', array $config = [])
	{
		// Clean the parameters
		$name   = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		$className = $this->getClassName('Table\\' . ucfirst($name) . 'Table', $prefix)
			?: $this->getClassName('Table\\' . ucfirst($name) . 'Table', 'Administrator');

		if (!$className)
		{
			return null;
		}

		if (array_key_exists('dbo', $config))
		{
			$db = $config['dbo'];
		}
		else
		{
			$db = Factory::getDbo();
		}

		return new $className($db);
	}

	/**
	 * Returns a standard classname, if the class doesn't exist null is returned.
	 *
	 * @param   string  $suffix  The suffix
	 * @param   string  $prefix  The prefix
	 *
	 * @return  string|null  The class name
	 *
	 * @since   4.0.0
	 */
	private function getClassName(string $suffix, string $prefix)
	{
		if (!$prefix)
		{
			$prefix = $this->application->getName();
		}

		$className = trim($this->namespace, '\\') . '\\' . ucfirst($prefix) . '\\' . $suffix;

		if (!class_exists($className))
		{
			return null;
		}

		return $className;
	}

	/**
	 * Sets the internal form factory on the given object.
	 *
	 * @param   object  $object  The object
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	private function setFormFactoryOnObject($object)
	{
		if (!$object instanceof FormFactoryAwareInterface)
		{
			return;
		}

		try
		{
			$object->setFormFactory($this->getFormFactory());
		}
		catch (\UnexpectedValueException $e)
		{
		}
	}
}
