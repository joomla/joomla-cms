<?php
/**
 * Declares the main Table object for com_cronjobs
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\Table;

// Restrict direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * The main DB Table object for com_cronjobs
 *
 * @since __DEPLOY_VERSION__
 */
class CronjobTable extends Table
{
	/**
	 * Indicates that columns do not fully support the NULL value in the database
	 *
	 * @var boolean
	 * @since __DEPLOY_VERSION__
	 */
	protected $_supportNullValue = false;

	/**
	 * Injected into the 'created' column
	 *
	 * @var string
	 * @since __DEPLOY_VERSION__
	 */
	private $created;

	/**
	 * CronjobTable constructor.
	 * Just passes the DB table name and primary key name to parent constructor.
	 *
	 * ? : How do we incorporate the supporting job type tables?
	 *     Is the solution a Table class for each of them
	 *     Or can we have a more elegant arrangement?
	 *
	 * @param   DatabaseDriver  $db  A database connector object (?)
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_cronjobs.cronjob';
		$this->created = Factory::getDate()->toSql();

		// Just for the sake of it, might remove
		$this->setColumnAlias('state', 'published');

		parent::__construct('#__cronjobs', 'id', $db);
	}
}
