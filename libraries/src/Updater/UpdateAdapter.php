<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Updater;

use Joomla\CMS\Adapter\AdapterInstance;
use Joomla\CMS\Event\Installer\BeforeUpdateSiteDownloadEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Version;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * UpdateAdapter class.
 *
 * @since  1.7.0
 */
abstract class UpdateAdapter extends AdapterInstance
{
    /**
     * Resource handle for the XML Parser
     *
     * @var    \XMLParser
     * @since  3.0.0
     */
    protected $xmlParser;

    /**
     * Element call stack
     *
     * @var    array
     * @since  3.0.0
     */
    protected $stack = ['base'];

    /**
     * ID of update site
     *
     * @var    integer
     * @since  3.0.0
     */
    protected $updateSiteId = 0;

    /**
     * Columns in the extensions table to be updated
     *
     * @var    array
     * @since  3.0.0
     */
    protected $updatecols = ['NAME', 'ELEMENT', 'TYPE', 'FOLDER', 'CLIENT', 'VERSION', 'DESCRIPTION', 'INFOURL', 'CHANGELOGURL', 'EXTRA_QUERY'];

    /**
     * Should we try appending a .xml extension to the update site's URL?
     *
     * @var   boolean
     */
    protected $appendExtension = false;

    /**
     * The name of the update site (used in logging)
     *
     * @var   string
     */
    protected $updateSiteName = '';

    /**
     * The update site URL from which we will get the update information
     *
     * @var   string
     */
    protected $_url = '';

    /**
     * The minimum stability required for updates to be taken into account. The possible values are:
     * 0    dev         Development snapshots, nightly builds, pre-release versions and so on
     * 1    alpha       Alpha versions (work in progress, things are likely to be broken)
     * 2    beta        Beta versions (major functionality in place, show-stopper bugs are likely to be present)
     * 3    rc          Release Candidate versions (almost stable, minor bugs might be present)
     * 4    stable      Stable versions (production quality code)
     *
     * @var    integer
     * @since  14.1
     *
     * @see    Updater
     */
    protected $minimum_stability = Updater::STABILITY_STABLE;

    /**
     * Gets the reference to the current direct parent
     *
     * @return  string
     *
     * @since   1.7.0
     */
    protected function _getStackLocation()
    {
        return implode('->', $this->stack);
    }

    /**
     * Gets the reference to the last tag
     *
     * @return  object
     *
     * @since   1.7.0
     */
    protected function _getLastTag()
    {
        return $this->stack[\count($this->stack) - 1];
    }

    /**
     * Finds an update
     *
     * @param   array  $options  Options to use: update_site_id: the unique ID of the update site to look at
     *
     * @return  array  Update_sites and updates discovered
     *
     * @since   1.7.0
     */
    abstract public function findUpdate($options);

    /**
     * Toggles the enabled status of an update site. Update sites are disabled before getting the update information
     * from their URL and enabled afterwards. If the URL fetch fails with a PHP fatal error (e.g. timeout) the faulty
     * update site will remain disabled the next time we attempt to load the update information.
     *
     * @param   int   $updateSiteId  The numeric ID of the update site to enable/disable
     * @param   bool  $enabled       Enable the site when true, disable it when false
     *
     * @return  void
     */
    protected function toggleUpdateSite($updateSiteId, $enabled = true)
    {
        $updateSiteId = (int) $updateSiteId;
        $enabled      = (bool) $enabled ? 1 : 0;

        if (empty($updateSiteId)) {
            return;
        }

        $db    = $this->parent->getDbo();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__update_sites'))
            ->set($db->quoteName('enabled') . ' = :enabled')
            ->where($db->quoteName('update_site_id') . ' = :id')
            ->bind(':enabled', $enabled, ParameterType::INTEGER)
            ->bind(':id', $updateSiteId, ParameterType::INTEGER);
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (\RuntimeException) {
            // Do nothing
        }
    }

