<?php
/**
 * @package       Joomla.Administrator
 * @subpackage    com_scheduler
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

/** Implements the helper class ExecRuleHelper. */

namespace Joomla\Component\Scheduler\Administrator\Helper;

// Restrict direct access
defined('_JEXEC') or die;

use Cron\CronExpression;
use DateInterval;
use DateTime;
use Exception;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseDriver;
use Joomla\Utilities\ArrayHelper;

/**
 * Helper class for supported execution rules.
 *
 * @since  __DEPLOY_VERSION__
 */
class ExecRuleHelper
{
	/**
	 * The execution rule type
	 *
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	private $type;

	/**
	 * @var array
	 * @since  __DEPLOY_VERSION__
	 */
	private $task;

	/**
	 * @var object
	 * @since  __DEPLOY_VERSION__
	 */
	private $rule;

	/**
	 * @param   array|object  $task  A task entry
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($task)
	{
		$this->task = is_array($task) ? $task : ArrayHelper::fromObject($task);
		$rule = $this->getFromTask('cron_rules');
		$this->rule = is_string($rule)
			? (object) json_decode($rule)
			: (is_array($rule) ? (object) $rule : $rule);
		$this->type = $this->rule->type;
	}

	/**
	 * Get a property from the task array
	 *
	 * @param   string  $property  The property to get
	 * @param   mixed   $default   The default value returned if property does not exist
	 *
	 * @return mixed
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function getFromTask(string $property, $default = null)
	{
		$property = ArrayHelper::getValue($this->task, $property);

		return $property ?? $default;
	}

	/**
	 * @param   boolean  $string  If true, an SQL formatted string is returned.
	 *
	 * @return ?Date|string
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function nextExec(bool $string = true, bool $basisNow = false)
	{
		// Exception handling here
		switch ($this->type)
		{
			case 'interval':
				$lastExec = Factory::getDate($basisNow ? 'now' : $this->getFromTask('last_execution'), 'UTC');
				$interval = new DateInterval($this->rule->exp);
				$nextExec = $lastExec->add($interval);
				$nextExec = $string ? $nextExec->toSql() : $nextExec;
				break;
			case 'cron':
				// @todo: testing
				$cExp = new CronExpression((string) $this->rule->exp);
				$nextExec = $cExp->getNextRunDate('now', 0, false, 'UTC');
				$nextExec = $string ? $this->dateTimeToSql($nextExec) : $nextExec;
				break;
			default:
				$nextExec = null;
		}

		return $nextExec;
	}

	/**
	 * Returns a sql-formatted string for a DateTime object.
	 * Only needed for DateTime objects returned by CronExpression, JDate supports this as class method.
	 *
	 * @param   DateTime  $dateTime  A DateTime object to format
	 *
	 * @return string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function dateTimeToSql(DateTime $dateTime): string
	{
		static $db;
		$db = $db ?? Factory::getContainer()->get(DatabaseDriver::class);

		return $dateTime->format($db->getDateFormat());
	}
}
