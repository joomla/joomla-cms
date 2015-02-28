<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JControllerState extends JControllerAdministrate
{
	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		/** @var JModelAdministrator $model */
		$model = $this->getModel($this->config['resource']);

		if (!$model->allowAction('core.edit.state'))
		{
			$msg = JText::_('BABELU_LIB_ACL_ERROR_EDIT_STATE_NOT_PERMITTED');
			throw new ErrorException($msg);
		}

		// Make sure the item ids are integers
		$cid = $this->getIds();

		$this->updateRecordState($model, $cid);

		return $this->executeController();
	}

	/**
	 * Method to update one or more record states
	 *
	 * @param JModelAdministrator $model
	 * @param array               $cid
	 */
	abstract protected function updateRecordState($model, $cid);


}