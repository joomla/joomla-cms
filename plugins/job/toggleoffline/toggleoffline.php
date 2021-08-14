<?php
/**
 * A job plugin to toggle the offline status of the site.
 *
 * @package       Joomla.Plugins
 * @subpackage    System.testjob
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Cronjobs\Administrator\Event\CronRunEvent;
use Joomla\Component\Cronjobs\Administrator\Traits\CronjobPluginTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * The plugin class
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgJobToggleoffline extends CMSPlugin implements SubscriberInterface
{
	use CronjobPluginTrait;

	protected const JOBS_MAP = [
		'plg_job_toggle_offline' => [
			'langConstPrefix' => 'PLG_JOB_TOGGLE_OFFLINE'
		]
	];

	/**
	 * The application object
	 *
	 * @var  CMSApplication
	 * @since __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Autoload the language file
	 *
	 * @var boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * An array of supported Form contexts
	 *
	 * @var string[]
	 * @since __DEPLOY_VERSION__
	 */
	private $supportedFormContexts = [
		'com_cronjobs.cronjob'
	];

	/**
	 * Returns event subscriptions
	 *
	 * @return string[]
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onCronOptionsList' => 'advertiseJobs',
			'onCronRun' => 'toggleOffline',
		];
	}

	/**
	 * @param   CronRunEvent  $event  The onCronRun event
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function toggleOffline(CronRunEvent $event): void
	{
		if (!array_key_exists($event->getJobId(), self::JOBS_MAP))
		{
			return;
		}

		$this->jobStart();

		$config = ArrayHelper::fromObject(new JConfig);

		$config['offline'] = !$config['offline'];
		$this->writeConfigFile(new Registry($config));

		$this->jobEnd($event, 0);
	}

	/**
	 * Method to write the configuration to a file.
	 *
	 * @param   Registry  $config  A Registry object containing all global config data.
	 *
	 * @return  int  The job exit code
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function writeConfigFile(Registry $config): int
	{
		// Set the configuration file path.
		$file = JPATH_CONFIGURATION . '/configuration.php';

		// Attempt to make the file writeable.
		if (Path::isOwner($file) && !Path::setPermissions($file))
		{
			$this->addJobLog(Text::_('PLG_JOB_TOGGLE_OFFLINE_ERROR_CONFIGURATION_PHP_NOTWRITABLE'), 'notice');
		}

		// Attempt to write the configuration file as a PHP class named JConfig.
		$configuration = $config->toString('PHP', array('class' => 'JConfig', 'closingtag' => false));

		if (!File::write($file, $configuration))
		{
			$this->addJobLog(Text::_('PLG_JOB_TOGGLE_OFFLINE_ERROR_WRITE_FAILED'), 'error');

			return self::$STATUS['KO_RUN'];
		}

		// Invalidates the cached configuration file
		if (function_exists('opcache_invalidate'))
		{
			opcache_invalidate($file);
		}

		// Attempt to make the file un-writeable.
		if (Path::isOwner($file) && !Path::setPermissions($file, '0444'))
		{
			$this->addJobLog(Text::_('PLG_JOB_TOGGLE_OFFLINE_ERROR_CONFIGURATION_PHP_NOTUNWRITABLE'), 'notice');
		}

		return self::$STATUS['OK_RUN'];
	}
}
