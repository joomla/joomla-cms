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
defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use function defined;

/**
 * The main DB Table class for com_cronjobs
 *
 * @since  __DEPLOY_VERSION__
 */
class CronjobTable extends Table
{
	/**
	 * Indicates that columns do not fully support the NULL value in the database
	 *
	 * @var boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $_supportNullValue = false;

	/**
	 * Ensure params are json encoded by the bind method
	 *
	 * @var string[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $_jsonEncode = ['params', 'execution_rules', 'cron_rules'];

	/**
	 * Injected into the 'created' column
	 *
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	public $created;

	/**
	 * Injected into the 'title' column
	 *
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	public $title;

	/**
	 * CronjobTable constructor.
	 * Just passes the DB table name and primary key name to parent constructor.
	 *
	 * @param   DatabaseDriver  $db  A database connector object (?)
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias = 'com_cronjobs.cronjob';
		$this->created = Factory::getDate()->toSql();

		$this->setColumnAlias('published', 'state');

		parent::__construct('#__cronjobs', 'id', $db);
	}

	/**
	 * Overloads the parent check function.
	 * Performs sanity checks on properties to make
	 * sure they're safe to store in the DB.
	 *
	 * @return boolean  True if checks were successful
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function check(): bool
	{
		try
		{
			parent::check();
		}
		catch (Exception $e)
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

		// TODO : Add more checks if needed

		return true;
	}

	/**
	 * @param   boolean  $updateNulls  True to update fields even if they're null [?]
	 *
	 * @return boolean  True if successful [yes?]
	 *
	 * @since  __DEPLOY_VERSION__
	 * @throws Exception
	 */
	public function store($updateNulls = false): bool
	{
		$date = Factory::getDate()->toSql();
		$userId = Factory::getApplication()->getIdentity();

		// Set creation date if not set
		if (!(int) $this->created)
		{
			$this->created = $date;
		}

		// TODO : Should we add modified, modified_by fields? [ ]

		// Set created_by if needed
		if (empty($this->created_by))
		{
			$this->created_by = $userId;
		}

		return parent::store($updateNulls);
	}

	/**
	 * Declares the assetName for the entry as in the `#__assets` table
	 *
	 * @return string  The asset name
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function _getAssetName(): string
	{
		$k = $this->_tbl_key;

		return 'com_cronjobs.cronjob.' . (int) $this->$k;
	}

}
