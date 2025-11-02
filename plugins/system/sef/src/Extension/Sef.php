<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.sef
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Sef\Extension;

use Joomla\CMS\Event\Application\AfterDispatchEvent;
use Joomla\CMS\Event\Application\AfterInitialiseEvent;
use Joomla\CMS\Event\Application\AfterRenderEvent;
use Joomla\CMS\Event\Application\AfterRouteEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Router\Router;
use Joomla\CMS\Router\SiteRouter;
use Joomla\CMS\Router\SiteRouterAwareTrait;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! SEF Plugin.
 *
 * @since  1.5
 */
final class Sef extends CMSPlugin implements SubscriberInterface
{
    use SiteRouterAwareTrait;

    /**
     * Returns an array of CMS events this plugin will listen to and the respective handlers.
     *
     * @return  array
     *
     * @since  5.1.0
     */
    public static function getSubscribedEvents(): array
    {
        /**
         * Note that onAfterInitialise must be the first handlers to run for this
         * plugin to operate as expected. These handlers load compatibility code which
         * might be needed by other plugins
         */
        return [
            'onAfterInitialise' => 'onAfterInitialise',
            'onAfterRoute'      => 'onAfterRoute',
            'onAfterDispatch'   => 'onAfterDispatch',
            'onAfterRender'     => 'onAfterRender',
        ];
    }

    /**
     * After initialise.
     *
     * @param   AfterInitialiseEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function onAfterInitialise(AfterInitialiseEvent $event)
    {
        $router = $this->getSiteRouter();
        $app    = $event->getApplication();

        if (
            $app->get('sef')
            && !$app->get('sef_suffix')
            && $this->params->get('trailingslash', -1) != -1
        ) {
            if ($this->params->get('trailingslash') == 0) {
                // Remove trailingslash
                $router->attachBuildRule([$this, 'removeTrailingSlash'], SiteRouter::PROCESS_AFTER);
            } elseif ($this->params->get('trailingslash') == 1) {
                // Add trailingslash
                $router->attachBuildRule([$this, 'addTrailingSlash'], SiteRouter::PROCESS_AFTER);
            }
        }
    }

    /**
     * OnAfterRoute listener
     *
     * @param   AfterRouteEvent $event  The event instance.
     *
     * @return void
     *
     * @since   5.1.0
     */
    public function onAfterRoute(AfterRouteEvent $event)
    {
        $app = $event->getApplication();

        // Following code only for Site application, GET requests and HTML documents
        if (
            !$app->isClient('site')
            || $app->getInput()->getMethod() !== 'GET'
            || $app->getInput()->get('format', 'html') !== 'html'
        ) {
            return;
        }

        $router = $this->getSiteRouter();

        /**
         * The URL was successfully parsed, but is "tainted", e.g. parts of
         * it were recoverably wrong. So we take the parsed variables, build
         * a new URL and redirect to that.
         */
        if ($router->isTainted()) {
            $parsedVars = $router->getVars();

            if ($app->getLanguageFilter()) {
                $parsedVars['lang'] = $parsedVars['language'];
                unset($parsedVars['language']);
            }

            $newRoute = Route::_($parsedVars, false);
            $origUri  = clone Uri::getInstance();
            $route    = $origUri->toString(['path', 'query']);

            if ($route !== $newRoute) {
                $app->redirect($newRoute, 301);
            }
        }

        // Enforce removing index.php with a redirect
        if ($app->get('sef_rewrite') && $this->params->get('indexphp')) {
            $this->removeIndexphp();
        }

        // Check for trailing slash
        if ($app->get('sef') && !$app->get('sef_suffix') && $this->params->get('trailingslash', '-1') != '-1') {
            $this->enforceTrailingSlash();
        }

        // Enforce adding a suffix with a redirect
        if ($app->get('sef') && $app->get('sef_suffix') && $this->params->get('enforcesuffix')) {
            $this->enforceSuffix();
        }

        // Enforce SEF URLs
        if ($this->params->get('strictrouting') && $app->getInput()->getMethod() == 'GET') {
            $this->enforceSEF();
        }
    }

