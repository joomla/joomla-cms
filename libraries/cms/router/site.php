<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\String\StringHelper;

/**
 * Class to create and parse routes for the site application
 *
 * @since  1.5
 */
class JRouterSite extends JRouter
{
	/**
	 * Component-router objects
	 *
	 * @var    array
	 * @since  3.3
	 */
	protected $componentRouters = array();

	/**
	 * Current JApplication-Object
	 *
	 * @var    JApplicationCms
	 * @since  3.4
	 */
	protected $app;

	/**
	 * Current JMenu-Object
	 *
	 * @var    JMenu
	 * @since  3.4
	 */
	protected $menu;

	/**
	 * Class constructor
	 *
	 * @param   JApplicationCms  $app   JApplicationCms Object
	 * @param   JMenu            $menu  JMenu object
	 *
	 * @since   3.4
	 */
	public function __construct(JApplicationCms $app = null, JMenu $menu = null)
	{
		$this->app  = $app ?: JApplicationCms::getInstance('site');
		$this->menu = $menu ?: $this->app->getMenu();

		// Add core rules
		if ($this->app->get('force_ssl') == 2)
		{
			$this->attachParseRule(array($this, 'parseCheckSSL'), self::PROCESS_BEFORE);
		}

		$this->attachParseRule(array($this, 'parseInit'), self::PROCESS_BEFORE);
		$this->attachBuildRule(array($this, 'buildInit'), self::PROCESS_BEFORE);
		$this->attachBuildRule(array($this, 'buildComponentPreprocess'), self::PROCESS_BEFORE);

		if ($this->app->get('sef', 1))
		{
			if ($this->app->get('sef_suffix'))
			{
				$this->attachParseRule(array($this, 'parseFormat'), self::PROCESS_BEFORE);
				$this->attachBuildRule(array($this, 'buildFormat'), self::PROCESS_AFTER);
			}

			$this->attachParseRule(array($this, 'parseSefRoute'), self::PROCESS_DURING);
			$this->attachBuildRule(array($this, 'buildSefRoute'), self::PROCESS_DURING);
			$this->attachParseRule(array($this, 'parsePaginationData'), self::PROCESS_AFTER);
			$this->attachBuildRule(array($this, 'buildPaginationData'), self::PROCESS_AFTER);

			if ($this->app->get('sef_rewrite'))
			{
				$this->attachBuildRule(array($this, 'buildRewrite'), self::PROCESS_AFTER);
			}
		}

		$this->attachParseRule(array($this, 'parseRawRoute'), self::PROCESS_DURING);
		$this->attachBuildRule(array($this, 'buildBase'), self::PROCESS_AFTER);
	}