    /**
     * Get the name of an update site. This is used in logging.
     *
     * @param   int  $updateSiteId  The numeric ID of the update site
     *
     * @return  string  The name of the update site or an empty string if it's not found
     */
    protected function getUpdateSiteName($updateSiteId)
    {
        $updateSiteId = (int) $updateSiteId;

        if (empty($updateSiteId)) {
            return '';
        }

        $db    = $this->parent->getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('name'))
            ->from($db->quoteName('#__update_sites'))
            ->where($db->quoteName('update_site_id') . ' = :id')
            ->bind(':id', $updateSiteId, ParameterType::INTEGER);
        $db->setQuery($query);

        $name = '';

        try {
            $name = $db->loadResult();
        } catch (\RuntimeException) {
            // Do nothing
        }

        return $name;
    }

    /**
     * Try to get the raw HTTP response from the update site, hopefully containing the update XML.
     *
     * @param   array  $options  The update options, see findUpdate() in children classes
     *
     * @return  \Joomla\CMS\Http\Response|bool  False if we can't connect to the site, HTTP Response object otherwise
     *
     * @throws  \Exception
     */
    protected function getUpdateSiteResponse($options = [])
    {
        $url                = trim($options['location']);
        $this->_url         = &$url;
        $this->updateSiteId = $options['update_site_id'];

        if (!isset($options['update_site_name'])) {
            $options['update_site_name'] = $this->getUpdateSiteName($this->updateSiteId);
        }

        $this->updateSiteName  = $options['update_site_name'];
        $this->appendExtension = false;

        if (\array_key_exists('append_extension', $options)) {
            $this->appendExtension = $options['append_extension'];
        }

        if ($this->appendExtension && (!str_ends_with($url, '.xml'))) {
            if (!str_ends_with($url, '/')) {
                $url .= '/';
            }

            $url .= 'extension.xml';
        }

        // Disable the update site. If the get() below fails with a fatal error (e.g. timeout) the faulty update
        // site will remain disabled
        $this->toggleUpdateSite($this->updateSiteId, false);

        $startTime = microtime(true);

        $version    = new Version();
        $httpOption = new Registry();
        $httpOption->set('userAgent', $version->getUserAgent('Joomla', true, false));

        $headers    = [];
        $dispatcher = Factory::getApplication()->getDispatcher();
        PluginHelper::importPlugin('installer', null, true, $dispatcher);
        $event = new BeforeUpdateSiteDownloadEvent('onInstallerBeforeUpdateSiteDownload', [
            'url'     => $url,
            'headers' => $headers,
        ]);
        $dispatcher->dispatch('onInstallerBeforeUpdateSiteDownload', $event);
        $url     = $event->getArgument('url', $url);
        $headers = $event->getArgument('headers', $headers);

        // Http transport throws an exception when there's no response.
        try {
            $http     = HttpFactory::getHttp($httpOption);
            $response = $http->get($url, $headers, 20);
        } catch (\RuntimeException) {
            $response = null;
        }

        // Enable the update site. Since the get() returned the update site should remain enabled
        $this->toggleUpdateSite($this->updateSiteId, true);

        // Log the time it took to load this update site's information
        $endTime    = microtime(true);
        $timeToLoad = \sprintf('%0.2f', $endTime - $startTime);
        Log::add(
            "Loading information from update site #{$this->updateSiteId} with name " .
            "\"$this->updateSiteName\" and URL $url took $timeToLoad seconds",
            Log::INFO,
            'updater'
        );

        if ($response === null || $response->code !== 200) {
            // If the URL is missing the .xml extension, try appending it and retry loading the update
            if (!$this->appendExtension && (!str_ends_with($url, '.xml'))) {
                $options['append_extension'] = true;

                return $this->getUpdateSiteResponse($options);
            }

            // Log the exact update site name and URL which could not be loaded
            Log::add('Error opening url: ' . $url . ' for update site: ' . $this->updateSiteName, Log::WARNING, 'updater');
            $app = Factory::getApplication();
            $app->enqueueMessage(
                html_entity_decode(Text::sprintf('JLIB_UPDATER_ERROR_OPEN_UPDATE_SITE', $this->updateSiteId, $this->updateSiteName, $url)),
                'warning'
            );

            return false;
        }

        return $response;
    }
}
