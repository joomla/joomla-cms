<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use DebugBar\DataCollector\MemoryCollector;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DebugBar;
use DebugBar\OpenHandler;
use Joomla\Application\ApplicationEvents;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Log\LogEntry;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Profiler\Profiler;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\Event\ConnectionEvent;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\System\Debug\DataCollector\InfoCollector;
use Joomla\Plugin\System\Debug\DataCollector\LanguageErrorsCollector;
use Joomla\Plugin\System\Debug\DataCollector\LanguageFilesCollector;
use Joomla\Plugin\System\Debug\DataCollector\LanguageStringsCollector;
use Joomla\Plugin\System\Debug\DataCollector\ProfileCollector;
use Joomla\Plugin\System\Debug\DataCollector\QueryCollector;
use Joomla\Plugin\System\Debug\DataCollector\RequestDataCollector;
use Joomla\Plugin\System\Debug\DataCollector\SessionCollector;
use Joomla\Plugin\System\Debug\DataCollector\UserCollector;
use Joomla\Plugin\System\Debug\JavascriptRenderer;
use Joomla\Plugin\System\Debug\JoomlaHttpDriver;
use Joomla\Plugin\System\Debug\Storage\FileStorage;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Joomla! Debug plugin.
 *
 * @since  1.5
 */
class PlgSystemDebug extends CMSPlugin implements SubscriberInterface
{
    /**
     * List of protected keys that will be redacted in multiple data collected
     *
     * @since  4.2.4
     */
    public const PROTECTED_COLLECTOR_KEYS = "/password|passwd|pwd|secret|token|server_auth|_pass|smtppass|otpKey|otep/i";

    /**
     * True if debug lang is on.
     *
     * @var    boolean
     * @since  3.0
     */
    private $debugLang = false;

    /**
     * Holds log entries handled by the plugin.
     *
     * @var    LogEntry[]
     * @since  3.1
     */
    private $logEntries = [];

    /**
     * Holds SHOW PROFILES of queries.
     *
     * @var    array
     * @since  3.1.2
     */
    private $sqlShowProfiles = [];

    /**
     * Holds all SHOW PROFILE FOR QUERY n, indexed by n-1.
     *
     * @var    array
     * @since  3.1.2
     */
    private $sqlShowProfileEach = [];

    /**
     * Holds all EXPLAIN EXTENDED for all queries.
     *
     * @var    array
     * @since  3.1.2
     */
    private $explains = [];

    /**
     * Holds total amount of executed queries.
     *
     * @var    int
     * @since  3.2
     */
    private $totalQueries = 0;

    /**
     * Application object.
     *
     * @var    CMSApplicationInterface
     * @since  3.3
     */
    protected $app;

    /**
     * Database object.
     *
     * @var    DatabaseDriver
     * @since  3.8.0
     */
    protected $db;

    /**
     * @var DebugBar
     * @since 4.0.0
     */
    private $debugBar;

    /**
     * The query monitor.
     *
     * @var    \Joomla\Database\Monitor\DebugMonitor
     * @since  4.0.0
     */
    private $queryMonitor;

    /**
     * AJAX marker
     *
     * @var   bool
     * @since 4.0.0
     */
    protected $isAjax = false;