	/**
	 * Force to SSL
	 *
	 * @param   JRouterSite  &$router  Router object
	 * @param   JUri         &$uri     URI object to process
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function parseCheckSSL(&$router, &$uri)
	{
		if (strtolower($uri->getScheme()) != 'https')
		{
			// Forward to https
			$uri->setScheme('https');
			$this->app->redirect((string) $uri, 301);
		}
	}

	/**
	 * Do some initial cleanup before parsing the URL
	 *
	 * @param   JRouterSite  &$router  Router object
	 * @param   JUri         &$uri     URI object to process
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function parseInit(&$router, &$uri)
	{
		// Get the path
		// Decode URL to convert percent-encoding to unicode so that strings match when routing.
		$path = urldecode($uri->getPath());

		// Remove the base URI path.
		$path = substr_replace($path, '', 0, strlen(JUri::base(true)));

		// Check to see if a request to a specific entry point has been made.
		if (preg_match("#.*?\.php#u", $path, $matches))
		{
			// Get the current entry point path relative to the site path.
			$scriptPath         = realpath($_SERVER['SCRIPT_FILENAME'] ?: str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']));
			$relativeScriptPath = str_replace('\\', '/', str_replace(JPATH_SITE, '', $scriptPath));

			// If a php file has been found in the request path, check to see if it is a valid file.
			// Also verify that it represents the same file from the server variable for entry script.
			if (file_exists(JPATH_SITE . $matches[0]) && ($matches[0] == $relativeScriptPath))
			{
				// Remove the entry point segments from the request path for proper routing.
				$path = str_replace($matches[0], '', $path);
			}
		}

		// Set the route
		$uri->setPath(trim($path, '/'));
	}

	/**
	 * Parse the format of the request
	 *
	 * @param   JRouterSite  &$router  Router object
	 * @param   JUri         &$uri     URI object to process
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function parseFormat(&$router, &$uri)
	{
		$route = $uri->getPath();

		// Identify format
		if (!(substr($route, -9) == 'index.php' || substr($route, -1) == '/') && $suffix = pathinfo($route, PATHINFO_EXTENSION))
		{
			$uri->setVar('format', $suffix);
			$route = str_replace('.' . $suffix, '', $route);
			$uri->setPath($route);
		}
	}

	/**
	 * Convert a sef route to an internal URI
	 *
	 * @param   JRouterSite  &$router  Router object
	 * @param   JUri         &$uri     URI object to process
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function parseSefRoute(&$router, &$uri)
	{
		$route = $uri->getPath();

		// If the URL is empty, we handle this in the non-SEF parse URL
		if (empty($route))
		{
			return;
		}

		// Parse the application route
		$segments = explode('/', $route);

		if (count($segments) > 1 && $segments[0] == 'component')
		{
			$uri->setVar('option', 'com_' . $segments[1]);
			$uri->setVar('Itemid', null);
			$route = implode('/', array_slice($segments, 2));
		}
		else
		{
			// Get menu items.
			$items = $this->menu->getMenu();

			$found           = false;
			$route_lowercase = StringHelper::strtolower($route);
			$lang_tag        = $this->app->getLanguage()->getTag();

			// Iterate through all items and check route matches.
			foreach ($items as $item)
			{
				if ($item->route && StringHelper::strpos($route_lowercase . '/', $item->route . '/') === 0 && $item->type != 'menulink')
				{
					// Usual method for non-multilingual site.
					if (!$this->app->getLanguageFilter())
					{
						// Exact route match. We can break iteration because exact item was found.
						if ($item->route == $route_lowercase)
						{
							$found = $item;
							break;
						}

						// Partial route match. Item with highest level takes priority.
						if (!$found || $found->level < $item->level)
						{
							$found = $item;
						}
					}
					// Multilingual site.
					elseif ($item->language == '*' || $item->language == $lang_tag)
					{
						// Exact route match.
						if ($item->route == $route_lowercase)
						{
							$found = $item;

							// Break iteration only if language is matched.
							if ($item->language == $lang_tag)
							{
								break;
							}
						}

						// Partial route match. Item with highest level or same language takes priority.
						if (!$found || $found->level < $item->level || $item->language == $lang_tag)
						{
							$found = $item;
						}
					}
				}
			}

			if (!$found)
			{
				$found = $this->menu->getDefault($lang_tag);
			}
			else
			{
				$route = trim(substr($route, strlen($found->route)), '/');
			}

			if ($found)
			{
				if ($found->type == 'alias')
				{
					$newItem = $this->menu->getItem($found->params->get('aliasoptions'));

					if ($newItem)
					{
						$found->query     = array_merge($found->query, $newItem->query);
						$found->component = $newItem->component;
					}
				}

				$uri->setVar('Itemid', $found->id);
				$uri->setVar('option', $found->component);
			}
		}

		// Set the active menu item
		if ($uri->getVar('Itemid'))
		{
			$this->menu->setActive($uri->getVar('Itemid'));
		}

		// Parse the component route
		if (!empty($route) && $uri->getVar('option'))
		{
			$segments = explode('/', $route);

			if (count($segments))
			{
				// Handle component route
				$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $uri->getVar('option'));
				$crouter = $this->getComponentRouter($component);
				$uri->setQuery(array_merge($uri->getQuery(true), $crouter->parse($segments)));
			}

			$route = implode('/', $segments);
		}

		$uri->setPath($route);
	}

	/**
	 * Convert a raw route to an internal URI
	 *
	 * @param   JRouterSite  &$router  Router object
	 * @param   JUri         &$uri     URI object to process
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function parseRawRoute(&$router, &$uri)
	{
		if ($uri->getVar('Itemid'))
		{
			$item = $this->menu->getItem($uri->getVar('Itemid'));
		}
		else
		{
			$item = $this->menu->getDefault($this->app->getLanguage()->getTag());
		}

		if ($item && $item->type == 'alias')
		{
			$newItem = $this->menu->getItem($item->params->get('aliasoptions'));

			if ($newItem)
			{
				$item->query     = array_merge($item->query, $newItem->query);
				$item->component = $newItem->component;
			}
		}

		if (is_object($item))
		{
			// Set the active menu item
			$this->menu->setActive($item->id);

			$uri->setVar('Itemid', $item->id);
			$uri->setQuery(array_merge($item->query, $uri->getQuery(true)));
		}
	}

	/**
	 * Convert limits for pagination
	 *
	 * @param   JRouterSite  &$router  Router object
	 * @param   JUri         &$uri     URI object to process
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function parsePaginationData(&$router, &$uri)
	{
		// Process the pagination support
		if ($uri->getVar('start'))
		{
			$uri->setVar('limitstart', $uri->getVar('start'));
			$uri->delVar('start');
		}
	}

	/**
	 * Do some initial processing for building a URL
	 *
	 * @param   JRouterSite  &$router  Router object
	 * @param   JUri         &$uri     URI object to process
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function buildInit(&$router, &$uri)
	{
		$itemid = $uri->getVar('Itemid');

		// If no Itemid and option given, merge in the current requests data
		if (!$itemid && !$uri->getVar('option'))
		{
			$uri->setQuery(array_merge($this->getVars(), $uri->getQuery(true)));
		}

		// If Itemid is given, but no option, set the option from the menu item
		if ($itemid && !$uri->getVar('option'))
		{
			if ($item = $this->menu->getItem($itemid))
			{
				$uri->setVar('option', $item->component);
			}
		}
	}

	/**
	 * Run the component preprocess method
	 *
	 * @param   JRouterSite  &$router  Router object
	 * @param   JUri         &$uri     URI object to process
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function buildComponentPreprocess(&$router, &$uri)
	{
		// Get the query data
		$query = $uri->getQuery(true);

		if (!isset($query['option']))
		{
			return;
		}

		$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $query['option']);
		$crouter   = $this->getComponentRouter($component);
		$query     = $crouter->preprocess($query);

		// Make sure any menu vars are used if no others are specified
		if (isset($query['Itemid'])
			&& (count($query) == 2 || (count($query) == 3 && isset($query['lang']))))
		{
			// Get the active menu item
			$item = $this->menu->getItem($query['Itemid']);
			$query = array_merge($item->query, $query);
		}

		$uri->setQuery($query);
	}

	/**
	 * Build the SEF route
	 *
	 * @param   JRouterSite  &$router  Router object
	 * @param   JUri         &$uri     URI object to process
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function buildSefRoute(&$router, &$uri)
	{
		// Get the route
		$route = $uri->getPath();

		// Get the query data
		$query = $uri->getQuery(true);

		if (!isset($query['option']))
		{
			return;
		}

		// Build the component route
		$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $query['option']);
		$itemID    = !empty($query['Itemid']) ? $query['Itemid'] : null;
		$crouter   = $this->getComponentRouter($component);
		$parts     = $crouter->build($query);
		$tmp       = trim(implode('/', $parts));

		if (empty($query['Itemid']) && !empty($itemID))
		{
			$query['Itemid'] = $itemID;
		}

		// Build the application route
		if (isset($query['Itemid']) && $item = $this->menu->getItem($query['Itemid']))
		{
			if (is_object($item) && $query['option'] == $item->component)
			{
				if (!$item->home)
				{
					$tmp = !empty($tmp) ? $item->route . '/' . $tmp : $item->route;
				}

				unset($query['Itemid']);
			}
		}
		else
		{
			$tmp = 'component/' . substr($query['option'], 4) . '/' . $tmp;
		}

		$route .= '/' . $tmp;

		// Unset unneeded query information
		unset($query['option']);

		// Set query again in the URI
		$uri->setQuery($query);
		$uri->setPath(trim($route, '/'));
	}

	/**
	 * Convert limits for pagination
	 *
	 * @param   JRouterSite  &$router  Router object
	 * @param   JUri         &$uri     URI object to process
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function buildPaginationData(&$router, &$uri)
	{
		if ($uri->getVar('limitstart'))
		{
			$uri->setVar('start', (int) $uri->getVar('limitstart'));
			$uri->delVar('limitstart');
		}
	}

	/**
	 * Build the format of the request
	 *
	 * @param   JRouterSite  &$router  Router object
	 * @param   JUri         &$uri     URI object to process
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function buildFormat(&$router, &$uri)
	{
		$route = $uri->getPath();

		// Identify format
		if (!(substr($route, -9) == 'index.php' || substr($route, -1) == '/') && $format = $uri->getVar('format', 'html'))
		{
			$route .= '.' . $format;
			$uri->setPath($route);
			$uri->delVar('format');
		}
	}

	/**
	 * Create a uri based on a full or partial URL string
	 *
	 * @param   JRouterSite  &$router  Router object
	 * @param   JUri         &$uri     URI object to process
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function buildRewrite(&$router, &$uri)
	{
		// Get the path data
		$route = $uri->getPath();

		// Transform the route
		if ($route == 'index.php')
		{
			$route = '';
		}
		else
		{
			$route = str_replace('index.php/', '', $route);
		}

		$uri->setPath($route);
	}

	/**
	 * Add the basepath to the URI
	 *
	 * @param   JRouterSite  &$router  Router object
	 * @param   JUri         &$uri     URI object to process
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	public function buildBase(&$router, &$uri)
	{
		// Add basepath to the uri
		$uri->setPath(JUri::base(true) . '/' . $uri->getPath());
	}

	/**
	 * Get component router
	 *
	 * @param   string  $component  Name of the component including com_ prefix
	 *
	 * @return  JComponentRouterInterface  Component router
	 *
	 * @since   3.3
	 */
	public function getComponentRouter($component)
	{
		if (!isset($this->componentRouters[$component]))
		{
			$compname = ucfirst(substr($component, 4));
			$class = $compname . 'Router';

			if (!class_exists($class))
			{
				// Use the component routing handler if it exists
				$path = JPATH_SITE . '/components/' . $component . '/router.php';

				// Use the custom routing handler if it exists
				if (file_exists($path))
				{
					require_once $path;
				}
			}

			if (class_exists($class))
			{
				$reflection = new ReflectionClass($class);

				if (in_array('Joomla\\Cms\\Component\\Router\\RouterInterface', $reflection->getInterfaceNames()))
				{
					$this->componentRouters[$component] = new $class($this->app, $this->menu);
				}
			}

			if (!isset($this->componentRouters[$component]))
			{
				$this->componentRouters[$component] = new JComponentRouterLegacy($compname);
			}
		}

		return $this->componentRouters[$component];
	}

	/**
	 * Set a router for a component
	 *
	 * @param   string  $component  Component name with com_ prefix
	 * @param   object  $router     Component router
	 *
	 * @return  boolean  True if the router was accepted, false if not
	 *
	 * @since   3.3
	 */
	public function setComponentRouter($component, $router)
	{
		$reflection = new ReflectionClass($router);

		if (in_array('Joomla\\Cms\\Component\\Router\\RouterInterface', $reflection->getInterfaceNames()))
		{
			$this->componentRouters[$component] = $router;

			return true;
		}
		else
		{
			return false;
		}
	}
}
