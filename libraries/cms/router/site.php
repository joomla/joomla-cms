<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Router
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Class to create and parse routes for the site application
 *
 * @package     Joomla.Libraries
 * @subpackage  Router
 * @since       1.5
 */
class JRouterSite extends JRouter
{
	/**
	 * Function to convert a route to an internal URI
	 *
	 * @param   JUri  &$uri  The uri.
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public function parse(&$uri)
	{
		$vars = array();

		// Get the application
		$app = JApplication::getInstance('site');

		if ($app->getCfg('force_ssl') == 2 && strtolower($uri->getScheme()) != 'https')
		{
			// Forward to https
			$uri->setScheme('https');
			$app->redirect((string) $uri);
		}

		// Get the path
		// Decode URL to convert punycode to unicode so that strings match when routing.
		$path = urldecode($uri->getPath());

		// Remove the base URI path.
		$path = substr_replace($path, '', 0, strlen(JUri::base(true)));

		// Check to see if a request to a specific entry point has been made.
		if (preg_match("#.*?\.php#u", $path, $matches))
		{
			// Get the current entry point path relative to the site path.
			$scriptPath = realpath($_SERVER['SCRIPT_FILENAME'] ? $_SERVER['SCRIPT_FILENAME'] : str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']));
			$relativeScriptPath = str_replace('\\', '/', str_replace(JPATH_SITE, '', $scriptPath));

			// If a php file has been found in the request path, check to see if it is a valid file.
			// Also verify that it represents the same file from the server variable for entry script.
			if (file_exists(JPATH_SITE . $matches[0]) && ($matches[0] == $relativeScriptPath))
			{
				// Remove the entry point segments from the request path for proper routing.
				$path = str_replace($matches[0], '', $path);
			}
		}

		// Identify format
		if ($this->_mode == JROUTER_MODE_SEF)
		{
			if ($app->getCfg('sef_suffix') && !(substr($path, -9) == 'index.php' || substr($path, -1) == '/'))
			{
				if ($suffix = pathinfo($path, PATHINFO_EXTENSION))
				{
					$vars['format'] = $suffix;
				}
			}
		}

		// Set the route
		$uri->setPath(trim($path, '/'));

		$vars += parent::parse($uri);

		return $vars;
	}

	/**
	 * Function to convert an internal URI to a route
	 *
	 * @param   string  $url  The internal URL
	 *
	 * @return  string  The absolute search engine friendly URL
	 *
	 * @since   1.5
	 */
	public function build($url)
	{
		$uri = parent::build($url);

		// Get the path data
		$route = $uri->getPath();

		// Add the suffix to the uri
		if ($this->_mode == JROUTER_MODE_SEF && $route)
		{
			$app = JApplication::getInstance('site');

			if ($app->getCfg('sef_suffix') && !(substr($route, -9) == 'index.php' || substr($route, -1) == '/'))
			{
				if ($format = $uri->getVar('format', 'html'))
				{
					$route .= '.' . $format;
					$uri->delVar('format');
				}
			}

			if ($app->getCfg('sef_rewrite'))
			{
				// Transform the route
				if ($route == 'index.php')
				{
					$route = '';
				}
				else
				{
					$route = str_replace('index.php/', '', $route);
				}
			}
		}

		// Add basepath to the uri
		$uri->setPath(JUri::base(true) . '/' . $route);

		return $uri;
	}

