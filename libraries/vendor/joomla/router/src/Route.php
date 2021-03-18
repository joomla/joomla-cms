<?php
/**
 * Part of the Joomla Framework Router Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Router;

use SuperClosure\SerializableClosure;

/**
 * An object representing a route definition.
 *
 * @since  __DEPLOY_VERSION__
 */
class Route implements \Serializable
{
	/**
	 * The controller which handles this route
	 *
	 * @var    mixed
	 * @since  __DEPLOY_VERSION__
	 */
	private $controller;

	/**
	 * The default variables defined by the route
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $defaults = [];

	/**
	 * The HTTP methods this route supports
	 *
	 * @var    string[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $methods;

	/**
	 * The route pattern to use for matching
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $pattern;

	/**
	 * The path regex this route processes
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	private $regex;

	/**
	 * The variables defined by the route
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $routeVariables = [];

	/**
	 * An array of regex rules keyed using the route variables
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	private $rules = [];

	/**
	 * Constructor.
	 *
	 * @param   array   $methods     The HTTP methods this route supports
	 * @param   string  $pattern     The route pattern to use for matching
	 * @param   mixed   $controller  The controller which handles this route
	 * @param   array   $rules       An array of regex rules keyed using the route variables
	 * @param   array   $defaults    The default variables defined by the route
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(array $methods, string $pattern, $controller, array $rules = [], array $defaults = [])
	{
		$this->setMethods($methods);
		$this->setPattern($pattern);
		$this->setController($controller);
		$this->setRules($rules);
		$this->setDefaults($defaults);
	}

	/**
	 * Parse the route's pattern to extract the named variables and build a proper regular expression for use when parsing the routes.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function buildRegexAndVarList(): void
	{
		// Sanitize and explode the pattern.
		$pattern = explode('/', trim(parse_url($this->getPattern(), PHP_URL_PATH), ' /'));

		// Prepare the route variables
		$vars = [];

		// Initialize regular expression
		$regex = [];

		// Loop on each segment
		foreach ($pattern as $segment)
		{
			if ($segment == '*')
			{
				// Match a splat with no variable.
				$regex[] = '.*';
			}
			elseif (isset($segment[0]) && $segment[0] == '*')
			{
				// Match a splat and capture the data to a named variable.
				$vars[]  = substr($segment, 1);
				$regex[] = '(.*)';
			}
			elseif (isset($segment[0]) && $segment[0] == '\\' && $segment[1] == '*')
			{
				// Match an escaped splat segment.
				$regex[] = '\*' . preg_quote(substr($segment, 2));
			}
			elseif ($segment == ':')
			{
				// Match an unnamed variable without capture.
				$regex[] = '([^/]*)';
			}
			elseif (isset($segment[0]) && $segment[0] == ':')
			{
				// Match a named variable and capture the data.
				$varName = substr($segment, 1);
				$vars[]  = $varName;

				// Use the regex in the rules array if it has been defined.
				$regex[] = array_key_exists($varName, $this->getRules()) ? '(' . $this->getRules()[$varName] . ')' : '([^/]*)';
			}
			elseif (isset($segment[0]) && $segment[0] == '\\' && $segment[1] == ':')
			{
				// Match a segment with an escaped variable character prefix.
				$regex[] = preg_quote(substr($segment, 1));
			}
			else
			{
				// Match the standard segment.
				$regex[] = preg_quote($segment);
			}
		}

		$this->setRegex(\chr(1) . '^' . implode('/', $regex) . '$' . \chr(1));
		$this->setRouteVariables($vars);
	}

	/**
	 * Retrieve the controller which handles this route
	 *
	 * @return  mixed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Retrieve the default variables defined by the route
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDefaults(): array
	{
		return $this->defaults;
	}

	/**
	 * Retrieve the HTTP methods this route supports
	 *
	 * @return  string[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getMethods(): array
	{
		return $this->methods;
	}

	/**
	 * Retrieve the route pattern to use for matching
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getPattern(): string
	{
		return $this->pattern;
	}

	/**
	 * Retrieve the path regex this route processes
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getRegex(): string
	{
		if (!$this->regex)
		{
			$this->buildRegexAndVarList();
		}

		return $this->regex;
	}

	/**
	 * Retrieve the variables defined by the route
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getRouteVariables(): array
	{
		if (!$this->regex)
		{
			$this->buildRegexAndVarList();
		}

		return $this->routeVariables;
	}

	/**
	 * Retrieve the regex rules keyed using the route variables
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getRules(): array
	{
		return $this->rules;
	}

	/**
	 * Set the controller which handles this route
	 *
	 * @param   mixed  $controller  The controller which handles this route
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setController($controller): self
	{
		$this->controller = $controller;

		return $this;
	}

	/**
	 * Set the default variables defined by the route
	 *
	 * @param   array  $defaults  The default variables defined by the route
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setDefaults(array $defaults): self
	{
		$this->defaults = $defaults;

		return $this;
	}

	/**
	 * Set the HTTP methods this route supports
	 *
	 * @param   array  $methods  The HTTP methods this route supports
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setMethods(array $methods): self
	{
		$this->methods = $this->methods = array_map('strtoupper', $methods);

		return $this;
	}

	/**
	 * Set the route pattern to use for matching
	 *
	 * @param   string  $pattern  The route pattern to use for matching
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setPattern(string $pattern): self
	{
		$this->pattern = $pattern;

		$this->setRegex('');
		$this->setRouteVariables([]);

		return $this;
	}

	/**
	 * Set the path regex this route processes
	 *
	 * @param   string  $regex  The path regex this route processes
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setRegex(string $regex): self
	{
		$this->regex = $regex;

		return $this;
	}

	/**
	 * Set the variables defined by the route
	 *
	 * @param   array  $routeVariables  The variables defined by the route
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setRouteVariables(array $routeVariables): self
	{
		$this->routeVariables = $routeVariables;

		return $this;
	}

	/**
	 * Set the regex rules keyed using the route variables
	 *
	 * @param   array  $rules  The rules defined by the route
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setRules(array $rules): self
	{
		$this->rules = $rules;

		return $this;
	}

	/**
	 * Serialize the route.
	 *
	 * @return  string  The serialized route.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function serialize()
	{
		return serialize($this->__serialize());
	}

	/**
	 * Serialize the route.
	 *
	 * @return  array  The data to be serialized
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __serialize()
	{
		$controller = $this->getController();

		if ($controller instanceof \Closure)
		{
			if (!class_exists(SerializableClosure::class))
			{
				throw new \RuntimeException(
					\sprintf(
						'Cannot serialize the route for pattern "%s" because the controller is a Closure. Install the "jeremeamia/superclosure" package to serialize Closures.',
						$this->getPattern()
					)
				);
			}

			$controller = new SerializableClosure($controller);
		}

		return [
			'controller'     => $controller,
			'defaults'       => $this->getDefaults(),
			'methods'        => $this->getMethods(),
			'pattern'        => $this->getPattern(),
			'regex'          => $this->getRegex(),
			'routeVariables' => $this->getRouteVariables(),
			'rules'          => $this->getRules(),
		];
	}

	/**
	 * Unserialize the route.
	 *
	 * @param   string  $serialized  The serialized route.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public function unserialize($serialized)
	{
		$this->__unserialize(unserialize($serialized));
	}

	/**
	 * Unserialize the route.
	 *
	 * @param   array  $data  The serialized route.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __unserialize(array $data)
	{
		$this->controller     = $data['controller'];
		$this->defaults       = $data['defaults'];
		$this->methods        = $data['methods'];
		$this->pattern        = $data['pattern'];
		$this->regex          = $data['regex'];
		$this->routeVariables = $data['routeVariables'];
		$this->rules          = $data['rules'];
	}
}