    /**
     * Add the canonical uri to the head.
     *
     * @param   AfterDispatchEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   3.5
     */
    public function onAfterDispatch(AfterDispatchEvent $event)
    {
        $app = $event->getApplication();
        $doc = $app->getDocument();

        if (!$app->isClient('site') || $doc->getType() !== 'html') {
            return;
        }

        $sefDomain = $this->params->get('domain', false);

        // Don't add a canonical html tag if no alternative domain has added in SEF plugin domain field.
        if (empty($sefDomain)) {
            return;
        }

        // Check if a canonical html tag already exists (for instance, added by a component).
        $canonical = '';

        foreach ($doc->_links as $linkUrl => $link) {
            if (isset($link['relation']) && $link['relation'] === 'canonical') {
                $canonical = $linkUrl;
                break;
            }
        }

        // If a canonical html tag already exists get the canonical and change it to use the SEF plugin domain field.
        if (!empty($canonical)) {
            // Remove current canonical link.
            unset($doc->_links[$canonical]);

            // Set the current canonical link but use the SEF system plugin domain field.
            $canonical = $sefDomain . Uri::getInstance($canonical)->toString(['path', 'query', 'fragment']);
        } else {
            // If a canonical html doesn't exists already add a canonical html tag using the SEF plugin domain field.
            $canonical = $sefDomain . Uri::getInstance()->toString(['path', 'query', 'fragment']);
        }

        // Add the canonical link.
        $doc->addHeadLink(htmlspecialchars($canonical), 'canonical');
    }

    /**
     * Convert the site URL to fit to the HTTP request.
     *
     * @param   AfterRenderEvent $event  The event instance.
     *
     * @return  void
     */
    public function onAfterRender(AfterRenderEvent $event)
    {
        $app = $event->getApplication();
        if (!$app->isClient('site')) {
            return;
        }

        // Replace src links.
        $base   = Uri::base(true) . '/';
        $buffer = $app->getBody();

        // For feeds we need to search for the URL with domain.
        $prefix = $app->getDocument()->getType() === 'feed' ? Uri::root() : '';

        // Replace index.php URI by SEF URI.
        if (str_contains($buffer, 'href="' . $prefix . 'index.php?')) {
            preg_match_all('#href="' . $prefix . 'index.php\?([^"]+)"#m', $buffer, $matches);

            foreach ($matches[1] as $urlQueryString) {
                $buffer = str_replace(
                    'href="' . $prefix . 'index.php?' . $urlQueryString . '"',
                    'href="' . $prefix . Route::_('index.php?' . $urlQueryString) . '"',
                    $buffer
                );
            }

            $this->checkBuffer($buffer);
        }

        // Check for all unknown protocols (a protocol must contain at least one alphanumeric character followed by a ":").
        $protocols  = '[a-zA-Z0-9\-]+:';
        $attributes = ['href=', 'src=', 'poster='];

        foreach ($attributes as $attribute) {
            if (str_contains($buffer, $attribute)) {
                $regex  = '#\s' . $attribute . '"(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
                $buffer = preg_replace($regex, ' ' . $attribute . '"' . $base . '$1"', $buffer);
                $this->checkBuffer($buffer);
            }
        }

        if (str_contains($buffer, 'srcset=')) {
            $regex = '#\s+srcset="([^"]+)"#m';

            $buffer = preg_replace_callback(
                $regex,
                function ($match) use ($base, $protocols) {
                    preg_match_all('#(?:[^\s]+)\s*(?:[\d\.]+[wx])?(?:\,\s*)?#i', $match[1], $matches);

                    foreach ($matches[0] as &$src) {
                        $src = preg_replace('#^(?!/|' . $protocols . '|\#|\')(.+)#', $base . '$1', $src);
                    }

                    return ' srcset="' . implode($matches[0]) . '"';
                },
                $buffer
            );

            $this->checkBuffer($buffer);
        }

        // Replace all unknown protocols in javascript window open events.
        if (str_contains($buffer, 'window.open(')) {
            $regex  = '#onclick="window.open\(\'(?!/|' . $protocols . '|\#)([^/]+[^\']*?\')#m';
            $buffer = preg_replace($regex, 'onclick="window.open(\'' . $base . '$1', $buffer);
            $this->checkBuffer($buffer);
        }

        // Replace all unknown protocols in onmouseover and onmouseout attributes.
        $attributes = ['onmouseover=', 'onmouseout='];

        foreach ($attributes as $attribute) {
            if (str_contains($buffer, $attribute)) {
                $regex  = '#' . $attribute . '"this.src=([\']+)(?!/|' . $protocols . '|\#|\')([^"]+)"#m';
                $buffer = preg_replace($regex, $attribute . '"this.src=$1' . $base . '$2"', $buffer);
                $this->checkBuffer($buffer);
            }
        }

        // Replace all unknown protocols in CSS background image.
        if (str_contains($buffer, 'style=')) {
            $regex_url  = '\s*url\s*\(([\'\"]|\&\#0?3[49];)?(?!/|\&\#0?3[49];|' . $protocols . '|\#)([^\)\'\"]+)([\'\"]|\&\#0?3[49];)?\)';
            $regex      = '#style=\s*([\'\"])(.*):' . $regex_url . '#m';
            $buffer     = preg_replace($regex, 'style=$1$2: url($3' . $base . '$4$5)', $buffer);
            $this->checkBuffer($buffer);
        }

        // Replace all unknown protocols in OBJECT param tag.
        if (str_contains($buffer, '<param')) {
            // OBJECT <param name="xx", value="yy"> -- fix it only inside the <param> tag.
            $regex  = '#(<param\s+)name\s*=\s*"(movie|src|url)"[^>]\s*value\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
            $buffer = preg_replace($regex, '$1name="$2" value="' . $base . '$3"', $buffer);
            $this->checkBuffer($buffer);

            // OBJECT <param value="xx", name="yy"> -- fix it only inside the <param> tag.
            $regex  = '#(<param\s+[^>]*)value\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"\s*name\s*=\s*"(movie|src|url)"#m';
            $buffer = preg_replace($regex, '<param value="' . $base . '$2" name="$3"', $buffer);
            $this->checkBuffer($buffer);
        }

        // Replace all unknown protocols in OBJECT tag.
        if (str_contains($buffer, '<object')) {
            $regex  = '#(<object\s+[^>]*)data\s*=\s*"(?!/|' . $protocols . '|\#|\')([^"]*)"#m';
            $buffer = preg_replace($regex, '$1data="' . $base . '$2"', $buffer);
            $this->checkBuffer($buffer);
        }

        // Use the replaced HTML body.
        $app->setBody($buffer);
    }