	/**
	 * Function to convert a raw route to an internal URI
	 *
	 * @param   JUri  &$uri  The raw route
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	protected function parseRawRoute(&$uri)
	{
		$vars = array();
		$app  = JApplication::getInstance('site');
		$menu = $app->getMenu(true);

		// Handle an empty URL (special case)
		if (!$uri->getVar('Itemid') && !$uri->getVar('option'))
		{
			$item = $menu->getDefault(JFactory::getLanguage()->getTag());

			if (!is_object($item))
			{
				// No default item set
				return $vars;
			}

			// Set the information in the request
			$vars = $item->query;

			// Get the itemid
			$vars['Itemid'] = $item->id;

			// Set the active menu item
			$menu->setActive($vars['Itemid']);

			return $vars;
		}

		// Get the variables from the uri
		$this->setVars($uri->getQuery(true));

		// Get the itemid, if it hasn't been set force it to null
		$this->setVar('Itemid', $app->input->getInt('Itemid', null));

		// Only an Itemid  OR if filter language plugin set? Get the full information from the itemid
		if (count($this->getVars()) == 1 || ($app->getLanguageFilter() && count($this->getVars()) == 2 ))
		{
			$item = $menu->getItem($this->getVar('Itemid'));

			if ($item !== null && is_array($item->query))
			{
				$vars = $vars + $item->query;
			}
		}

		// Set the active menu item
		$menu->setActive($this->getVar('Itemid'));

		return $vars;
	}

	/**
	 * Function to convert a sef route to an internal URI
	 *
	 * @param   JUri  &$uri  The sef URI
	 *
	 * @return  string  Internal URI
	 *
	 * @since   3.2
	 */
	protected function parseSefRoute(&$uri)
	{
		$app   = JApplication::getInstance('site');
		$menu  = $app->getMenu(true);
		$route = $uri->getPath();

		// Remove the suffix
		if ($this->_mode == JROUTER_MODE_SEF)
		{
			if ($app->getCfg('sef_suffix'))
			{
				if ($suffix = pathinfo($route, PATHINFO_EXTENSION))
				{
					$route = str_replace('.' . $suffix, '', $route);
				}
			}
		}

		// Get the variables from the uri
		$vars = $uri->getQuery(true);

		// Handle an empty URL (special case)
		if (empty($route))
		{
			// If route is empty AND option is set in the query, assume it's non-sef url, and parse apropriately
			if (isset($vars['option']) || isset($vars['Itemid']))
			{
				return $this->parseRawRoute($uri);
			}

			$item = $menu->getDefault(JFactory::getLanguage()->getTag());

			// If user not allowed to see default menu item then avoid notices
			if (is_object($item))
			{
				// Set the information in the request
				$vars = $item->query;

				// Get the itemid
				$vars['Itemid'] = $item->id;

				// Set the active menu item
				$menu->setActive($vars['Itemid']);
			}

			return $vars;
		}

		// Parse the application route
		$segments = explode('/', $route);

		if (count($segments) > 1 && $segments[0] == 'component')
		{
			$vars['option'] = 'com_' . $segments[1];
			$vars['Itemid'] = null;
			$route = implode('/', array_slice($segments, 2));
		}
		else
		{
			// Need to reverse the array (highest sublevels first)
			$items = array_reverse($menu->getMenu());

			$found           = false;
			$route_lowercase = JString::strtolower($route);
			$lang_tag        = JFactory::getLanguage()->getTag();

			foreach ($items as $item)
			{
				if (isset($item->language))
				{
					$item->language = trim($item->language);
				}

				// Get the length of the route
				$length = strlen($item->route);
				if ($length > 0 && JString::strpos($route_lowercase . '/', $item->route . '/') === 0
					&& $item->type != 'menulink' && (!$app->getLanguageFilter() || $item->language == '*'
					|| $item->language == $lang_tag))
				{
					// We have exact item for this language
					if ($item->language == $lang_tag)
					{
						$found = $item;
						break;
					}
					// Or let's remember an item for all languages
					elseif (!$found)
					{
						$found = $item;
					}
				}
			}

			if (!$found)
			{
				$found = $menu->getDefault($lang_tag);
			}
			else
			{
				$route = substr($route, strlen($found->route));

				if ($route)
				{
					$route = substr($route, 1);
				}
			}

			$vars['Itemid'] = $found->id;
			$vars['option'] = $found->component;
		}

		// Set the active menu item
		if (isset($vars['Itemid']))
		{
			$menu->setActive($vars['Itemid']);
		}

		// Set the variables
		$this->setVars($vars);

		// Parse the component route
		if (!empty($route) && isset($this->_vars['option']))
		{
			$segments = explode('/', $route);

			if (empty($segments[0]))
			{
				array_shift($segments);
			}

			// Handle component	route
			$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $this->_vars['option']);

			// Use the component routing handler if it exists
			$path = JPATH_SITE . '/components/' . $component . '/router.php';

			if (file_exists($path) && count($segments))
			{
				// Cheap fix on searches
				if ($component != 'com_search')
				{
					// Decode the route segments
					$segments = $this->decodeSegments($segments);
				}
				else
				{
					// Fix up search for URL
					$total = count($segments);

					for ($i = 0; $i < $total; $i++)
					{
						// Urldecode twice because it is encoded twice
						$segments[$i] = urldecode(urldecode(stripcslashes($segments[$i])));
					}
				}

				require_once $path;
				$function = substr($component, 4) . 'ParseRoute';
				$function = str_replace(array("-", "."), "", $function);
				$vars = $function($segments);

				$this->setVars($vars);
			}
		}
		else
		{
			// Set active menu item
			if ($item = $menu->getActive())
			{
				$vars = $item->query;
			}
		}

