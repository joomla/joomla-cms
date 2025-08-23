<?php

/**
 * @package     Joomla.Plugins
 * @subpackage  Task.SiteStatus
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\SiteStatus\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Task plugin with routines to change the offline status of the site. These routines can be used to control planned
 * maintenance periods and related operations.
 *
 * @since  4.1.0
 */
final class SiteStatus extends CMSPlugin implements SubscriberInterface
{
    use TaskPluginTrait;

    /**
     * @var string[]
     * @since 4.1.0
     */
    protected const TASKS_MAP = [
        'plg_task_toggle_offline' => [
            'langConstPrefix' => 'PLG_TASK_SITE_STATUS',
            'toggle'          => true,
        ],
        'plg_task_toggle_offline_set_online' => [
            'langConstPrefix' => 'PLG_TASK_SITE_STATUS_SET_ONLINE',
            'toggle'          => false,
            'offline'         => false,
        ],
        'plg_task_toggle_offline_set_offline' => [
            'langConstPrefix' => 'PLG_TASK_SITE_STATUS_SET_OFFLINE',
            'toggle'          => false,
            'offline'         => true,
        ],

    ];

    /**
     * Autoload the language file.
     *
     * @var boolean
     * @since 4.1.0
     */
    protected $autoloadLanguage = true;

    /**
     * @inheritDoc
     *
     * @return string[]
     *
     * @since 4.1.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList' => 'advertiseRoutines',
            'onExecuteTask'     => 'alterSiteStatus',
        ];
    }

    /**
     * The old config
     *
     * @var    array
     * @since  4.2.0
     */
    private $oldConfig;

    /**
     * The config file
     *
     * @var    string
     * @since  4.2.0
     */
    private $configFile;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher  The dispatcher
     * @param   array                $config      An optional associative array of configuration settings
     * @param   array                $oldConfig   The old config
     * @param   string               $configFile  The config
     *
     * @since   4.2.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, array $oldConfig, string $configFile)
    {
        parent::__construct($dispatcher, $config);

        $this->oldConfig  = $oldConfig;
        $this->configFile = $configFile;
    }

    /**
     * @param   ExecuteTaskEvent  $event  The onExecuteTask event
     *
     * @return void
     *
     * @since 4.1.0
     * @throws \Exception
     */
    public function alterSiteStatus(ExecuteTaskEvent $event): void
    {
        if (!\array_key_exists($event->getRoutineId(), self::TASKS_MAP)) {
            return;
        }

        $this->startRoutine($event);

        $config = $this->oldConfig;

        $toggle    = self::TASKS_MAP[$event->getRoutineId()]['toggle'];
        $oldStatus = $config['offline'] ? 'offline' : 'online';

        if ($toggle) {
            $config['offline'] = !$config['offline'];
        } else {
            $config['offline'] = self::TASKS_MAP[$event->getRoutineId()]['offline'];
        }

        $newStatus = $config['offline'] ? 'offline' : 'online';
        $exit      = $this->writeConfigFile(new Registry($config));
        $this->logTask(\sprintf($this->getApplication()->getLanguage()->_('PLG_TASK_SITE_STATUS_TASK_LOG_SITE_STATUS'), $oldStatus, $newStatus));

        $this->endRoutine($event, $exit);
    }

    /**
     * Method to write the configuration to a file.
     *
     * @param   Registry  $config  A Registry object containing all global config data.
     *
     * @return  integer  The task exit code
     *
     * @since  4.1.0
     * @throws \Exception
     */
    private function writeConfigFile(Registry $config): int
    {
        // Set the configuration file path.
        $file = $this->configFile;

        // Attempt to make the file writeable.
        if (file_exists($file) && Path::isOwner($file) && !Path::setPermissions($file)) {
            $this->logTask($this->getApplication()->getLanguage()->_('PLG_TASK_SITE_STATUS_ERROR_CONFIGURATION_PHP_NOTWRITABLE'), 'notice');
        }

        try {
            // Attempt to write the configuration file as a PHP class named JConfig.
            $configuration = $config->toString('PHP', ['class' => 'JConfig', 'closingtag' => false]);
            File::write($file, $configuration);
        } catch (\Exception) {
            $this->logTask($this->getApplication()->getLanguage()->_('PLG_TASK_SITE_STATUS_ERROR_WRITE_FAILED'), 'error');

            return Status::KNOCKOUT;
        }

        // Invalidates the cached configuration file
        if (\function_exists('opcache_invalidate')) {
            opcache_invalidate($file);
        }

        // Attempt to make the file un-writeable.
        if (Path::isOwner($file) && !Path::setPermissions($file, '0444')) {
            $this->logTask($this->getApplication()->getLanguage()->_('PLG_TASK_SITE_STATUS_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'), 'notice');
        }

        return Status::OK;
    }
}
