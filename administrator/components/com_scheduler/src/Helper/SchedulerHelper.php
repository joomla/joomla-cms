<?php
/**
 * @package       Joomla.Administrator
 * @subpackage    com_scheduler
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Implements the SchedulerHelper class. */

namespace Joomla\Component\Scheduler\Administrator\Helper;

// Restrict direct access
defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Scheduler\Administrator\Task\TaskOptions;
use function defined;

/**
 * The SchedulerHelper class.
 * Provides static methods used across com_scheduler
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class SchedulerHelper
{
	/**
	 * Cached TaskOptions object
	 *
	 * @var  TaskOptions
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $taskOptionsCache;


	/**
	 * Returns available task routines as a TaskOptions object.
	 *
	 * @return  TaskOptions  A TaskOptions object populated with task routines offered by plugins
	 * @throws  Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getTaskOptions(): TaskOptions
	{
		if (self::$taskOptionsCache !== null)
		{
			return self::$taskOptionsCache;
		}

		/** @var  AdministratorApplication  $app */
		$app = Factory::getApplication();
		$options = new TaskOptions;
		$event = AbstractEvent::create(
			'onTaskOptionsList',
			[
				'subject' => $options
			]
		);

		PluginHelper::importPlugin('task');
		$app->getDispatcher()->dispatch('onTaskOptionsList', $event);

		self::$taskOptionsCache = $options;

		return $options;
	}
}
