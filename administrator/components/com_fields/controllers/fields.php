<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 * 
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class FieldsControllerFields extends JControllerAdmin
{

	public function delete ()
	{
		$return = parent::delete();

		$this->setRedirect(
				JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_list . '&context=' .
								 $this->input->getCmd('context', 'com_content.article'), false));

		return $return;
	}

	public function publish ()
	{
		$return = parent::publish();

		$this->setRedirect(
				JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_list . '&context=' .
								 $this->input->getCmd('context', 'com_content.article'), false));

		return $return;
	}

	public function getModel ($name = 'Field', $prefix = 'FieldsModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
}
