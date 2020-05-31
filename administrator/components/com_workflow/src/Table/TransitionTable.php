<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Transition table
 *
 * @since  4.0.0
 */
class TransitionTable extends Table
{
	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;

	/**
	 * An array of key names to be json encoded in the bind function
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $_jsonEncode = [
		'options'
	];

	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database connector object
	 *
	 * @since  4.0.0
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__workflow_transitions', 'id', $db);

		$this->access = (int) Factory::getApplication()->get('access');
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since  4.0.0
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;
		$workflow = new WorkflowTable($this->getDbo());
		$workflow->load($this->workflow_id);

		$parts = explode('.', $workflow->extension);

		$extension = array_shift($parts);

		return $extension . '.transition.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 *
	 * @since  4.0.0
	 */
	protected function _getAssetTitle()
	{
		return $this->title;
	}

	/**
	 * Get the parent asset id for the record
	 *
	 * @param   Table    $table  A JTable object for the asset parent.
	 * @param   integer  $id     The id for the asset
	 *
	 * @return  integer  The id of the asset's parent
	 *
	 * @since  4.0.0
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		$asset = self::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));

		$workflow = new WorkflowTable($this->getDbo());
		$workflow->load($this->workflow_id);

		$parts = explode('.', $workflow->extension);

		$extension = array_shift($parts);

		$name = $extension . '.workflow.' . (int) $workflow->id;

		$asset->loadByName($name);
		$assetId = $asset->id;

		return !empty($assetId) ? $assetId : parent::_getAssetParentId($table, $id);
	}
}