    /**
     * Enforce the URL suffix with a redirect
     *
     * @return  void
     *
     * @since   5.2.0
     */
    public function enforceSuffix()
    {
        $origUri = Uri::getInstance();
        $route   = $origUri->getPath();

        if (str_ends_with($route, 'index.php') || str_ends_with($route, '/')) {
            // We don't want suffixes when the URL ends in index.php or with a /
            return;
        }

        $suffix       = pathinfo($route, PATHINFO_EXTENSION);
        $nonSEFSuffix = $origUri->getVar('format');

        if ($nonSEFSuffix && $suffix !== $nonSEFSuffix) {
            // There is a URL query parameter named "format", which isn't the same to the suffix
            $pathWithoutSuffix = ($suffix !== '') ? substr($route, 0, -(\strlen($suffix) + 1)) : $route;

            $origUri->delVar('format');
            $origUri->setPath($pathWithoutSuffix . '.' . $nonSEFSuffix);
            $this->getApplication()->redirect($origUri->toString(), 301);
        }

        if ($suffix && $suffix == $nonSEFSuffix) {
            // There is a URL query parameter named "format", which is identical to the suffix
            $origUri->delVar('format');
            $this->getApplication()->redirect($origUri->toString(), 301);
        }

        if (!$suffix) {
            // We don't have a suffix, so we default to .html at the end
            $origUri->setPath($route . '.html');
            $this->getApplication()->redirect($origUri->toString(), 301);
        }
    }

