<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

/**
 * Category table
 *
 * @since  __DEPLOY_VERSION__
 */
class TransitionTable extends Table
{
	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database connector object
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct(\JDatabaseDriver $db)
	{
		parent::__construct('#__workflow_transitions', 'id', $db);

		$this->access = (int) Factory::getConfig()->get('access');
	}

	/**
	 * Method to compute the default name of the asset.
	 * The default name is in the form table_name.id
	 * where id is the value of the primary key of the table.
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;
		$workflow = new WorkflowTable($this->getDbo());
		$workflow->load($this->workflow_id);

		return $workflow->extension . '.transition.' . (int) $this->$k;
	}

	/**
	 * Method to return the title to use for the asset table.
	 *
	 * @return  string
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @since  __DEPLOY_VERSION__
	 */
	protected function _getAssetParentId(Table $table = null, $id = null)
	{
		$asset = self::getInstance('Asset', 'JTable', array('dbo' => $this->getDbo()));
		$workflow = new WorkflowTable($this->getDbo());
		$workflow->load($this->workflow_id);
		$name = $workflow->extension . '.workflow.' . (int) $workflow->id;
		$asset->loadByName($name);
		$assetId = $asset->id;

		return !empty($assetId) ? $assetId : parent::_getAssetParentId($table, $id);
	}
}
