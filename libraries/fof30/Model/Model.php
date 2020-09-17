<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Model;

defined('_JEXEC') || die;

use FOF30\Container\Container;
use FOF30\Input\Input;
use FOF30\Model\Exception\CannotGetName;
use Joomla\CMS\Filter\InputFilter;
use RuntimeException;
use stdClass;

/**
 * Class Model
 *
 * A generic MVC model implementation
 *
 * @property-read  Input $input  The input object (magic __get returns the Input from the Container)
 */
class Model
{
	/**
	 * Should I save the model's state in the session?
	 *
	 * @var   boolean
	 */
	protected $_savestate = true;

	/**
	 * Should we ignore request data when trying to get state data not already set in the Model?
	 *
	 * @var bool
	 */
	protected $_ignoreRequest = false;

	/**
	 * The model (base) name
	 *
	 * @var    string
	 */
	protected $name;

	/**
	 * A state object
	 *
	 * @var    string
	 */
	protected $state;

	/**
	 * Are the state variables already set?
	 *
	 * @var   boolean
	 */
	protected $_state_set = false;

	/**
	 * The container attached to the model
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * The state key hash returned by getHash(). This is typically something like "com_foobar.example." (note the dot
	 * at the end). Always use getHash to get it and setHash to set it.
	 *
	 * @var null|string
	 */
	private $stateHash = null;

