<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.stats
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Cache\Cache;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\HttpFactory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

// Uncomment the following line to enable debug mode for testing purposes. Note: statistics will be sent on every page load
// define('PLG_SYSTEM_STATS_DEBUG', 1);

/**
 * Statistics system plugin. This sends anonymous data back to the Joomla! Project about the
 * PHP, SQL, Joomla and OS versions
 *
 * @since  3.5
 */
class PlgSystemStats extends CMSPlugin
{
    /**
     * Indicates sending statistics is always allowed.
     *
     * @var    integer
     *
     * @since  3.5
     */
    public const MODE_ALLOW_ALWAYS = 1;

    /**
     * Indicates sending statistics is never allowed.
     *
     * @var    integer
     *
     * @since  3.5
     */
    public const MODE_ALLOW_NEVER = 3;

    /**
     * @var    \Joomla\CMS\Application\CMSApplication
     *
     * @since  3.5
     */
    protected $app;

    /**
     * @var    \Joomla\Database\DatabaseDriver
     *
     * @since  3.5
     */
    protected $db;

    /**
     * URL to send the statistics.
     *
     * @var    string
     *
     * @since  3.5
     */
    protected $serverUrl = 'https://developer.joomla.org/stats/submit';

    /**
     * Unique identifier for this site
     *
     * @var    string
     *
     * @since  3.5
     */
    protected $uniqueId;

    /**
     * Listener for the `onAfterInitialise` event
     *
     * @return  void
     *
     * @since   3.5
     */
    public function onAfterInitialise()
    {
        if (!$this->app->isClient('administrator') || !$this->isAllowedUser()) {
            return;
        }

        if ($this->isCaptiveMFA()) {
            return;
        }

        if (!$this->isDebugEnabled() && !$this->isUpdateRequired()) {
            return;
        }

        if (Uri::getInstance()->getVar('tmpl') === 'component') {
            return;
        }

        // Load plugin language files only when needed (ex: they are not needed in site client).
        $this->loadLanguage();
    }

    /**
     * Listener for the `onAfterDispatch` event
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAfterDispatch()
    {
        if (!$this->app->isClient('administrator') || !$this->isAllowedUser()) {
            return;
        }

        if ($this->isCaptiveMFA()) {
            return;
        }

        if (!$this->isDebugEnabled() && !$this->isUpdateRequired()) {
            return;
        }

        if (Uri::getInstance()->getVar('tmpl') === 'component') {
            return;
        }

        if ($this->app->getDocument()->getType() !== 'html') {
            return;
        }

        $this->app->getDocument()->getWebAssetManager()
            ->registerAndUseScript('plg_system_stats.message', 'plg_system_stats/stats-message.js', [], ['defer' => true], ['core']);
    }

    /**
     * User selected to always send data
     *
     * @return  void
     *
     * @since   3.5
     *
     * @throws  Exception         If user is not allowed.
     * @throws  RuntimeException  If there is an error saving the params or sending the data.
     */
    public function onAjaxSendAlways()
    {
        if (!$this->isAllowedUser() || !$this->isAjaxRequest()) {
            throw new Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
        }

        $this->params->set('mode', static::MODE_ALLOW_ALWAYS);

        if (!$this->saveParams()) {
            throw new RuntimeException('Unable to save plugin settings', 500);
        }

        echo json_encode(['sent' => (int) $this->sendStats()]);
    }

    /**
     * User selected to never send data.
     *
     * @return  void
     *
     * @since   3.5
     *
     * @throws  Exception         If user is not allowed.
     * @throws  RuntimeException  If there is an error saving the params.
     */
    public function onAjaxSendNever()
    {
        if (!$this->isAllowedUser() || !$this->isAjaxRequest()) {
            throw new Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
        }

        $this->params->set('mode', static::MODE_ALLOW_NEVER);

        if (!$this->saveParams()) {
            throw new RuntimeException('Unable to save plugin settings', 500);
        }

        if (!$this->disablePlugin()) {
            throw new RuntimeException('Unable to disable the statistics plugin', 500);
        }

        echo json_encode(['sent' => 0]);
    }