    /**
     * Enforce removal of index.php with a redirect
     *
     * @return  void
     *
     * @since   5.1.0
     */
    protected function removeIndexphp()
    {
        $origUri = Uri::getInstance();

        if (str_ends_with($origUri->getPath(), 'index.php')) {
            // Remove trailing index.php
            $origUri->setPath(substr($origUri->getPath(), 0, -9));
            $this->getApplication()->redirect($origUri->toString(), 301);
        }

        if (substr($origUri->getPath(), \strlen(Uri::base(true)), 11) === '/index.php/') {
            // Remove leading index.php
            $origUri->setPath(Uri::base(true) . substr($origUri->getPath(), \strlen(Uri::base(true)) + 10));
            $this->getApplication()->redirect($origUri->toString(), 301);
        }
    }

    /**
     * Remove any trailing slash from URLs built in Joomla
     *
     * @param   Router  &$router  Router object.
     * @param   Uri     &$uri     Uri object.
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function removeTrailingSlash(&$router, &$uri)
    {
        $path = $uri->getPath();

        if ($path != Uri::base(true) . '/' && str_ends_with($path, '/')) {
            $uri->setPath(substr($path, 0, -1));
        }
    }

    /**
     * Add trailing slash to URLs built in Joomla
     *
     * @param   Router  &$router  Router object.
     * @param   Uri     &$uri     Uri object.
     *
     * @return  void
     *
     * @since   5.1.0
     */
    public function addTrailingSlash(&$router, &$uri)
    {
        $path = $uri->getPath();

        if (!str_ends_with($path, '/')) {
            $uri->setPath($path . '/');
        }
    }

    /**
     * Redirect to a URL with or without trailing slash
     *
     * @return  void
     *
     * @since   5.1.0
     */
    protected function enforceTrailingSlash()
    {
        $originalUri = Uri::getInstance();

        if (
            (int)$this->params->get('trailingslash') === 0
            && str_ends_with($originalUri->getPath(), '/')
            && $originalUri->toString(['scheme', 'host', 'port', 'path']) !== Uri::root()
        ) {
            // Remove trailingslash
            $originalUri->setPath(substr($originalUri->getPath(), 0, -1));
            $this->getApplication()->redirect($originalUri->toString(), 301);
        } elseif ((int)$this->params->get('trailingslash') === 1 && !str_ends_with($originalUri->getPath(), '/')) {
            // Add trailingslash
            $originalUri->setPath($originalUri->getPath() . '/');
            $this->getApplication()->redirect($originalUri->toString(), 301);
        }
    }

    /**
     * Enforce a redirect from URL with query parameters to SEF URL
     *
     * @return  void
     *
     * @since   5.2.0
     */
    protected function enforceSEF()
    {
        $app     = $this->getApplication();
        $origUri = clone Uri::getInstance();

        if (\count($origUri->getQuery(true))) {
            $parsedVars = $app->getInput()->getArray();

            if ($app->getLanguageFilter()) {
                $parsedVars['lang'] = $parsedVars['language'];
                unset($parsedVars['language']);
            }

            $route    = $origUri->toString(['path', 'query']);
            $newRoute = Route::_($parsedVars, false);
            $newUri   = new Uri($newRoute);

            if (!\count($newUri->getQuery(true)) && $route !== $newRoute) {
                $app->redirect($newRoute, 301);
            }
        }
    }

    /**
     * Check the buffer.
     *
     * @param   string  $buffer  Buffer to be checked.
     *
     * @return  void
     */
    private function checkBuffer($buffer)
    {
        if ($buffer === null) {
            switch (preg_last_error()) {
                case PREG_BACKTRACK_LIMIT_ERROR:
                    $message = 'PHP regular expression limit reached (pcre.backtrack_limit)';
                    break;
                case PREG_RECURSION_LIMIT_ERROR:
                    $message = 'PHP regular expression limit reached (pcre.recursion_limit)';
                    break;
                case PREG_BAD_UTF8_ERROR:
                    $message = 'Bad UTF8 passed to PCRE function';
                    break;
                default:
                    $message = 'Unknown PCRE error calling PCRE function';
            }

            throw new \RuntimeException($message);
        }
    }
}