	/**
	 * Public class constructor
	 *
	 * You can use the $config array to pass some configuration values to the object:
	 *
	 * state            stdClass|array. The state variables of the Model.
	 * use_populate        Boolean. When true the model will set its state from populateState() instead of the request.
	 * ignore_request    Boolean. When true getState will not automatically load state data from the request.
	 *
	 * @param   Container  $container  The configuration variables to this model
	 * @param   array      $config     Configuration values for this model
	 */
	public function __construct(Container $container, array $config = [])
	{
		$this->container = $container;

		// Set the model's name from $config
		if (isset($config['name']))
		{
			$this->name = $config['name'];
		}

		// If $config['name'] is not set, auto-detect the model's name
		$this->name = $this->getName();

		// Do we have a configured state hash? Since 3.1.2.
		if (isset($config['hash']) && !empty($config['hash']))
		{
			$this->setHash($config['hash']);
		}
		elseif (isset($config['hash_view']) && !empty($config['hash_view']))
		{
			$this->getHash($config['hash_view']);
		}

		// Set the model state
		if (array_key_exists('state', $config))
		{
			if (is_object($config['state']))
			{
				$this->state = $config['state'];
			}
			elseif (is_array($config['state']))
			{
				$this->state = (object) $config['state'];
			}
			// Protect vs malformed state
			else
			{
				$this->state = new stdClass();
			}
		}
		else
		{
			$this->state = new stdClass();
		}

		// Set the internal state marker
		if (!empty($config['use_populate']))
		{
			$this->_state_set = true;
		}

		// Set the internal state marker
		if (!empty($config['ignore_request']))
		{
			$this->_ignoreRequest = true;
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
	 * @throws  RuntimeException  If it's impossible to get the name
	 */
	public function getName()
	{
		if (empty($this->name))
		{
			$r = null;

			if (!preg_match('/(.*)\\\\Model\\\\(.*)/i', get_class($this), $r))
			{
				throw new CannotGetName;
			}

			$this->name = $r[2];
		}

		return $this->name;
	}

	/**
	 * Get a filtered state variable
	 *
	 * @param   string  $key          The state variable's name
	 * @param   mixed   $default      The default value to return if it's not already set
	 * @param   string  $filter_type  The filter type to use
	 *
	 * @return  mixed  The state variable's contents
	 */
	public function getState($key = null, $default = null, $filter_type = 'raw')
	{
		if (empty($key))
		{
			return $this->internal_getState();
		}

		// Get the savestate status
		$value = $this->internal_getState($key);

		// Value is not found in the internal state
		if (is_null($value))
		{
			// Can I fetch it from the request?
			if (!$this->_ignoreRequest)
			{
				$value = $this->container->platform->getUserStateFromRequest($this->getHash() . $key, $key, $this->input, $value, 'none', $this->_savestate);

				// Did I get any useful value from the request?
				if (is_null($value))
				{
					return $default;
				}
			}
			// Nope! Let's return the default value
			else
			{
				return $default;
			}
		}

		if (strtoupper($filter_type) == 'RAW')
		{
			return $value;
		}
		else
		{
			$filter = new InputFilter();

			return $filter->clean($value, $filter_type);
		}
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 */
	public function setState($property, $value = null)
	{
		if (is_null($this->state))
		{
			$this->state = new stdClass();
		}

		return $this->state->$property = $value;
	}

	/**
	 * Returns a unique hash for each view, used to prefix the state variables to allow us to retrieve them from the
	 * state later on. If it's not already set (with setHash) it will be set in the form com_something.myModel. If you
	 * pass a non-empty $viewName then if it's not already set it will be instead set in the form of
	 * com_something.viewName.myModel  which is useful when you are reusing models in multiple views and want to avoid
	 * state bleedover among views.
	 *
	 * Also see the hash and hash_view parameters in the constructor's options.
	 *
	 * @return  string
	 */
	public function getHash($viewName = null)
	{
		if (is_null($this->stateHash))
		{
			$this->stateHash = ucfirst($this->container->componentName) . '.';

			if (!empty($viewName))
			{
				$this->stateHash .= $viewName . '.';
			}

			$this->stateHash .= $this->getName() . '.';

		}

		return $this->stateHash;
	}

	/**
	 * Sets the unique hash to prefix the state variables. The hash is cleaned according to the 'CMD' input filtering,
	 * must end in a dot (if not a dot is added automatically) and cannot be empty.
	 *
	 * @param   string  $hash
	 *
	 * @return  void
	 *
	 * @see   self::getHash()
	 */
	public function setHash($hash)
	{
		// Clean the hash, it has to conform to 'CMD' filtering
		$tempInput = new Input(['hash' => $hash]);
		$hash      = $tempInput->getCmd('hash', null);

		if (empty($hash))
		{
			return;
		}

		if (substr($hash, -1) == '_')
		{
			$hash = substr($hash, 0, -1);
		}

		if (substr($hash, -1) != '.')
		{
			$hash .= '.';
		}

		$this->stateHash = $hash;
	}

	/**
	 * Clears the model state, but doesn't touch the internal lists of records,
	 * record tables or record id variables. To clear these values, please use
	 * reset().
	 *
	 * @return  static
	 */
	public function clearState()
	{
		$this->state = new stdClass();

		return $this;
	}

	/**
	 * Clones the model object and returns the clone
	 *
	 * @return  static
	 */
	public function getClone()
	{
		$clone = clone($this);

		return $clone;
	}

	/**
	 * Returns a reference to the model's container
	 *
	 * @return Container
	 */
	public function getContainer()
	{
		return $this->container;
	}

	/**
	 * Magic getter; allows to use the name of model state keys as properties. Also handles magic properties:
	 * $this->input  mapped to $this->container->input
	 *
	 * @param   string  $name  The state variable key
	 *
	 * @return  static
	 */
	public function __get($name)
	{
		// Handle $this->input
		if ($name == 'input')
		{
			return $this->container->input;
		}

		return $this->getState($name);
	}

	/**
	 * Magic setter; allows to use the name of model state keys as properties
	 *
	 * @param   string  $name   The state variable key
	 * @param   mixed   $value  The state variable value
	 *
	 * @return  static
	 */
	public function __set($name, $value)
	{
		return $this->setState($name, $value);
	}

	/**
	 * Magic caller; allows to use the name of model state keys as methods to
	 * set their values.
	 *
	 * @param   string  $name       The state variable key
	 * @param   mixed   $arguments  The state variable contents
	 *
	 * @return  static
	 */
	public function __call($name, $arguments)
	{
		$arg1 = array_shift($arguments);
		$this->setState($name, $arg1);

		return $this;
	}

	/**
	 * Sets the model state auto-save status. By default the model is set up to
	 * save its state to the session.
	 *
	 * @param   boolean  $newState  True to save the state, false to not save it.
	 *
	 * @return  static
	 */
	public function savestate($newState)
	{
		$this->_savestate = $newState ? true : false;

		return $this;
	}

	/**
	 * Public setter for the _savestate variable. Set it to true to save the state
	 * of the Model in the session.
	 *
	 * @return  static
	 */
	public function populateSavestate()
	{
		if (is_null($this->_savestate))
		{
			$savestate = $this->input->getInt('savestate', -999);

			if ($savestate == -999)
			{
				$savestate = true;
			}
			$this->savestate($savestate);
		}
	}

	/**
	 * Gets the ignore request flag. When false, getState() will try to populate state variables not already set from
	 * same-named state variables in the request.
	 *
	 * @return boolean
	 */
	public function getIgnoreRequest()
	{
		return $this->_ignoreRequest;
	}

	/**
	 * Sets the ignore request flag. When false, getState() will try to populate state variables not already set from
	 * same-named state variables in the request.
	 *
	 * @param   boolean  $ignoreRequest
	 *
	 * @return  static
	 */
	public function setIgnoreRequest($ignoreRequest)
	{
		$this->_ignoreRequest = $ignoreRequest;

		return $this;
	}

	/**
	 * Returns a temporary instance of the model. Please note that this returns a _clone_ of the model object, not the
	 * original object. The new object is set up to not save its stats, ignore the request when getting state variables
	 * and comes with an empty state.
	 *
	 * @return  static
	 */
	public function tmpInstance()
	{
		return $this->getClone()->savestate(false)->setIgnoreRequest(true)->clearState();
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
	 */
	protected function populateState()
	{
	}

	/**
	 * Triggers an object-specific event. The event runs both locally –if a suitable method exists– and through the
	 * object's behaviours dispatcher and Joomla! plugin system. Neither handler is expected to return anything (return
	 * values are ignored). If you want to mark an error and cancel the event you have to raise an exception.
	 *
	 * EXAMPLE
	 * Component: com_foobar, Object name: item, Event: onBeforeSomething, Arguments: array(123, 456)
	 * The event calls:
	 * 1. $this->onBeforeSomething(123, 456)
	 * 2. $his->behavioursDispatcher->trigger('onBeforeSomething', array(&$this, 123, 456))
	 * 3. Joomla! plugin event onComFoobarModelItemBeforeSomething($this, 123, 456)
	 *
	 * @param   string  $event      The name of the event, typically named onPredicateVerb e.g. onBeforeKick
	 * @param   array   $arguments  The arguments to pass to the event handlers
	 *
	 * @return  void
	 */
	protected function triggerEvent($event, array $arguments = [])
	{
		// If there is an object method for this event, call it
		if (method_exists($this, $event))
		{
			switch (count($arguments))
			{
				case 0:
					$this->{$event}();
					break;
				case 1:
					$this->{$event}($arguments[0]);
					break;
				case 2:
					$this->{$event}($arguments[0], $arguments[1]);
					break;
				case 3:
					$this->{$event}($arguments[0], $arguments[1], $arguments[2]);
					break;
				case 4:
					$this->{$event}($arguments[0], $arguments[1], $arguments[2], $arguments[3]);
					break;
				case 5:
					$this->{$event}($arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4]);
					break;
				default:
					call_user_func_array([$this, $event], $arguments);
					break;
			}
		}

		// All other event handlers live outside this object, therefore they need to be passed a reference to this
		// objects as the first argument.
		array_unshift($arguments, $this);

		// Trigger the object's behaviours dispatcher, if such a thing exists
		if (property_exists($this, 'behavioursDispatcher') && method_exists($this->behavioursDispatcher, 'trigger'))
		{
			$this->behavioursDispatcher->trigger($event, $arguments);
		}

		// Prepare to run the Joomla! plugins now.

		// If we have an "on" prefix for the event (e.g. onFooBar) remove it and stash it for later.
		$prefix = '';

		if (substr($event, 0, 2) == 'on')
		{
			$prefix = 'on';
			$event  = substr($event, 2);
		}

		// Get the component/model prefix for the event
		$prefix .= 'Com' . ucfirst($this->container->bareComponentName) . 'Model';
		$prefix .= ucfirst($this->getName());

		// The event name will be something like onComFoobarItemsBeforeSomething
		$event = $prefix . $event;

		// Call the Joomla! plugins
		$this->container->platform->runPlugins($event, $arguments);
	}

	/**
	 * Method to get model state variables
	 *
	 * @param   string  $property  Optional parameter name
	 * @param   mixed   $default   Optional default value
	 *
	 * @return  object  The property where specified, the state object where omitted
	 */
	private function internal_getState($property = null, $default = null)
	{
		if (!$this->_state_set)
		{
			// Protected method to auto-populate the model state.
			$this->populateState();

			// Set the model state set flag to true.
			$this->_state_set = true;
		}

		if (is_null($property))
		{
			return $this->state;
		}
		else
		{
			if (property_exists($this->state, $property))
			{
				return $this->state->$property;
			}
			else
			{
				return $default;
			}
		}
	}
}
