<?php
/**
 * Declares the CronjobsModel MVC Model.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\Helper;

// Restrict direct access
defined('_JEXEC') or die;

use Cron\CronExpression;
use DateInterval;
use DateTime;
use Exception;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
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
	 * @since __DEPLOY_VERSION__
	 */
	private $type;

	/**
	 * @var array
	 * @since __DEPLOY_VERSION__
	 */
	private $cronjob;

	/**
	 * @var object
	 * @since __DEPLOY_VERSION__
	 */
	private $rule;

	/**
	 * @param   array|object  $cronjob  A cronjob entry
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct($cronjob)
	{
		$this->cronjob = is_array($cronjob) ? $cronjob : ArrayHelper::fromObject($cronjob);
		$rule = $this->getFromCronjob('cron_rules');
		$this->rule = is_string($rule) ? json_decode($rule) : is_array($rule) ? (object) $rule : $rule;
		$this->type = $this->rule->type;
	}

	/**
	 * Get a property from the cronjob array
	 *
	 * @param   string  $property  The property to get
	 * @param   mixed   $default   The default value returned if property does not exist
	 *
	 * @return mixed
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function getFromCronjob(string $property, $default = null)
	{
		$property = ArrayHelper::getValue($this->cronjob, $property);

		return $property ?? $default;
	}

	/**
	 * @param   boolean  $string  If true, an SQL formatted string is returned.
	 *
	 * @return ?Date|string
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function nextExec(bool $string = true)
	{
		// Exception handling here
		switch ($this->type)
		{
			case 'interval':
				$lastExec = Factory::getDate($this->getFromCronjob('last_execution'), 'GMT');
				$interval = new DateInterval($this->rule->exp);
				$nextExec = $lastExec->add($interval);
				$nextExec = $string ? $nextExec->toSql() : $nextExec;
				break;
			case 'cron':
				// TODO: testing
				$cExp = new CronExpression((string) $this->rule->exp);
				$nextExec = $cExp->getNextRunDate('now', 0, false, 'GMT');
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
	 * @since __DEPLOY_VERSION__
	 */
	private function dateTimeToSql(DateTime $dateTime): string
	{
		// TODO: Get DBO from DI container
		static $db;
		$db = $db ?? Factory::getDbo();

		return $dateTime->format($db->getDateFormat());
	}
}
