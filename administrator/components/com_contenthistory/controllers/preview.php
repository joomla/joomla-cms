<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contenthistory list controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 * @since       3.2
 */
class ContenthistoryControllerPreview extends JControllerLegacy
{
	/**
	 * Proxy for getModel.
	 * @since   1.6
	 */
	public function getModel($name = 'Preview', $prefix = 'ContenthistoryModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	/**
	 * Method to provide child classes the opportunity to process after the delete task.
	 *
	 * @param   JModelLegacy   $model   The model for the component
	 * @param   mixed          $ids     array of ids deleted.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function postDeleteHook(JModelLegacy $model, $ids = null)
	{
	}

}
