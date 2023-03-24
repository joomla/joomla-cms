<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Router;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Component\Router\RouterInterface;
use Joomla\CMS\Component\Router\RouterLegacy;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Menu\AbstractMenu;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class to create and parse routes for the site application
 *
 * @since  1.5
 */
class SiteRouter extends Router
{
    /**
     * Component-router objects
     *
     * @var    array
     *
     * @since  3.3
     */
    protected $componentRouters = [];

    /**
     * @var    CMSApplication
     *
     * @since  3.4
     */
    protected $app;

    /**
     * Current Menu-Object
     *
     * @var    AbstractMenu
     *
     * @since  3.4
     */
    protected $menu;

    /**
     * Class constructor
     *
     * @param   CMSApplication  $app   Application Object
     * @param   AbstractMenu    $menu  Menu object
     *
     * @since   3.4
     */
    public function __construct(CMSApplication $app = null, AbstractMenu $menu = null)
    {
        $this->app  = $app ?: Factory::getContainer()->get(SiteApplication::class);
        $this->menu = $menu ?: $this->app->getMenu();

        // Add core rules
        if ($this->app->get('force_ssl') === 2) {
            $this->attachParseRule([$this, 'parseCheckSSL'], self::PROCESS_BEFORE);
        }

        $this->attachParseRule([$this, 'parseInit'], self::PROCESS_BEFORE);
        $this->attachBuildRule([$this, 'buildInit'], self::PROCESS_BEFORE);
        $this->attachBuildRule([$this, 'buildComponentPreprocess'], self::PROCESS_BEFORE);

        if ($this->app->get('sef', 1)) {
            if ($this->app->get('sef_suffix')) {
                $this->attachParseRule([$this, 'parseFormat'], self::PROCESS_BEFORE);
                $this->attachBuildRule([$this, 'buildFormat'], self::PROCESS_AFTER);
            }

            $this->attachParseRule([$this, 'parseSefRoute'], self::PROCESS_DURING);
            $this->attachBuildRule([$this, 'buildSefRoute'], self::PROCESS_DURING);
            $this->attachParseRule([$this, 'parsePaginationData'], self::PROCESS_AFTER);
            $this->attachBuildRule([$this, 'buildPaginationData'], self::PROCESS_AFTER);

            if ($this->app->get('sef_rewrite')) {
                $this->attachBuildRule([$this, 'buildRewrite'], self::PROCESS_AFTER);
            }
        }

        $this->attachParseRule([$this, 'parseRawRoute'], self::PROCESS_DURING);
        $this->attachBuildRule([$this, 'buildBase'], self::PROCESS_AFTER);
    }

    /**
     * Force to SSL
     *
     * @param   Router  &$router  Router object
     * @param   Uri     &$uri     URI object to process
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function parseCheckSSL(&$router, &$uri)
    {
        if (strtolower($uri->getScheme()) !== 'https') {
            // Forward to https
            $uri->setScheme('https');
            $this->app->redirect((string) $uri, 301);
        }
    }

    /**
     * Do some initial cleanup before parsing the URL
     *
     * @param   SiteRouter  &$router  Router object
     * @param   Uri         &$uri     URI object to process
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function parseInit(&$router, &$uri)
    {
        // Get the path
        // Decode URL to convert percent-encoding to unicode so that strings match when routing.
        $path = urldecode($uri->getPath());

        /**
         * In some environments (e.g. CLI we can't form a valid base URL). In this case we catch the exception thrown
         * by URI and set an empty base URI for further work.
         * @todo: This should probably be handled better
         */
        try {
            $baseUri = Uri::base(true);
        } catch (\RuntimeException $e) {
            $baseUri = '';
        }

        // Remove the base URI path.
        $path = substr_replace($path, '', 0, \strlen($baseUri));

