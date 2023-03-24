<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.redirect
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\ErrorEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Plugin class for redirect handling.
 *
 * @since  1.6
 */
class PlgSystemRedirect extends CMSPlugin implements SubscriberInterface
{
    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  3.4
     */
    protected $autoloadLanguage = false;

    /**
     * Database object.
     *
     * @var    DatabaseInterface
     * @since  4.0.0
     */
    protected $db;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onError' => 'handleError',
        ];
    }

    /**
     * Internal processor for all error handlers
     *
     * @param   ErrorEvent  $event  The event object
     *
     * @return  void
     *
     * @since   3.5
     */
    public function handleError(ErrorEvent $event)
    {
        /** @var \Joomla\CMS\Application\CMSApplication $app */
        $app = $event->getApplication();

        if ($app->isClient('administrator') || ((int) $event->getError()->getCode() !== 404)) {
            return;
        }

        $uri = Uri::getInstance();

        // These are the original URLs
        $orgurl                = rawurldecode($uri->toString(['scheme', 'host', 'port', 'path', 'query', 'fragment']));
        $orgurlRel             = rawurldecode($uri->toString(['path', 'query', 'fragment']));

        // The above doesn't work for sub directories, so do this
        $orgurlRootRel         = str_replace(Uri::root(), '', $orgurl);

        // For when users have added / to the url
        $orgurlRootRelSlash    = str_replace(Uri::root(), '/', $orgurl);
        $orgurlWithoutQuery    = rawurldecode($uri->toString(['scheme', 'host', 'port', 'path', 'fragment']));
        $orgurlRelWithoutQuery = rawurldecode($uri->toString(['path', 'fragment']));

        // These are the URLs we save and use
        $url                = StringHelper::strtolower(rawurldecode($uri->toString(['scheme', 'host', 'port', 'path', 'query', 'fragment'])));
        $urlRel             = StringHelper::strtolower(rawurldecode($uri->toString(['path', 'query', 'fragment'])));

        // The above doesn't work for sub directories, so do this
        $urlRootRel         = str_replace(Uri::root(), '', $url);

        // For when users have added / to the url
        $urlRootRelSlash    = str_replace(Uri::root(), '/', $url);
        $urlWithoutQuery    = StringHelper::strtolower(rawurldecode($uri->toString(['scheme', 'host', 'port', 'path', 'fragment'])));
        $urlRelWithoutQuery = StringHelper::strtolower(rawurldecode($uri->toString(['path', 'fragment'])));

        $excludes = (array) $this->params->get('exclude_urls');

        $skipUrl = false;

        foreach ($excludes as $exclude) {
            if (empty($exclude->term)) {
                continue;
            }

            if (!empty($exclude->regexp)) {
                // Only check $url, because it includes all other sub urls
                if (preg_match('/' . $exclude->term . '/i', $orgurlRel)) {
                    $skipUrl = true;
                    break;
                }
            } else {
                if (StringHelper::strpos($orgurlRel, $exclude->term) !== false) {
                    $skipUrl = true;
                    break;
                }
            }
        }

        /**
         * Why is this (still) here?
         * Because hackers still try urls with mosConfig_* and Url Injection with =http[s]:// and we dont want to log/redirect these requests
         */
        if ($skipUrl || (strpos($url, 'mosConfig_') !== false) || (strpos($url, '=http') !== false)) {
            return;
        }

        $query = $this->db->getQuery(true);

        $query->select('*')
            ->from($this->db->quoteName('#__redirect_links'))
            ->whereIn(
                $this->db->quoteName('old_url'),
                [
                    $url,
                    $urlRel,
                    $urlRootRel,
                    $urlRootRelSlash,
                    $urlWithoutQuery,
                    $urlRelWithoutQuery,
                    $orgurl,
                    $orgurlRel,
                    $orgurlRootRel,
                    $orgurlRootRelSlash,
                    $orgurlWithoutQuery,
                    $orgurlRelWithoutQuery,
                ],
                ParameterType::STRING
            );

        $this->db->setQuery($query);

        $redirect = null;

        try {
            $redirects = $this->db->loadAssocList();
        } catch (Exception $e) {
            $event->setError(new Exception(Text::_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));

            return;
        }

        $possibleMatches = array_unique(
            [
                $url,
                $urlRel,
                $urlRootRel,
                $urlRootRelSlash,
                $urlWithoutQuery,
                $urlRelWithoutQuery,
                $orgurl,
                $orgurlRel,
                $orgurlRootRel,
                $orgurlRootRelSlash,
                $orgurlWithoutQuery,
                $orgurlRelWithoutQuery,
            ]
        );

        foreach ($possibleMatches as $match) {
            if (($index = array_search($match, array_column($redirects, 'old_url'))) !== false) {
                $redirect = (object) $redirects[$index];

                if ((int) $redirect->published === 1) {
                    break;
                }
            }
        }

        // A redirect object was found and, if published, will be used
        if ($redirect !== null && ((int) $redirect->published === 1)) {
            if (!$redirect->header || (bool) ComponentHelper::getParams('com_redirect')->get('mode', false) === false) {
                $redirect->header = 301;
            }

            if ($redirect->header < 400 && $redirect->header >= 300) {
                $urlQuery = $uri->getQuery();

                $oldUrlParts = parse_url($redirect->old_url);

                $newUrl = $redirect->new_url;

                if ($urlQuery !== '' && empty($oldUrlParts['query'])) {
                    $newUrl .= '?' . $urlQuery;
                }

                $dest = Uri::isInternal($newUrl) || strpos($newUrl, 'http') === false ?
                    Route::_($newUrl) : $newUrl;

                // In case the url contains double // lets remove it
                $destination = str_replace(Uri::root() . '/', Uri::root(), $dest);

                // Always count redirect hits
                $redirect->hits++;

                try {
                    $this->db->updateObject('#__redirect_links', $redirect, 'id');
                } catch (Exception $e) {
                    // We don't log issues for now
                }

                $app->redirect($destination, (int) $redirect->header);
            }

            $event->setError(new RuntimeException($event->getError()->getMessage(), $redirect->header, $event->getError()));
        } elseif ($redirect === null) {
            // No redirect object was found so we create an entry in the redirect table
            if ((bool) $this->params->get('collect_urls', 1)) {
                if (!$this->params->get('includeUrl', 1)) {
                    $url = $urlRel;
                }

                $nowDate = Factory::getDate()->toSql();

                $data = (object) [
                    'id' => 0,
                    'old_url' => $url,
                    'referer' => $app->input->server->getString('HTTP_REFERER', ''),
                    'hits' => 1,
                    'published' => 0,
                    'created_date' => $nowDate,
                    'modified_date' => $nowDate,
                ];

                try {
                    $this->db->insertObject('#__redirect_links', $data, 'id');
                } catch (Exception $e) {
                    $event->setError(new Exception(Text::_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));

                    return;
                }
            }
        } else {
            // We have an unpublished redirect object, increment the hit counter
            $redirect->hits++;

            try {
                $this->db->updateObject('#__redirect_links', $redirect, ['id']);
            } catch (Exception $e) {
                $event->setError(new Exception(Text::_('PLG_SYSTEM_REDIRECT_ERROR_UPDATING_DATABASE'), 500, $e));

                return;
            }
        }
    }
}
