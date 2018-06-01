<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryServiceInterface;
use Joomla\CMS\Object\CMSObject;

/**
 * Base class for a Joomla Model
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class StateModel extends CMSObject implements ModelInterface, StateModelInterface
{
	use LeagcyModelLoaderTrait;

	/**
	 * Indicates if the internal state has been set
	 *
	 * @var    boolean
	 * @since  3.0
	 */
	protected $__state_set = null;

	/**
	 * The model (base) name
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $name;

	/**
	 * A state object
	 *
	 * @var    \JObject
	 * @since  3.0
	 */
	protected $state;

	/**
	 * The factory.
	 *
	 * @var    MVCFactoryInterface
	 * @since  4.0.0
	 */
	private $factory;

	/**
	 * Constructor
	 *
	 * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		// Set the view name
		if (empty($this->name))
		{
			if (array_key_exists('name', $config))
			{
				$this->name = $config['name'];
			}
			else
			{
				$this->name = $this->getName();
			}
		}

		// Set the model state
		if (array_key_exists('state', $config))
		{
			$this->state = $config['state'];
		}
		else
		{
			$this->state = new \JObject;
		}

		// Set the internal state marker - used to ignore setting state from the request
		if (!empty($config['ignore_request']))
		{
			$this->__state_set = true;
		}

		if ($factory)
		{
			$this->factory = $factory;
			return;
		}

		$component = Factory::getApplication()->bootComponent($this->option);

		if ($component instanceof MVCFactoryServiceInterface)
		{
			$this->factory = $component->createMVCFactory(Factory::getApplication());
		}
	}

	/**
	 * Method to get the model name
	 *
	 * The model name. By default parsed using the classname or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;

			if (!preg_match('/Model(.*)/i', get_class($this), $r))
			{
				throw new \Exception(\JText::_('JLIB_APPLICATION_ERROR_MODEL_GET_NAME'), 500);
			}

			$this->name = str_replace(['\\', 'model'], '', strtolower($r[1]));
		}

		return $this->name;
	}

	/**
	 * Method to get model state variables
	 *
	 * @param   string  $property  Optional parameter name
	 * @param   mixed   $default   Optional default value
	 *
	 * @return  mixed  The property where specified, the state object where omitted
	 *
	 * @since   3.0
	 */
	public function getState($property = null, $default = null)
	{
		if (!$this->__state_set)
		{
			// Protected method to auto-populate the model state.
			$this->populateState();

			// Set the model state set flag to true.
			$this->__state_set = true;
		}

		return $property === null ? $this->state : $this->state->get($property, $default);
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 *
	 * @since   3.0
	 */
	public function setState($property, $value = null)
	{
		return $this->state->set($property, $value);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return  void
	 *
	 * @note    Calling getState in this method will result in recursion.
	 * @since   3.0
	 */
	protected function populateState()
	{
	}

	/**
	 * Returns the internal MVC factory.
	 *
	 * @return  MVCFactoryInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getMVCFactory(): MVCFactoryInterface
	{
		return $this->factory;
	}
}
