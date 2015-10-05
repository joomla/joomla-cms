<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tracks list controller class.
 *
 * @since  1.6
 */
class BannersControllerTracks extends JControllerLegacy
{
	/**
	 * @var     string  The prefix to use with controller messages.
	 *
	 * @since   1.6
	 */
	protected $context = 'com_banners.tracks';

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Tracks', $prefix = 'BannersModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	/**
	 * Method to remove a record.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function delete()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the model.
		$model = $this->getModel();

		// Load the filter state.
		$app = JFactory::getApplication();

		$type = $app->getUserState($this->context . '.filter.type');
		$model->setState('filter.type', $type);

		$begin = $app->getUserState($this->context . '.filter.begin');
		$model->setState('filter.begin', $begin);

		$end = $app->getUserState($this->context . '.filter.end');
		$model->setState('filter.end', $end);

		$categoryId = $app->getUserState($this->context . '.filter.category_id');
		$model->setState('filter.category_id', $categoryId);

		$clientId = $app->getUserState($this->context . '.filter.client_id');
		$model->setState('filter.client_id', $clientId);

		$model->setState('list.limit', 0);
		$model->setState('list.start', 0);

		$count = $model->getTotal();

		// Remove the items.
		if (!$model->delete())
		{
			JError::raiseWarning(500, $model->getError());
		}
		elseif (count > 0)
		{
			$this->setMessage(JText::plural('COM_BANNERS_TRACKS_N_ITEMS_DELETED', $count));
		}
		else
		{
			$this->setMessage(JText::_('COM_BANNERS_TRACKS_NO_ITEMS_DELETED'));
		}

		$this->setRedirect('index.php?option=com_banners&view=tracks');
	}
}
