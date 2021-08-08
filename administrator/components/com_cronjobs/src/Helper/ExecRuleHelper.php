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
use DateInterval;
use DateTimeZone;
use Exception;
use Joomla\CMS\Date\Date;

defined('_JEXEC') or die;

/**
 * Helper class for supported execution rules.
 *
 * @since  __DEPLOY_VERSION__
 */
class ExecRuleHelper
{
	/**
	 * The execution rule type
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
	 * @param   object  $cronjob   A cronjob entry in a class with ::get()
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(object $cronjob)
	{
		$this->cronjob = $cronjob;
		$this->rule = json_decode($cronjob->get('cron_rules'));
		$this->type = $this->rule->type;
	}

	/**
	 * @param   boolean  $string   If true, an SQL formatted string is returned.
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
				$lastExec = new Date($this->cronjob->get('last_execution'), new DateTimeZone('GMT'));
				$interval = new DateInterval($this->rule->exp);
				$nextExec = $lastExec->add($interval);
				$nextExec = $string ? $nextExec->toSql() : $nextExec;
				break;
			case 'cron':
				// Cannot handle cron expressions yet
				$nextExec = null;
				break;
			default:
				$nextExec = null;
		}

		return $nextExec;
	}
}
