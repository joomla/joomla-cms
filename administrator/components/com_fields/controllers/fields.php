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
 * Fields list controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class FieldsControllerFields extends JControllerAdmin
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
				'&context=' . $this->input->getCmd('context', 'com_content.article'), false
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
		$return = parent::delete();

		$this->setRedirect(
			JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list .
				'&context=' . $this->input->getCmd('context', 'com_content.article'), false
			)
		);

		return $return;
	}

	/**
	 * Method to publish a list of items
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function publish()
	{
		$return = parent::publish();

		$this->setRedirect(
			JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list .
					'&context=' . $this->input->getCmd('context', 'com_content.article'), false
			)
		);

		return $return;
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModel
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Field', $prefix = 'FieldsModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
