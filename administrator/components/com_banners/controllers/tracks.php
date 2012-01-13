<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Tracks list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_banners
 * @since		1.6
 */
class BannersControllerTracks extends JController
{
	/**
	 * @var		string	The context for persistent state.
	 * @since	1.6
	 */
	protected $context = 'com_banners.tracks';

	/**
	 * Proxy for getModel.
	 *
	 * @param	string	$name	The name of the model.
	 * @param	string	$prefix	The prefix for the model class name.
	 *
	 * @return	JModel
	 * @since	1.6
	 */
	public function &getModel($name = 'Tracks', $prefix = 'BannersModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Method to remove a record.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function delete()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the model.
		$model = $this->getModel();

		// Load the filter state.
		$app = JFactory::getApplication();

		$type = $app->getUserState($this->context.'.filter.type');
		$model->setState('filter.type', $type);

		$begin = $app->getUserState($this->context.'.filter.begin');
		$model->setState('filter.begin', $begin);

		$end = $app->getUserState($this->context.'.filter.end');
		$model->setState('filter.end', $end);

		$categoryId = $app->getUserState($this->context.'.filter.category_id');
		$model->setState('filter.category_id', $categoryId);

		$clientId = $app->getUserState($this->context.'.filter.client_id');
		$model->setState('filter.client_id', $clientId);

		$model->setState('list.limit', 0);
		$model->setState('list.start', 0);

		$count = $model->getTotal();
		// Remove the items.
		if (!$model->delete()) {
			JError::raiseWarning(500, $model->getError());
		} else {
			$this->setMessage(JText::plural('COM_BANNERS_TRACKS_N_ITEMS_DELETED', $count));
		}

		$this->setRedirect('index.php?option=com_banners&view=tracks');
	}
}