		return $vars;
	}

	/**
	 * Function to build a sef route
	 *
	 * @param   JUri  &$uri  The internal URL
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use buildSefRoute() instead
	 */
	protected function _buildSefRoute(&$uri)
	{
		$this->buildSefRoute($uri);
	}

	/**
	 * Function to build a sef route
	 *
	 * @param   JUri  &$uri  The uri
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function buildSefRoute(&$uri)
	{
		// Get the route
		$route = $uri->getPath();

		// Get the query data
		$query = $uri->getQuery(true);

		if (!isset($query['option']))
		{
			return;
		}

		$app  = JApplication::getInstance('site');
		$menu = $app->getMenu();

		// Build the component route
		$component = preg_replace('/[^A-Z0-9_\.-]/i', '', $query['option']);
		$tmp       = '';
		$itemID    = !empty($query['Itemid']) ? $query['Itemid'] : null;

		// Use the component routing handler if it exists
		$path = JPATH_SITE . '/components/' . $component . '/router.php';

		// Use the custom routing handler if it exists
		if (file_exists($path) && !empty($query))
		{
			require_once $path;
			$function = substr($component, 4) . 'BuildRoute';
			$function = str_replace(array("-", "."), "", $function);
			$parts    = $function($query);

			// Encode the route segments
			if ($component != 'com_search')
			{
				// Cheep fix on searches
				$parts = $this->encodeSegments($parts);
			}
			else
			{
				// Fix up search for URL
				$total = count($parts);

				for ($i = 0; $i < $total; $i++)
				{
					// Urlencode twice because it is decoded once after redirect
					$parts[$i] = urlencode(urlencode(stripcslashes($parts[$i])));
				}
			}

			$result = implode('/', $parts);
			$tmp    = ($result != "") ? $result : '';
		}

		// Build the application route
		$built = false;

		if (!empty($query['Itemid']))
		{
			$item = $menu->getItem($query['Itemid']);

			if (is_object($item) && $query['option'] == $item->component)
			{
				if (!$item->home || $item->language != '*')
				{
					$tmp = !empty($tmp) ? $item->route . '/' . $tmp : $item->route;
				}

				$built = true;
			}
		}

		if (empty($query['Itemid']) && !empty($itemID))
		{
			$query['Itemid'] = $itemID;
		}

		if (!$built)
		{
			$tmp = 'component/' . substr($query['option'], 4) . '/' . $tmp;
		}

		if ($tmp)
		{
			$route .= '/' . $tmp;
		}
		elseif ($route == 'index.php')
		{
			$route = '';
		}

		// Unset unneeded query information
		if (isset($item) && $query['option'] == $item->component)
		{
			unset($query['Itemid']);
		}

		unset($query['option']);

		// Set query again in the URI
		$uri->setQuery($query);
		$uri->setPath($route);
	}

	/**
	 * Process the parsed router variables based on custom defined rules
	 *
	 * @param   JUri  &$uri  The URI to parse
	 *
	 * @return  array  The array of processed URI variables
	 *
	 * @since   3.2
	 */
	protected function processParseRules(&$uri)
	{
		// Process the attached parse rules
		$vars = parent::processParseRules($uri);

		// Process the pagination support
		if ($this->_mode == JROUTER_MODE_SEF)
		{
			if ($start = $uri->getVar('start'))
			{
				$uri->delVar('start');
				$vars['limitstart'] = $start;
			}
		}

		return $vars;
	}

	/**
	 * Process the build uri query data based on custom defined rules
	 *
	 * @param   JUri  &$uri  The URI
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function processBuildRules(&$uri)
	{
		// Make sure any menu vars are used if no others are specified
		if (($this->_mode != JROUTER_MODE_SEF) && $uri->getVar('Itemid') && count($uri->getQuery(true)) == 2)
		{
			$app  = JApplication::getInstance('site');
			$menu = $app->getMenu();

			// Get the active menu item
			$itemid = $uri->getVar('Itemid');
			$item = $menu->getItem($itemid);

			if ($item)
			{
				$uri->setQuery($item->query);
			}

			$uri->setVar('Itemid', $itemid);
		}

		// Process the attached build rules
		parent::processBuildRules($uri);

		// Get the path data
		$route = $uri->getPath();

		if ($this->_mode == JROUTER_MODE_SEF && $route)
		{
			if ($limitstart = $uri->getVar('limitstart'))
			{
				$uri->setVar('start', (int) $limitstart);
				$uri->delVar('limitstart');
			}
		}

		$uri->setPath($route);
	}

	/**
	 * Create a uri based on a full or partial url string
	 *
	 * @param   string  $url  The URI
	 *
	 * @return  JUri
	 *
	 * @since   3.2
	 */
	protected function createURI($url)
	{
		// Create the URI
		$uri = parent::createURI($url);

		// Set URI defaults
		$app  = JApplication::getInstance('site');
		$menu = $app->getMenu();

		// Get the itemid form the URI
		$itemid = $uri->getVar('Itemid');

		if (is_null($itemid))
		{
			if ($option = $uri->getVar('option'))
			{
				$item  = $menu->getItem($this->getVar('Itemid'));

				if (isset($item) && $item->component == $option)
				{
					$uri->setVar('Itemid', $item->id);
				}
			}
			else
			{
				if ($option = $this->getVar('option'))
				{
					$uri->setVar('option', $option);
				}

				if ($itemid = $this->getVar('Itemid'))
				{
					$uri->setVar('Itemid', $itemid);
				}
			}
		}
		else
		{
			if (!$uri->getVar('option'))
			{
				if ($item = $menu->getItem($itemid))
				{
					$uri->setVar('option', $item->component);
				}
			}
		}

		return $uri;
	}
}
