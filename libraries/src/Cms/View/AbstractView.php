<?php
/**
 * @package     Joomla.Cms
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Cms\View;

defined('JPATH_PLATFORM') or die;

use Joomla\Cms\Model\Model;

/**
 * Base class for a Joomla View
 *
 * Class holding methods for displaying presentation data.
 *
 * @since  2.5.5
 */
class AbstractView extends \JObject
{
	/**
	 * The active document object
	 *
	 * @var    \JDocument
	 * @since  3.0
	 */
	public $document;

	/**
	 * The URL option for the component. It is usually passed by controller while it creates the view
	 *
	 * @var    string
	 * @since  3.0
	 */
	protected $option = null;

	/**
	 * The name of the view
	 *
	 * @var    array
	 * @since  3.0
	 */
	protected $_name = null;

	/**
	 * Registered models
	 *
	 * @var    array
	 * @since  3.0
	 */
	protected $_models = array();

	/**
	 * The default model
	 *
	 * @var	   string
	 * @since  3.0
	 */
	protected $_defaultModel = null;

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.
	 *                          name: the name (optional) of the view (defaults to the view class name suffix).
	 *                          charset: the character set to use for display
	 *                          escape: the name (optional) of the function to use for escaping strings
	 *                          base_path: the parent path (optional) of the views directory (defaults to the component folder)
	 *                          template_plath: the path (optional) of the layout directory (defaults to base_path + /views/ + view name
	 *                          helper_path: the path (optional) of the helper files (defaults to base_path + /helpers/)
	 *                          layout: the layout (optional) to use to display the view
	 *
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		// Set the view name
		if (empty($this->_name))
		{
			if (array_key_exists('name', $config))
			{
				$this->_name = $config['name'];
			}
			else
			{
				$this->_name = $this->getName();
			}
		}

		// Set the component name if passed
		if (!empty($config['option']))
		{
			$this->option = $config['option'];
		}
	}

	/**
	 * Method to get data from a registered model or a property of the view
	 *
	 * @param   string  $property  The name of the method to call on the model or the property to get
	 * @param   string  $default   The name of the model to reference or the default value [optional]
	 *
	 * @return  mixed  The return value of the method
	 *
	 * @since   3.0
	 */
	public function get($property, $default = null)
	{
		// If $model is null we use the default model
		if (is_null($default))
		{
			$model = $this->_defaultModel;
		}
		else
		{
			$model = strtolower($default);
		}

		// First check to make sure the model requested exists
		if (isset($this->_models[$model]))
		{
			// Model exists, let's build the method name
			$method = 'get' . ucfirst($property);

			// Does the method exist?
			if (method_exists($this->_models[$model], $method))
			{
				// The method exists, let's call it and return what we get
				return $this->_models[$model]->$method();
			}
		}

		// Degrade to \JObject::get
		return parent::get($property, $default);
	}

	/**
	 * Method to get the model object
	 *
	 * @param   string  $name  The name of the model (optional)
	 *
	 * @return  Model  The model object
	 *
	 * @since   3.0
	 */
	public function getModel($name = null)
	{
		if ($name === null)
		{
			$name = $this->_defaultModel;
		}

		return $this->_models[strtolower($name)];
	}

	/**
	 * Method to add a model to the view.  We support a multiple model single
	 * view system by which models are referenced by classname.  A caveat to the
	 * classname referencing is that any classname prepended by \JModel will be
	 * referenced by the name without \JModel, eg. \JModelCategory is just
	 * Category.
	 *
	 * @param   Model    $model    The model to add to the view.
	 * @param   boolean  $default  Is this the default model?
	 *
	 * @return  Model  The added model.
	 *
	 * @since   3.0
	 */
	public function setModel($model, $default = false)
	{
		$name = strtolower($model->getName());
		$this->_models[$name] = $model;

		if ($default)
		{
			$this->_defaultModel = $name;
		}

		return $model;
	}

	/**
	 * Method to get the view name
	 *
	 * The model name by default parsed using the classname, or it can be set
	 * by passing a $config['name'] in the class constructor
	 *
	 * @return  string  The name of the model
	 *
	 * @since   3.0
	 * @throws  \Exception
	 */
	public function getName()
	{
		if (empty($this->_name))
		{
			$reflection = new \ReflectionClass($this);

			if ($viewNamespace = $reflection->getNamespaceName())
			{
				$pos = strrpos($viewNamespace, '\\');

				if ($pos !== false)
				{
					$this->_name = strtolower(substr($viewNamespace, $pos));
				}
			}
			else
			{
				$className = get_class($this);
				$viewPos   = strpos($className, 'View');

				if ($viewPos != false)
				{
					$this->_name = strtolower(substr($className, $viewPos + 4));
				}
			}

			if (empty($this->_name))
			{
				throw new \Exception(\JText::_('JLIB_APPLICATION_ERROR_VIEW_GET_NAME'), 500);
			}
		}

		return $this->_name;
	}
}
