<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\Component\Privacy\Administrator\Model\ConsentsModel;

/**
 * Consents management controller class.
 *
 * @since  3.9.0
 */
class ConsentsController extends FormController
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
		$this->checkToken();

		$ids = $this->input->get('cid', [], 'array');

		if (empty($ids))
		{
			$this->setError(Text::_('JERROR_NO_ITEMS_SELECTED'));
		}
		else
		{
			// Get the model.
			/** @var ConsentsModel $model */
			$model = $this->getModel();

			// Publish the items.
			if (!$model->invalidate($ids))
			{
				$this->setError($model->getError());
			}

			$message = Text::plural('COM_PRIVACY_N_CONSENTS_INVALIDATED', count($ids));
		}

		$this->setRedirect(Route::_('index.php?option=com_privacy&view=consents', false), $message);
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
		$this->checkToken();

		$filters = $this->input->get('filter', [], 'array');

		if (isset($filters['subject']) && $filters['subject'] != '')
		{
			$subject = $filters['subject'];
		}
		else
		{
			$this->setError(Text::_('JERROR_NO_ITEMS_SELECTED'));
		}

		// Get the model.
		/** @var ConsentsModel $model */
		$model = $this->getModel();

		// Publish the items.
		if (!$model->invalidateAll($subject))
		{
			$this->setError($model->getError());
		}

		$message = Text::_('COM_PRIVACY_CONSENTS_INVALIDATED_ALL');

		$this->setRedirect(Route::_('index.php?option=com_privacy&view=consents', false), $message);
	}
}
