<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Router;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Router\Exception\RouteNotFoundException;
use Joomla\CMS\Uri\Uri;
use Joomla\Router\Route;
use Joomla\Router\Router;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! API Router class
 *
 * @since  4.0.0
 */
class ApiRouter extends Router
{
    /**
     * The application object
     *
     * @var    CMSApplicationInterface
     * @since  4.0.0
     */
    protected $app;

    /**
     * Constructor.
     *
     * @param   CMSApplicationInterface  $app   The application object
     * @param   array                    $maps  An optional array of route maps
     *
     * @since   1.0
     */
    public function __construct(CMSApplicationInterface $app, array $maps = [])
    {
        $this->app = $app;

        parent::__construct($maps);
    }

    /**
     * Creates routes map for CRUD
     *
     * @param   string  $baseName    The base name of the component.
     * @param   string  $controller  The name of the controller that contains CRUD functions.
     * @param   array   $defaults    An array of default values that are used when the URL is matched.
     * @param   bool    $publicGets  Allow the public to make GET requests.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function createCRUDRoutes($baseName, $controller, $defaults = [], $publicGets = false)
    {
        $getDefaults = array_merge(['public' => $publicGets], $defaults);

        $routes = [
            new Route(['GET'], $baseName, $controller . '.displayList', [], $getDefaults),
            new Route(['GET'], $baseName . '/:id', $controller . '.displayItem', ['id' => '(\d+)'], $getDefaults),
            new Route(['POST'], $baseName, $controller . '.add', [], $defaults),
            new Route(['PATCH'], $baseName . '/:id', $controller . '.edit', ['id' => '(\d+)'], $defaults),
            new Route(['DELETE'], $baseName . '/:id', $controller . '.delete', ['id' => '(\d+)'], $defaults),
        ];

        $this->addRoutes($routes);
    }

    /**
     * Parse the given route and return the name of a controller mapped to the given route.
     *
     * @param   string  $method  Request method to match. One of GET, POST, PUT, DELETE, HEAD, OPTIONS, TRACE or PATCH
     *
     * @return  array   An array containing the controller and the matched variables.
     *
     * @since   4.0.0
     * @throws  \InvalidArgumentException
     */
    public function parseApiRoute($method = 'GET')
    {
        $method = strtoupper($method);

        $validMethods = ["GET", "POST", "PUT", "DELETE", "HEAD", "TRACE", "PATCH"];

        if (!\in_array($method, $validMethods)) {
            throw new \InvalidArgumentException(\sprintf('%s is not a valid HTTP method.', $method));
        }

        // Get the path from the route and remove and leading or trailing slash.
        $routePath = $this->getRoutePath();

        // Iterate through all of the known routes looking for a match.
        foreach ($this->routes as $route) {
            if (\in_array($method, $route->getMethods())) {
                if (preg_match($route->getRegex(), ltrim($routePath, '/'), $matches)) {
                    // If we have gotten this far then we have a positive match.
                    $vars = $route->getDefaults();

                    foreach ($route->getRouteVariables() as $i => $var) {
                        $vars[$var] = $matches[$i + 1];
                    }

                    $controller = preg_split("/[.]+/", $route->getController());

                    return [
                        'controller' => $controller[0],
                        'task'       => $controller[1],
                        'vars'       => $vars,
                    ];
                }
            }
        }

        throw new RouteNotFoundException(\sprintf('Unable to handle request for route `%s`.', $routePath));
    }

    /**
     * Get the path from the route and remove and leading or trailing slash.
     *
     * @return string
     *
     * @since 4.0.0
     */
    public function getRoutePath()
    {
        // Get the path from the route and remove and leading or trailing slash.
        $uri  = Uri::getInstance();
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

        // Transform the route
        $path = $this->removeIndexPhpFromPath($path);

        return $path;
    }

    /**
     * Removes the index.php from the route's path.
     *
     * @param   string  $path  The path
     *
     * @return  string
     *
     * @since   4.0.0
     */
    private function removeIndexPhpFromPath(string $path): string
    {
        // Normalize the path
        $path = ltrim($path, '/');

        // We can only remove index.php if it's present in the beginning of the route
        if (strpos($path, 'index.php') !== 0) {
            return $path;
        }

        // Edge case: the route is index.php without a trailing slash. Bad idea but we can still map it to a null route.
        if ($path === 'index.php') {
            return '';
        }

        // Remove the "index.php/" part of the route and return the result.
        return substr($path, 10);
    }

    /**
     * Extract routes matching current route from all known routes.
     *
     * @return \Joomla\Router\Route[]
     *
     * @since 4.0.0
     */
    public function getMatchingRoutes()
    {
        $routePath = $this->getRoutePath();

        // Extract routes matching $routePath from all known routes.
        return array_filter(
            $this->routes,
            function ($route) use ($routePath) {
                return preg_match($route->getRegex(), ltrim($routePath, '/')) === 1;
            }
        );
    }
}
