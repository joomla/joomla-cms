<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JControllerStateBase extends JControllerCms
{
	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		$input = $this->input;

		$model = $this->getModel();

		if (!$model->allowAction('core.edit.state'))
		{
			throw new ErrorException($this->translate('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
		}

		$cid = $input->get('cid', array(), 'array');
		// Make sure the item ids are integers
		$cid = $this->cleanCid($cid);

		$this->updateRecordState($model, $cid);

		return true;
	}

	/**
	 * Method to update one or more record states
	 *
	 * @param JModelAdministrator $model
	 * @param array               $cid
	 */
	abstract protected function updateRecordState($model, $cid);


}