<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Groups list controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class FieldsControllerGroups extends JControllerAdmin
{
	/**
	 * Check in of one or more records.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function checkin()
	{
		$return = parent::checkin();

		$this->setRedirect(
			JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list .
				'&extension=' . $this->input->getCmd('extension'), false
			)
		);

		return $return;
	}

	/**
	 * Removes an item
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function delete()
	{
		parent::delete();

		$this->setRedirect(
			JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list .
				'&extension=' . $this->input->getCmd('extension'), false
			)
		);

		return;
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModelLegacy|boolean  Model object on success; otherwise false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Group', $prefix = 'FieldsModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