        // Check to see if a request to a specific entry point has been made.
        if (preg_match("#.*?\.php#u", $path, $matches)) {
            // Get the current entry point path relative to the site path.
            $scriptPath         = realpath($_SERVER['SCRIPT_FILENAME'] ?: str_replace('\\\\', '\\', $_SERVER['PATH_TRANSLATED']));
            $relativeScriptPath = str_replace('\\', '/', str_replace(JPATH_SITE, '', $scriptPath));

            // If a php file has been found in the request path, check to see if it is a valid file.
            // Also verify that it represents the same file from the server variable for entry script.
            if (is_file(JPATH_SITE . $matches[0]) && ($matches[0] === $relativeScriptPath)) {
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
     * @param   SiteRouter  &$router  Router object
     * @param   Uri         &$uri     URI object to process
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function parseFormat(&$router, &$uri)
    {
        $route = $uri->getPath();

        // Identify format
        if (!(substr($route, -9) === 'index.php' || substr($route, -1) === '/') && $suffix = pathinfo($route, PATHINFO_EXTENSION)) {
            $uri->setVar('format', $suffix);
            $route = str_replace('.' . $suffix, '', $route);
            $uri->setPath($route);
        }
    }

    /**
     * Convert a sef route to an internal URI
     *
     * @param   SiteRouter  &$router  Router object
     * @param   Uri         &$uri     URI object to process
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function parseSefRoute(&$router, &$uri)
    {
        $route = $uri->getPath();

        // If the URL is empty, we handle this in the non-SEF parse URL
        if (empty($route)) {
            return;
        }

        // Parse the application route
        $segments = explode('/', $route);

        if (\count($segments) > 1 && $segments[0] === 'component') {
            $uri->setVar('option', 'com_' . $segments[1]);
            $uri->setVar('Itemid', null);
            $route = implode('/', \array_slice($segments, 2));
        } else {
            // Get menu items.
            $items    = $this->menu->getItems(['parent_id', 'access'], [1, null]);
            $lang_tag = $this->app->getLanguage()->getTag();
            $found   = null;

            foreach ($segments as $segment) {
                $matched = false;

                foreach ($items as $item) {
                    if (
                        $item->alias == $segment
                        && (!$this->app->getLanguageFilter()
                        || ($item->language === '*'
                        || $item->language === $lang_tag))
                    ) {
                        $found = $item;
                        $matched = true;
                        $items = $item->getChildren();
                        break;
                    }
                }

                if (!$matched) {
                    break;
                }
            }

            // Menu links are not valid URLs. Find the first parent that isn't a menulink
            if ($found && $found->type === 'menulink') {
                while ($found->hasParent() && $found->type === 'menulink') {
                    $found = $found->getParent();
                }

                if ($found->type === 'menulink') {
                    $found = null;
                }
            }

            if (!$found) {
                $found = $this->menu->getDefault($lang_tag);
            } else {
                $route = trim(substr($route, \strlen($found->route)), '/');
            }

            if ($found) {
                if ($found->type === 'alias') {
                    $newItem = $this->menu->getItem($found->getParams()->get('aliasoptions'));

                    if ($newItem) {
                        $found->query     = array_merge($found->query, $newItem->query);
                        $found->component = $newItem->component;
                    }
                }

                $uri->setVar('Itemid', $found->id);
                $uri->setVar('option', $found->component);
            }
        }

        // Set the active menu item
        if ($uri->getVar('Itemid')) {
            $this->menu->setActive($uri->getVar('Itemid'));
        }

        // Parse the component route
        if (!empty($route) && $uri->getVar('option')) {
            $segments = explode('/', $route);

            if (\count($segments)) {
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
     * @param   SiteRouter  &$router  Router object
     * @param   Uri         &$uri     URI object to process
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function parseRawRoute(&$router, &$uri)
    {
        if ($uri->getVar('Itemid')) {
            $item = $this->menu->getItem($uri->getVar('Itemid'));
        } else {
            $item = $this->menu->getDefault($this->app->getLanguage()->getTag());
        }

        if ($item && $item->type === 'alias') {
            $newItem = $this->menu->getItem($item->getParams()->get('aliasoptions'));

            if ($newItem) {
                $item->query     = array_merge($item->query, $newItem->query);
                $item->component = $newItem->component;
            }
        }

        if (\is_object($item)) {
            // Set the active menu item
            $this->menu->setActive($item->id);

            $uri->setVar('Itemid', $item->id);
            $uri->setQuery(array_merge($item->query, $uri->getQuery(true)));
        }
    }

    /**
     * Convert limits for pagination
     *
     * @param   SiteRouter  &$router  Router object
     * @param   Uri         &$uri     URI object to process
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function parsePaginationData(&$router, &$uri)
    {
        // Process the pagination support
        $start = $uri->getVar('start');

        if ($start !== null) {
            $uri->setVar('limitstart', $uri->getVar('start'));
            $uri->delVar('start');
        }
    }

    /**
     * Do some initial processing for building a URL
     *
     * @param   SiteRouter  &$router  Router object
     * @param   Uri         &$uri     URI object to process
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function buildInit(&$router, &$uri)
    {
        $itemid = $uri->getVar('Itemid');

        // If no Itemid and option given, merge in the current requests data
        if (!$itemid && !$uri->getVar('option')) {
            $uri->setQuery(array_merge($this->getVars(), $uri->getQuery(true)));
        }

        // If Itemid is given, but no option, set the option from the menu item
        if ($itemid && !$uri->getVar('option')) {
            if ($item = $this->menu->getItem($itemid)) {
                $uri->setVar('option', $item->component);
            }
        }
    }

    /**
     * Run the component preprocess method
     *
     * @param   SiteRouter  &$router  Router object
     * @param   Uri         &$uri     URI object to process
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function buildComponentPreprocess(&$router, &$uri)
    {
        // Get the query data
        $query = $uri->getQuery(true);

        if (!isset($query['option'])) {
            return;
        }

        $component = preg_replace('/[^A-Z0-9_\.-]/i', '', $query['option']);
        $crouter   = $this->getComponentRouter($component);
        $query     = $crouter->preprocess($query);

        // Make sure any menu vars are used if no others are specified
        if (
            isset($query['Itemid'])
            && (\count($query) === 2 || (\count($query) === 3 && isset($query['lang'])))
        ) {
            // Get the active menu item
            $item = $this->menu->getItem($query['Itemid']);

            if ($item !== null) {
                $query = array_merge($item->query, $query);
            }
        }

        $uri->setQuery($query);
    }

    /**
     * Build the SEF route
     *
     * @param   SiteRouter  &$router  Router object
     * @param   Uri         &$uri     URI object to process
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function buildSefRoute(&$router, &$uri)
    {
        // Get the query data
        $query = $uri->getQuery(true);

        if (!isset($query['option'])) {
            return;
        }

        // Get Menu Item
        $item = empty($query['Itemid']) ? null : $this->menu->getItem($query['Itemid']);

        // Build the component route
        $component = preg_replace('/[^A-Z0-9_\.-]/i', '', $query['option']);
        $crouter   = $this->getComponentRouter($component);
        $parts     = $crouter->build($query);
        $tmp       = trim(implode('/', $parts));

        // Build the application route
        if ($item !== null && $query['option'] === $item->component) {
            if (!$item->home) {
                $tmp = $tmp ? $item->route . '/' . $tmp : $item->route;
            }

            unset($query['Itemid']);
        } else {
            $tmp = 'component/' . substr($query['option'], 4) . '/' . $tmp;
        }

        // Get the route
        if ($tmp) {
            $uri->setPath($uri->getPath() . '/' . $tmp);
        }

        // Unset unneeded query information
        unset($query['option']);

        // Set query again in the URI
        $uri->setQuery($query);
    }

    /**
     * Convert limits for pagination
     *
     * @param   SiteRouter  &$router  Router object
     * @param   Uri         &$uri     URI object to process
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function buildPaginationData(&$router, &$uri)
    {
        $limitstart = $uri->getVar('limitstart');

        if ($limitstart !== null) {
            $uri->setVar('start', (int) $uri->getVar('limitstart'));
            $uri->delVar('limitstart');
        }
    }

    /**
     * Build the format of the request
     *
     * @param   SiteRouter  &$router  Router object
     * @param   Uri         &$uri     URI object to process
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function buildFormat(&$router, &$uri)
    {
        $route = $uri->getPath();

        // Identify format
        if (!(substr($route, -9) === 'index.php' || substr($route, -1) === '/') && $format = $uri->getVar('format', 'html')) {
            $route .= '.' . $format;
            $uri->setPath($route);
            $uri->delVar('format');
        }
    }

    /**
     * Create a uri based on a full or partial URL string
     *
     * @param   SiteRouter  &$router  Router object
     * @param   Uri         &$uri     URI object to process
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function buildRewrite(&$router, &$uri)
    {
        // Get the path data
        $route = $uri->getPath();

        // Transform the route
        if ($route === 'index.php') {
            $route = '';
        } else {
            $route = str_replace('index.php/', '', $route);
        }

        $uri->setPath($route);
    }

    /**
     * Add the basepath to the URI
     *
     * @param   SiteRouter  &$router  Router object
     * @param   Uri         &$uri     URI object to process
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function buildBase(&$router, &$uri)
    {
        // Add frontend basepath to the uri
        $uri->setPath(Uri::root(true) . '/' . $uri->getPath());
    }

    /**
     * Get component router
     *
     * @param   string  $component  Name of the component including com_ prefix
     *
     * @return  RouterInterface  Component router
     *
     * @since   3.3
     */
    public function getComponentRouter($component)
    {
        if (!isset($this->componentRouters[$component])) {
            $componentInstance = $this->app->bootComponent($component);

            if ($componentInstance instanceof RouterServiceInterface) {
                $this->componentRouters[$component] = $componentInstance->createRouter($this->app, $this->menu);
            }

            if (!isset($this->componentRouters[$component])) {
                $this->componentRouters[$component] = new RouterLegacy(ucfirst(substr($component, 4)));
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
        $reflection = new \ReflectionClass($router);

        if (\in_array('Joomla\\CMS\\Component\\Router\\RouterInterface', $reflection->getInterfaceNames())) {
            $this->componentRouters[$component] = $router;

            return true;
        } else {
            return false;
        }
    }
}
