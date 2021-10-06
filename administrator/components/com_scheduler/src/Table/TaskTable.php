<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/** phpcs:disable Joomla.NamingConventions.ValidVariableName.MemberNotCamelCaps,Joomla.NamingConventions.ValidVariableName.ClassVarHasUnderscore,Joomla.NamingConventions.ValidFunctionName.MethodUnderscore */

namespace Joomla\Component\Scheduler\Administrator\Table;

// Restrict direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Asset;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Table class for tasks scheduled through `com_scheduler`.
 * The type alias for Task table entries is `com_scheduler.task`.
 *
 * @since  __DEPLOY_VERSION__
 */
class TaskTable extends Table
{
	/**
	 * Ensure params are json encoded by the bind method.
	 *
	 * @var string[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $_jsonEncode = ['params', 'execution_rules', 'cron_rules'];

	/**
	 * The 'created' column.
	 *
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	public $created;

	/**
	 * The 'title' column.
	 *
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	public $title;

	/**
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	public $typeAlias = 'com_scheduler.task';


	/**
	 * TaskTable constructor override, needed to pass the DB table name and primary key to {@see Table::__construct()}.
	 *
	 * @param   DatabaseDriver  $db  A database connector object.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->setColumnAlias('published', 'state');

		parent::__construct('#__scheduler_tasks', 'id', $db);
	}

	/**
	 * Overloads {@see Table::check()} to perform sanity checks on properties and make sure they're
	 * safe to store.
	 *
	 * @return boolean  True if checks pass.
	 *
	 * @throws \Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function check(): bool
	{
		try
		{
			parent::check();
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage());

			return false;
		}

		$this->title = htmlspecialchars_decode($this->title, ENT_QUOTES);

		// Set created date if not set.
		// ? Might not need since the constructor already sets this
		if (!(int) $this->created)
		{
			$this->created = Factory::getDate()->toSql();
		}

		// @todo : Add more checks if needed

		return true;
	}

	/**
	 * Override {@see Table::store()} to update null fields as a default, which is needed when DATETIME
	 * fields need to be updated to NULL. This override is needed because {@see AdminModel::save()} does not
	 * expose an option to pass true to Table::store(). Also ensures the `created` and `created_by` fields are
	 * set.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they're null.
	 *
	 * @return boolean  True if successful.
	 *
	 * @since  __DEPLOY_VERSION__
	 * @throws \Exception
	 */
	public function store($updateNulls = true): bool
	{
		$isNew = empty($this->getId());

		// Set creation date if not set for a new item.
		if ($isNew && empty($this->created))
		{
			$this->created = Factory::getDate()->toSql();
		}

		// Set `created_by` if not set for a new item.
		if ($isNew && empty($this->created_by))
		{
			$this->created_by = Factory::getApplication()->getIdentity()->id;
		}

		// @todo : Should we add modified, modified_by fields? [ ]

		return parent::store($updateNulls);
	}

	/**
	 * Returns the asset name of the entry as it appears in the {@see Asset} table.
	 *
	 * @return string  The asset name.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function _getAssetName(): string
	{
		$k = $this->_tbl_key;

		return 'com_scheduler.task.' . (int) $this->$k;
	}

	/**
	 * Override {@see Table::bind()} to bind some fields even if they're null given they're present in $src.
	 * This override is needed specifically for DATETIME fields, of which the `next_execution` field is updated to
	 * null if a task is configured to execute only on manual trigger.
	 *
	 * @param   array|object  $src     An associative array or object to bind to the Table instance.
	 * @param   array|string  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return boolean
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function bind($src, $ignore = array()): bool
	{
		$fields = ['next_execution'];

		foreach ($fields as $field)
		{
			if (array_key_exists($field, $src) && is_null($src[$field]))
			{
				$this->$field = $src[$field];
			}
		}

		return parent::bind($src, $ignore);
	}
}
