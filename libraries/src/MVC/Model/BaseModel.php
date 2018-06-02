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
abstract class BaseModel extends CMSObject implements ModelInterface, StatefulModelInterface
{
	use StateBehaviorTrait;
	use LeagcyModelLoaderTrait;

	/**
	 * The model (base) name
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $name;

	/**
	 * The factory.
	 *
	 * @var    MVCFactoryInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $factory;

	/**
	 * Constructor
	 *
	 * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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
