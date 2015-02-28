<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class  JControllerStateChange extends JControllerState
{
	public function __construct(JInput $input, $app = null, $config = array())
	{
		$newState = $input->get('newState', 'CMD');
		if (empty($newState))
		{
			$newState = 'archived';
		}

		$config['newState'] = $newState;
		parent::__construct($input, $app, $config);
	}

	/**
	 * Method to update one or more record states
	 *
	 * @param JModelAdministrator $model
	 * @param array               $cid
	 */
	protected function updateRecordState($model, $cid)
	{
		$model->updateRecordState($cid, $this->config['newState']);
		$this->addMessage(JText::_('BABELU_LIB_CONTROLLER_MESSAGE_ITEMS_' . strtoupper($this->config['newState'])));
	}
}