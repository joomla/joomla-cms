<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerEdit extends JControllerAdministrate
{
	public function execute()
	{
		/** @var JModelAdministrator $model */
		$model = $this->getModel($this->config['resource']);

		$ids    = $this->getIds();
		$editId = $ids[0];

		if (!$model->allowAction('core.edit') && $editId != 0)
		{
			$msg = JText::_('JLIB_APPLICATION_ERROR_EDIT_ITEM_NOT_PERMITTED');
			throw new ErrorException($msg);
		}

		$config = $this->config;
		$url    = 'index.php?option=' . $config['option'] . '&view=' . $config['view'] . '&layout=form';

		$context = $model->getContext();
		$this->setUserState($context . '.edit.id', $editId);

		if ($editId != 0)
		{
			$model->checkout($editId);
			$item = $model->getItem($editId);

			$context = $model->getContext();
			$this->setUserState($context . '.jform.data', $item->getProperties());
			$url .= '&id=' . $editId;
		}

		$this->setReturn($url);

		return $this->executeController();
	}
}