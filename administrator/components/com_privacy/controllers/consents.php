<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Consents management controller class.
 *
 * @since  3.9.0
 */
class PrivacyControllerConsents extends JControllerForm
{
	/**
	 * Method to invalidate specific consents.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function invalidate($key = null, $urlVar = null)
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$ids    = $this->input->get('cid', array(), 'array');

		if (empty($ids))
		{
			$this->setError(JText::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else
		{
			// Get the model.
			/** @var PrivacyModelConsents $model */
			$model = $this->getModel();

			// Publish the items.
			if (!$model->invalidate($ids))
			{
				$this->setError($model->getError());
			}

			$message = JText::plural('COM_PRIVACY_N_CONSENTS_INVALIDATED', count($ids));
		}

		$this->setRedirect(JRoute::_('index.php?option=com_privacy&view=consents', false), $message);
	}

	/**
	 * Method to invalidate all consents of a specific subject.
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function invalidateAll()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$filters = $this->input->get('filter', array(), 'array');

		if (isset($filters['subject']) && $filters['subject'] != '')
		{
			$subject = $filters['subject'];
		}
		else
		{
			$this->setError(JText::_('JERROR_NO_ITEMS_SELECTED'));
		}

		// Get the model.
		/** @var PrivacyModelConsents $model */
		$model = $this->getModel();

		// Publish the items.
		if (!$model->invalidateAll($subject))
		{
			$this->setError($model->getError());
		}

		$message = JText::_('COM_PRIVACY_CONSENTS_INVALIDATED_ALL');

		$this->setRedirect(JRoute::_('index.php?option=com_privacy&view=consents', false), $message);
	}
}
