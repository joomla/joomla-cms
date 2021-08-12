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
	 * @var object
	 * @since __DEPLOY_VERSION__
	 */
	private $cronjob;

	/**
	 * @var object
	 * @since __DEPLOY_VERSION__
	 */
	private $rule;

	/**
	 * @param   object  $cronjob  A cronjob entry in a class with ::get()
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(object $cronjob)
	{
		$this->cronjob = $cronjob;
		$this->rule = json_decode($cronjob->get('cron_rules'));
		$this->type = $this->rule->type;
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
				$lastExec = Factory::getDate($this->cronjob->get('last_execution'), 'GMT');
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
		static $db;
		$db = $db ?? Factory::getDbo();

		return $dateTime->format($db->getDateFormat());
	}
}
