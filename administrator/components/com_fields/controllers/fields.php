<?php
/**
 * @package    Fields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
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