    /**
     * Whether displaing a logs is enabled
     *
     * @var   bool
     * @since 4.0.0
     */
    protected $showLogs = false;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.1.3
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeCompileHead'            => 'onBeforeCompileHead',
            'onAjaxDebug'                    => 'onAjaxDebug',
            'onBeforeRespond'                => 'onBeforeRespond',
            'onAfterRespond'                 => 'onAfterRespond',
            ApplicationEvents::AFTER_RESPOND => 'onAfterRespond',
            'onAfterDisconnect'              => 'onAfterDisconnect',
        ];
    }

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  &$subject  The object to observe.
     * @param   array                $config    An optional associative array of configuration settings.
     *
     * @since   1.5
     */
    public function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);

        $this->debugLang = $this->app->get('debug_lang');

        // Skip the plugin if debug is off
        if (!$this->debugLang && !$this->app->get('debug')) {
            return;
        }

        $this->app->getConfig()->set('gzip', false);
        ob_start();
        ob_implicit_flush(false);

        /** @var \Joomla\Database\Monitor\DebugMonitor */
        $this->queryMonitor = $this->db->getMonitor();

        if (!$this->params->get('queries', 1)) {
            // Remove the database driver monitor
            $this->db->setMonitor(null);
        }

        $this->debugBar = new DebugBar();

        // Check whether we want to track the request history for future use.
        if ($this->params->get('track_request_history', false)) {
            $storagePath = JPATH_CACHE . '/plg_system_debug_' . $this->app->getName();
            $this->debugBar->setStorage(new FileStorage($storagePath));
        }

        $this->debugBar->setHttpDriver(new JoomlaHttpDriver($this->app));

        $this->isAjax = $this->app->getInput()->get('option') === 'com_ajax'
            && $this->app->getInput()->get('plugin') === 'debug' && $this->app->getInput()->get('group') === 'system';

        $this->showLogs = (bool) $this->params->get('logs', true);

        // Log deprecated class aliases
        if ($this->showLogs && $this->app->get('log_deprecated')) {
            foreach (JLoader::getDeprecatedAliases() as $deprecation) {
                Log::add(
                    sprintf(
                        '%1$s has been aliased to %2$s and the former class name is deprecated. The alias will be removed in %3$s.',
                        $deprecation['old'],
                        $deprecation['new'],
                        $deprecation['version']
                    ),
                    Log::WARNING,
                    'deprecation-notes'
                );
            }
        }
    }

    /**
     * Add an assets for debugger.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onBeforeCompileHead()
    {
        // Only if debugging or language debug is enabled.
        if ((JDEBUG || $this->debugLang) && $this->isAuthorisedDisplayDebug() && $this->app->getDocument() instanceof HtmlDocument) {
            // Use our own jQuery and fontawesome instead of the debug bar shipped version
            $assetManager = $this->app->getDocument()->getWebAssetManager();
            $assetManager->registerAndUseStyle(
                'plg.system.debug',
                'plg_system_debug/debug.css',
                [],
                [],
                ['fontawesome']
            );
            $assetManager->registerAndUseScript(
                'plg.system.debug',
                'plg_system_debug/debug.min.js',
                [],
                ['defer' => true],
                ['jquery']
            );
        }

        // Disable asset media version if needed.
        if (JDEBUG && (int) $this->params->get('refresh_assets', 1) === 0) {
            $this->app->getDocument()->setMediaVersion('');
        }
    }

    /**
     * Show the debug info.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onAfterRespond()
    {
        // Do not collect data if debugging or language debug is not enabled.
        if (!JDEBUG && !$this->debugLang || $this->isAjax) {
            return;
        }

        // User has to be authorised to see the debug information.
        if (!$this->isAuthorisedDisplayDebug()) {
            return;
        }

        // Load language.
        $this->loadLanguage();

        $this->debugBar->addCollector(new InfoCollector($this->params, $this->debugBar->getCurrentRequestId()));
        $this->debugBar->addCollector(new UserCollector());

        if (JDEBUG) {
            if ($this->params->get('memory', 1)) {
                $this->debugBar->addCollector(new MemoryCollector());
            }

            if ($this->params->get('request', 1)) {
                $this->debugBar->addCollector(new RequestDataCollector());
            }

            if ($this->params->get('session', 1)) {
                $this->debugBar->addCollector(new SessionCollector($this->params));
            }

            if ($this->params->get('profile', 1)) {
                $this->debugBar->addCollector(new ProfileCollector($this->params));
            }

            if ($this->params->get('queries', 1)) {
                // Call $db->disconnect() here to trigger the onAfterDisconnect() method here in this class!
                $this->db->disconnect();
                $this->debugBar->addCollector(new QueryCollector($this->params, $this->queryMonitor, $this->sqlShowProfileEach, $this->explains));
            }

            if ($this->showLogs) {
                $this->collectLogs();
            }
        }

        if ($this->debugLang) {
            $this->debugBar->addCollector(new LanguageFilesCollector($this->params));
            $this->debugBar->addCollector(new LanguageStringsCollector($this->params));
            $this->debugBar->addCollector(new LanguageErrorsCollector($this->params));
        }

        // Only render for HTML output.
        if (!($this->app->getDocument() instanceof HtmlDocument)) {
            $this->debugBar->stackData();

            return;
        }

        $debugBarRenderer = new JavascriptRenderer($this->debugBar, Uri::root(true) . '/media/vendor/debugbar/');
        $openHandlerUrl   = Uri::base(true) . '/index.php?option=com_ajax&plugin=debug&group=system&format=raw&action=openhandler';
        $openHandlerUrl .= '&' . Session::getFormToken() . '=1';

        $debugBarRenderer->setOpenHandlerUrl($openHandlerUrl);

        /**
         * @todo disable highlightjs from the DebugBar, import it through NPM
         *       and deliver it through Joomla's API
         *       Also every DebugBar script and stylesheet needs to use Joomla's API
         *       $debugBarRenderer->disableVendor('highlightjs');
         */

        // Capture output.
        $contents = ob_get_contents();

        if ($contents) {
            ob_end_clean();
        }

        // No debug for Safari and Chrome redirection.
        if (
            strpos($contents, '<html><head><meta http-equiv="refresh" content="0;') === 0
            && strpos(strtolower($_SERVER['HTTP_USER_AGENT'] ?? ''), 'webkit') !== false
        ) {
            $this->debugBar->stackData();

            echo $contents;

            return;
        }

        echo str_replace('</body>', $debugBarRenderer->renderHead() . $debugBarRenderer->render() . '</body>', $contents);
    }

    /**
     * AJAX handler
     *
     * @param Joomla\Event\Event $event
     *
     * @return  void
     *
     * @since  4.0.0
     */
    public function onAjaxDebug($event)
    {
        // Do not render if debugging or language debug is not enabled.
        if (!JDEBUG && !$this->debugLang) {
            return;
        }

        // User has to be authorised to see the debug information.
        if (!$this->isAuthorisedDisplayDebug() || !Session::checkToken('request')) {
            return;
        }

        switch ($this->app->getInput()->get('action')) {
            case 'openhandler':
                $result  = $event['result'] ?: [];
                $handler = new OpenHandler($this->debugBar);

                $result[]        = $handler->handle($this->app->getInput()->request->getArray(), false, false);
                $event['result'] = $result;
        }
    }

    /**
     * Method to check if the current user is allowed to see the debug information or not.
     *
     * @return  boolean  True if access is allowed.
     *
     * @since   3.0
     */
    private function isAuthorisedDisplayDebug(): bool
    {
        static $result = null;

        if ($result !== null) {
            return $result;
        }

        // If the user is not allowed to view the output then end here.
        $filterGroups = (array) $this->params->get('filter_groups', []);

        if (!empty($filterGroups)) {
            $userGroups = $this->app->getIdentity()->get('groups');

            if (!array_intersect($filterGroups, $userGroups)) {
                $result = false;

                return false;
            }
        }

        $result = true;

        return true;
    }

    /**
     * Disconnect handler for database to collect profiling and explain information.
     *
     * @param   ConnectionEvent  $event  Event object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onAfterDisconnect(ConnectionEvent $event)
    {
        if (!JDEBUG) {
            return;
        }

        $db = $event->getDriver();

        // Remove the monitor to avoid monitoring the following queries
        $db->setMonitor(null);

        $this->totalQueries = $db->getCount();

        if ($this->params->get('query_profiles') && $db->getServerType() === 'mysql') {
            try {
                // Check if profiling is enabled.
                $db->setQuery("SHOW VARIABLES LIKE 'have_profiling'");
                $hasProfiling = $db->loadResult();

                if ($hasProfiling) {
                    // Run a SHOW PROFILE query.
                    $db->setQuery('SHOW PROFILES');
                    $this->sqlShowProfiles = $db->loadAssocList();

                    if ($this->sqlShowProfiles) {
                        foreach ($this->sqlShowProfiles as $qn) {
                            // Run SHOW PROFILE FOR QUERY for each query where a profile is available (max 100).
                            $db->setQuery('SHOW PROFILE FOR QUERY ' . (int) $qn['Query_ID']);
                            $this->sqlShowProfileEach[(int) ($qn['Query_ID'] - 1)] = $db->loadAssocList();
                        }
                    }
                } else {
                    $this->sqlShowProfileEach[0] = [['Error' => 'MySql have_profiling = off']];
                }
            } catch (Exception $e) {
                $this->sqlShowProfileEach[0] = [['Error' => $e->getMessage()]];
            }
        }

        if ($this->params->get('query_explains') && in_array($db->getServerType(), ['mysql', 'postgresql'], true)) {
            $logs        = $this->queryMonitor->getLogs();
            $boundParams = $this->queryMonitor->getBoundParams();

            foreach ($logs as $k => $query) {
                $dbVersion56 = $db->getServerType() === 'mysql' && version_compare($db->getVersion(), '5.6', '>=');
                $dbVersion80 = $db->getServerType() === 'mysql' && version_compare($db->getVersion(), '8.0', '>=');

                if ($dbVersion80) {
                    $dbVersion56 = false;
                }

                if ((stripos($query, 'select') === 0) || ($dbVersion56 && ((stripos($query, 'delete') === 0) || (stripos($query, 'update') === 0)))) {
                    try {
                        $queryInstance = $db->getQuery(true);
                        $queryInstance->setQuery('EXPLAIN ' . ($dbVersion56 ? 'EXTENDED ' : '') . $query);

                        if ($boundParams[$k]) {
                            foreach ($boundParams[$k] as $key => $obj) {
                                $queryInstance->bind($key, $obj->value, $obj->dataType, $obj->length, $obj->driverOptions);
                            }
                        }

                        $this->explains[$k] = $db->setQuery($queryInstance)->loadAssocList();
                    } catch (Exception $e) {
                        $this->explains[$k] = [['error' => $e->getMessage()]];
                    }
                }
            }
        }
    }

    /**
     * Store log messages so they can be displayed later.
     * This function is passed log entries by JLogLoggerCallback.
     *
     * @param   LogEntry  $entry  A log entry.
     *
     * @return  void
     *
     * @since   3.1
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use \Joomla\CMS\Log\Log::add(LogEntry $entry) instead
     */
    public function logger(LogEntry $entry)
    {
        if (!$this->showLogs) {
            return;
        }

        $this->logEntries[] = $entry;
    }

    /**
     * Collect log messages.
     *
     * @return $this
     *
     * @since 4.0.0
     */
    private function collectLogs(): self
    {
        $loggerOptions = ['group' => 'default'];
        $logger        = new Joomla\CMS\Log\Logger\InMemoryLogger($loggerOptions);
        $logEntries    = $logger->getCollectedEntries();

        if (!$this->logEntries && !$logEntries) {
            return $this;
        }

        if ($this->logEntries) {
            $logEntries = array_merge($logEntries, $this->logEntries);
        }

        $logDeprecated     = $this->app->get('log_deprecated', 0);
        $logDeprecatedCore = $this->params->get('log-deprecated-core', 0);

        $this->debugBar->addCollector(new MessagesCollector('log'));

        if ($logDeprecated) {
            $this->debugBar->addCollector(new MessagesCollector('deprecated'));
            $this->debugBar->addCollector(new MessagesCollector('deprecation-notes'));
        }

        if ($logDeprecatedCore) {
            $this->debugBar->addCollector(new MessagesCollector('deprecated-core'));
        }

        foreach ($logEntries as $entry) {
            switch ($entry->category) {
                case 'deprecation-notes':
                    if ($logDeprecated) {
                        $this->debugBar[$entry->category]->addMessage($entry->message);
                    }
                    break;
                case 'deprecated':
                    if (!$logDeprecated && !$logDeprecatedCore) {
                        break;
                    }

                    $file = '';
                    $line = '';

                    // Find the caller, skip Log methods and trigger_error function
                    foreach ($entry->callStack as $stackEntry) {
                        if (
                            !empty($stackEntry['class'])
                            && ($stackEntry['class'] === 'Joomla\CMS\Log\LogEntry' || $stackEntry['class'] === 'Joomla\CMS\Log\Log')
                        ) {
                            continue;
                        }

                        if (
                            empty($stackEntry['class']) && !empty($stackEntry['function'])
                            && $stackEntry['function'] === 'trigger_error'
                        ) {
                            continue;
                        }

                        $file = $stackEntry['file'] ?? '';
                        $line = $stackEntry['line'] ?? '';

                        break;
                    }

                    $category = $entry->category;
                    $relative = $file ? str_replace(JPATH_ROOT, '', $file) : '';

                    if ($relative && 0 === strpos($relative, '/libraries/src')) {
                        if (!$logDeprecatedCore) {
                            break;
                        }

                        $category .= '-core';
                    } elseif (!$logDeprecated) {
                        break;
                    }

                    $message = [
                        'message' => $entry->message,
                        'caller'  => $file . ':' . $line,
                        // @todo 'stack' => $entry->callStack;
                    ];
                    $this->debugBar[$category]->addMessage($message, 'warning');
                    break;

                case 'databasequery':
                    // Should be collected by its own collector
                    break;

                default:
                    switch ($entry->priority) {
                        case Log::EMERGENCY:
                        case Log::ALERT:
                        case Log::CRITICAL:
                        case Log::ERROR:
                            $level = 'error';
                            break;
                        case Log::WARNING:
                            $level = 'warning';
                            break;
                        default:
                            $level = 'info';
                    }

                    $this->debugBar['log']->addMessage($entry->category . ' - ' . $entry->message, $level);
                    break;
            }
        }

        return $this;
    }

    /**
     * Add server timing headers when profile is activated.
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function onBeforeRespond(): void
    {
        if (!JDEBUG || !$this->params->get('profile', 1)) {
            return;
        }

        $metrics    = '';
        $moduleTime = 0;
        $accessTime = 0;

        foreach (Profiler::getInstance('Application')->getMarks() as $index => $mark) {
            // Ignore the before mark as the after one contains the timing of the action
            if (stripos($mark->label, 'before') !== false) {
                continue;
            }

            // Collect the module render time
            if (strpos($mark->label, 'mod_') !== false) {
                $moduleTime += $mark->time;
                continue;
            }

            // Collect the access render time
            if (strpos($mark->label, 'Access:') !== false) {
                $accessTime += $mark->time;
                continue;
            }

            $desc     = str_ireplace('after', '', $mark->label);
            $name     = preg_replace('/[^\da-z]/i', '', $desc);
            $metrics .= sprintf('%s;dur=%f;desc="%s", ', $index . $name, $mark->time, $desc);

            // Do not create too large headers, some web servers don't love them
            if (strlen($metrics) > 3000) {
                $metrics .= 'System;dur=0;desc="Data truncated to 3000 characters", ';
                break;
            }
        }

        // Add the module entry
        $metrics .= 'Modules;dur=' . $moduleTime . ';desc="Modules", ';

        // Add the access entry
        $metrics .= 'Access;dur=' . $accessTime . ';desc="Access"';

        $this->app->setHeader('Server-Timing', $metrics);
    }
}
