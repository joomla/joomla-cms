<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  Task.SiteStatus
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Task plugin with routines to change the offline status of the site. These routines can be used to control planned
 * maintenance periods and related operations.
 *
 * @since  4.1.0
 */
class PlgTaskSitestatus extends CMSPlugin implements SubscriberInterface
{
	use TaskPluginTrait;

	/**
	 * @var string[]
	 * @since 4.1.0
	 */
	protected const TASKS_MAP = [
		'plg_task_toggle_offline'             => [
			'langConstPrefix' => 'PLG_TASK_SITE_STATUS',
			'toggle'          => true,
		],
		'plg_task_toggle_offline_set_online'  => [
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
	 * The application object.
	 *
	 * @var  CMSApplication
	 * @since 4.1.0
	 */
	protected $app;

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
	 * @param   ExecuteTaskEvent  $event  The onExecuteTask event
	 *
	 * @return void
	 *
	 * @since 4.1.0
	 * @throws Exception
	 */
	public function alterSiteStatus(ExecuteTaskEvent $event): void
	{
		if (!array_key_exists($event->getRoutineId(), self::TASKS_MAP))
		{
			return;
		}

		$this->startRoutine($event);

		$config = ArrayHelper::fromObject(new JConfig);

		$toggle    = self::TASKS_MAP[$event->getRoutineId()]['toggle'];
		$oldStatus = $config['offline'] ? 'offline' : 'online';

		if ($toggle)
		{
			$config['offline'] = !$config['offline'];
		}
		else
		{
			$offline           = self::TASKS_MAP[$event->getRoutineId()]['offline'];
			$config['offline'] = $offline;
		}

		$newStatus = $config['offline'] ? 'offline' : 'online';
		$exit      = $this->writeConfigFile(new Registry($config));
		$this->logTask(Text::sprintf('PLG_TASK_SITE_STATUS_TASK_LOG_SITE_STATUS', $oldStatus, $newStatus));

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
	 * @throws Exception
	 */
	private function writeConfigFile(Registry $config): int
	{
		// Set the configuration file path.
		$file = JPATH_CONFIGURATION . '/configuration.php';

		// Attempt to make the file writeable.
		if (Path::isOwner($file) && !Path::setPermissions($file))
		{
			$this->logTask(Text::_('PLG_TASK_SITE_STATUS_ERROR_CONFIGURATION_PHP_NOTWRITABLE'), 'notice');
		}

		// Attempt to write the configuration file as a PHP class named JConfig.
		$configuration = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));

		if (!File::write($file, $configuration))
		{
			$this->logTask(Text::_('PLG_TASK_SITE_STATUS_ERROR_WRITE_FAILED'), 'error');

			return Status::KNOCKOUT;
		}

		// Invalidates the cached configuration file
		if (function_exists('opcache_invalidate'))
		{
			opcache_invalidate($file);
		}

		// Attempt to make the file un-writeable.
		if (Path::isOwner($file) && !Path::setPermissions($file, '0444'))
		{
			$this->logTask(Text::_('PLG_TASK_SITE_STATUS_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'), 'notice');
		}

		return Status::OK;
	}
}
