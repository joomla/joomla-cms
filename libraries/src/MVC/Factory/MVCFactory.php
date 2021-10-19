<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\MVC\Factory;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryAwareInterface;
use Joomla\CMS\Form\FormFactoryAwareTrait;
use Joomla\CMS\MVC\Model\ModelInterface;
use Joomla\Input\Input;

/**
 * Factory to create MVC objects based on a namespace.
 *
 * @since  3.10.0
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
	 * The namespace must be like:
	 * Joomla\Component\Content
	 *
	 * @param   string  $namespace  The namespace
	 *
	 * @since   4.0.0
	 */
	public function __construct($namespace)
	{
		$this->namespace = $namespace;
	}

	/**
	 * Method to load and return a controller object.
	 *
	 * @param   string                   $name    The name of the controller
	 * @param   string                   $prefix  The controller prefix
	 * @param   array                    $config  The configuration array for the controller
	 * @param   CMSApplicationInterface  $app     The app
	 * @param   Input                    $input   The input
	 *
	 * @return  \Joomla\CMS\MVC\Controller\ControllerInterface
	 *
	 * @since   3.10.0
	 * @throws  \Exception
	 */
	public function createController($name, $prefix, array $config, CMSApplicationInterface $app, Input $input)
	{
		// Clean the parameters
		$name   = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		$className = $this->getClassName('Controller\\' . ucfirst($name) . 'Controller', $prefix);

		if (!$className)
		{
			return null;
		}

		$controller = new $className($config, $this, $app, $input);
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
	 * @return  ModelInterface  The model object
	 *
	 * @since   3.10.0
	 * @throws  \Exception
	 */
	public function createModel($name, $prefix = '', array $config = [])
	{
		// Clean the parameters
		$name   = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		if (!$prefix)
		{
			@trigger_error(
				sprintf(
					'Calling %s() without a prefix is deprecated.',
					__METHOD__
				),
				E_USER_DEPRECATED
			);

			$prefix = Factory::getApplication()->getName();
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
	 * @return  \Joomla\CMS\MVC\View\ViewInterface  The view object
	 *
	 * @since   3.10.0
	 * @throws  \Exception
	 */
	public function createView($name, $prefix = '', $type = '', array $config = [])
	{
		// Clean the parameters
		$name   = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);
		$type   = preg_replace('/[^A-Z0-9_]/i', '', $type);

		if (!$prefix)
		{
			@trigger_error(
				sprintf(
					'Calling %s() without a prefix is deprecated.',
					__METHOD__
				),
				E_USER_DEPRECATED
			);

			$prefix = Factory::getApplication()->getName();
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
	 * @since   3.10.0
	 * @throws  \Exception
	 */
	public function createTable($name, $prefix = '', array $config = [])
	{
		// Clean the parameters
		$name   = preg_replace('/[^A-Z0-9_]/i', '', $name);
		$prefix = preg_replace('/[^A-Z0-9_]/i', '', $prefix);

		if (!$prefix)
		{
			@trigger_error(
				sprintf(
					'Calling %s() without a prefix is deprecated.',
					__METHOD__
				),
				E_USER_DEPRECATED
			);

			$prefix = Factory::getApplication()->getName();
		}

		$className = $this->getClassName('Table\\' . ucfirst($name) . 'Table', $prefix)
			?: $this->getClassName('Table\\' . ucfirst($name) . 'Table', 'Administrator');

		if (!$className)
		{
			return null;
		}

		if (\array_key_exists('dbo', $config))
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
	 * @since   3.10.0
	 */
	protected function getClassName(string $suffix, string $prefix)
	{
		if (!$prefix)
		{
			$prefix = Factory::getApplication();
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
			// Ignore it
		}
	}
}