    /**
     * Send the stats to the server.
     * On first load | on demand mode it will show a message asking users to select mode.
     *
     * @return  void
     *
     * @since   3.5
     *
     * @throws  Exception         If user is not allowed.
     * @throws  RuntimeException  If there is an error saving the params, disabling the plugin or sending the data.
     */
    public function onAjaxSendStats()
    {
        if (!$this->isAllowedUser() || !$this->isAjaxRequest()) {
            throw new Exception(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
        }

        // User has not selected the mode. Show message.
        if ((int) $this->params->get('mode') !== static::MODE_ALLOW_ALWAYS) {
            $data = [
                'sent' => 0,
                'html' => $this->getRenderer('message')->render($this->getLayoutData()),
            ];

            echo json_encode($data);

            return;
        }

        if (!$this->saveParams()) {
            throw new RuntimeException('Unable to save plugin settings', 500);
        }

        echo json_encode(['sent' => (int) $this->sendStats()]);
    }

    /**
     * Get the data through events
     *
     * @param   string  $context  Context where this will be called from
     *
     * @return  array
     *
     * @since   3.5
     */
    public function onGetStatsData($context)
    {
        return $this->getStatsData();
    }

    /**
     * Debug a layout of this plugin
     *
     * @param   string  $layoutId  Layout identifier
     * @param   array   $data      Optional data for the layout
     *
     * @return  string
     *
     * @since   3.5
     */
    public function debug($layoutId, $data = [])
    {
        $data = array_merge($this->getLayoutData(), $data);

        return $this->getRenderer($layoutId)->debug($data);
    }

    /**
     * Get the data for the layout
     *
     * @return  array
     *
     * @since   3.5
     */
    protected function getLayoutData()
    {
        return [
            'plugin'       => $this,
            'pluginParams' => $this->params,
            'statsData'    => $this->getStatsData(),
        ];
    }

    /**
     * Get the layout paths
     *
     * @return  array
     *
     * @since   3.5
     */
    protected function getLayoutPaths()
    {
        $template = Factory::getApplication()->getTemplate();

        return [
            JPATH_ADMINISTRATOR . '/templates/' . $template . '/html/layouts/plugins/' . $this->_type . '/' . $this->_name,
            __DIR__ . '/layouts',
        ];
    }

    /**
     * Get the plugin renderer
     *
     * @param   string  $layoutId  Layout identifier
     *
     * @return  \Joomla\CMS\Layout\LayoutInterface
     *
     * @since   3.5
     */
    protected function getRenderer($layoutId = 'default')
    {
        $renderer = new FileLayout($layoutId);

        $renderer->setIncludePaths($this->getLayoutPaths());

        return $renderer;
    }

    /**
     * Get the data that will be sent to the stats server.
     *
     * @return  array
     *
     * @since   3.5
     */
    private function getStatsData()
    {
        $data = [
            'unique_id'   => $this->getUniqueId(),
            'php_version' => PHP_VERSION,
            'db_type'     => $this->db->name,
            'db_version'  => $this->db->getVersion(),
            'cms_version' => JVERSION,
            'server_os'   => php_uname('s') . ' ' . php_uname('r'),
        ];

        // Check if we have a MariaDB version string and extract the proper version from it
        if (preg_match('/^(?:5\.5\.5-)?(mariadb-)?(?P<major>\d+)\.(?P<minor>\d+)\.(?P<patch>\d+)/i', $data['db_version'], $versionParts)) {
            $data['db_version'] = $versionParts['major'] . '.' . $versionParts['minor'] . '.' . $versionParts['patch'];
        }

        return $data;
    }

    /**
     * Get the unique id. Generates one if none is set.
     *
     * @return  integer
     *
     * @since   3.5
     */
    private function getUniqueId()
    {
        if (null === $this->uniqueId) {
            $this->uniqueId = $this->params->get('unique_id', hash('sha1', UserHelper::genRandomPassword(28) . time()));
        }

        return $this->uniqueId;
    }

    /**
     * Check if current user is allowed to send the data
     *
     * @return  boolean
     *
     * @since   3.5
     */
    private function isAllowedUser()
    {
        return Factory::getUser()->authorise('core.admin');
    }

    /**
     * Check if the debug is enabled
     *
     * @return  boolean
     *
     * @since   3.5
     */
    private function isDebugEnabled()
    {
        return defined('PLG_SYSTEM_STATS_DEBUG');
    }

    /**
     * Check if last_run + interval > now
     *
     * @return  boolean
     *
     * @since   3.5
     */
    private function isUpdateRequired()
    {
        $last     = (int) $this->params->get('lastrun', 0);
        $interval = (int) $this->params->get('interval', 12);
        $mode     = (int) $this->params->get('mode', 0);

        if ($mode === static::MODE_ALLOW_NEVER) {
            return false;
        }

        // Never updated or debug enabled
        if (!$last || $this->isDebugEnabled()) {
            return true;
        }

        return abs(time() - $last) > $interval * 3600;
    }

    /**
     * Check valid AJAX request
     *
     * @return  boolean
     *
     * @since   3.5
     */
    private function isAjaxRequest()
    {
        return strtolower($this->app->input->server->get('HTTP_X_REQUESTED_WITH', '')) === 'xmlhttprequest';
    }

    /**
     * Render a layout of this plugin
     *
     * @param   string  $layoutId  Layout identifier
     * @param   array   $data      Optional data for the layout
     *
     * @return  string
     *
     * @since   3.5
     */
    public function render($layoutId, $data = [])
    {
        $data = array_merge($this->getLayoutData(), $data);

        return $this->getRenderer($layoutId)->render($data);
    }

    /**
     * Save the plugin parameters
     *
     * @return  boolean
     *
     * @since   3.5
     */
    private function saveParams()
    {
        // Update params
        $this->params->set('lastrun', time());
        $this->params->set('unique_id', $this->getUniqueId());
        $interval = (int) $this->params->get('interval', 12);
        $this->params->set('interval', $interval ?: 12);

        $paramsJson = $this->params->toString('JSON');
        $db         = $this->db;

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('params') . ' = :params')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('stats'))
            ->bind(':params', $paramsJson);

