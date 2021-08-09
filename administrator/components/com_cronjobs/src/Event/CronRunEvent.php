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

namespace Joomla\Component\Cronjobs\Administrator\Event;

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractEvent;
use BadMethodCallException;

/**
 * Event class for onCronRun event.
 *
 * @since  __DEPLOY_VERSION__
 */
class CronRunEvent extends AbstractEvent
{
	/**
	 * Constructor.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 *
	 * @since   4.0.0
	 */
	public function __construct($name, array $arguments = array())
	{
		$arguments['resultSnapshot'] = null;

		parent::__construct($name, $arguments);
	}

	/**
	 * @param   array  $snapshot   The job snapshot.
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function setResult(array $snapshot = []): void
	{
		$this->arguments['resultSnapshot'] = $snapshot;

		if ($snapshot)
		{
			$this->stopPropagation();
		}
	}
}
