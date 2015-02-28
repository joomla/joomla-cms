<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerDelete extends JControllerAdministrate
{
	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		/** @var JModelAdministrator $model */
		$model = $this->getModel($this->config['resource']);

		If (!$model->allowAction('core.delete'))
		{
			$msg = JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED');
			throw new ErrorException($msg);
		}

		$context = $model->getContext();
		$editId  = $this->getUserState($context . '.edit.id', 0);

		if ($editId != 0)
		{
			$model->checkin($editId);
		}

		$this->setUserState($context . '.edit.id', 0);
		$this->setUserState($context . '.jform.data', null);

		$cid = $this->getIds();

		if (count($cid) < 1)
		{
			$msg = JText::_('JWARNING_DELETE_MUST_SELECT');
			throw new ErrorException($msg);
		}

		$model->delete($cid);

		//I couldn't find the language string for this.
		$this->addMessage(JText::_('Item(s) deleted successfully'));

		return $this->executeController();
	}
}