        try {
            // Lock the tables to prevent multiple plugin executions causing a race condition
            $db->lockTable('#__extensions');
        } catch (Exception $e) {
            // If we can't lock the tables it's too risky to continue execution
            return false;
        }

        try {
            // Update the plugin parameters
            $result = $db->setQuery($query)->execute();

            $this->clearCacheGroups(['com_plugins']);
        } catch (Exception $exc) {
            // If we failed to execute
            $db->unlockTables();
            $result = false;
        }

        try {
            // Unlock the tables after writing
            $db->unlockTables();
        } catch (Exception $e) {
            // If we can't lock the tables assume we have somehow failed
            $result = false;
        }

        return $result;
    }

    /**
     * Send the stats to the stats server
     *
     * @return  boolean
     *
     * @since   3.5
     *
     * @throws  RuntimeException  If there is an error sending the data and debug mode enabled.
     */
    private function sendStats()
    {
        $error = false;

        try {
            // Don't let the request take longer than 2 seconds to avoid page timeout issues
            $response = HttpFactory::getHttp()->post($this->serverUrl, $this->getStatsData(), [], 2);

            if (!$response) {
                $error = 'Could not send site statistics to remote server: No response';
            } elseif ($response->code !== 200) {
                $data = json_decode($response->body);

                $error = 'Could not send site statistics to remote server: ' . $data->message;
            }
        } catch (UnexpectedValueException $e) {
            // There was an error sending stats. Should we do anything?
            $error = 'Could not send site statistics to remote server: ' . $e->getMessage();
        } catch (RuntimeException $e) {
            // There was an error connecting to the server or in the post request
            $error = 'Could not connect to statistics server: ' . $e->getMessage();
        } catch (Exception $e) {
            // An unexpected error in processing; don't let this failure kill the site
            $error = 'Unexpected error connecting to statistics server: ' . $e->getMessage();
        }

        if ($error !== false) {
            // Log any errors if logging enabled.
            Log::add($error, Log::WARNING, 'jerror');

            // If Stats debug mode enabled, or Global Debug mode enabled, show error to the user.
            if ($this->isDebugEnabled() || $this->app->get('debug')) {
                throw new RuntimeException($error, 500);
            }

            return false;
        }

        return true;
    }

    /**
     * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
     *
     * @param   array  $clearGroups  The cache groups to clean
     *
     * @return  void
     *
     * @since   3.5
     */
    private function clearCacheGroups(array $clearGroups)
    {
        foreach ($clearGroups as $group) {
            try {
                $options = [
                    'defaultgroup' => $group,
                    'cachebase'    => $this->app->get('cache_path', JPATH_CACHE),
                ];

                $cache = Cache::getInstance('callback', $options);
                $cache->clean();
            } catch (Exception $e) {
                // Ignore it
            }
        }
    }

    /**
     * Disable this plugin, if user selects once or never, to stop Joomla loading the plugin on every page load and
     * therefore regaining a tiny bit of performance
     *
     * @since   4.0.0
     *
     * @return  boolean
     */
    private function disablePlugin()
    {
        $db = $this->db;

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('enabled') . ' = 0')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('stats'));

        try {
            // Lock the tables to prevent multiple plugin executions causing a race condition
            $db->lockTable('#__extensions');
        } catch (Exception $e) {
            // If we can't lock the tables it's too risky to continue execution
            return false;
        }

        try {
            // Update the plugin parameters
            $result = $db->setQuery($query)->execute();

            $this->clearCacheGroups(['com_plugins']);
        } catch (Exception $exc) {
            // If we failed to execute
            $db->unlockTables();
            $result = false;
        }

        try {
            // Unlock the tables after writing
            $db->unlockTables();
        } catch (Exception $e) {
            // If we can't lock the tables assume we have somehow failed
            $result = false;
        }

        return $result;
    }

    /**
     * Are we in a Multi-factor Authentication page?
     *
     * @return  bool
     * @since   4.2.1
     */
    private function isCaptiveMFA(): bool
    {
        return method_exists($this->app, 'isMultiFactorAuthenticationPage')
            && $this->app->isMultiFactorAuthenticationPage(true);
    }
}
