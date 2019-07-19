<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * The Categories List Controller
 *
 * @since  1.6
 */
class CategoriesControllerCategories extends JControllerAdmin
{
	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModelLegacy  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Category', $prefix = 'CategoriesModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  boolean  False on failure or error, true on success.
	 *
	 * @since   1.6
	 */
	public function rebuild()
	{
		$this->checkToken();

		$extension = $this->input->get('extension');
		$this->setRedirect(JRoute::_('index.php?option=com_categories&view=categories&extension=' . $extension, false));

		/** @var CategoriesModelCategory $model */
		$model = $this->getModel();

		if ($model->rebuild())
		{
			// Rebuild succeeded.
			$this->setMessage(JText::_('COM_CATEGORIES_REBUILD_SUCCESS'));

			return true;
		}

		// Rebuild failed.
		$this->setMessage(JText::_('COM_CATEGORIES_REBUILD_FAILURE'));

		return false;
	}

	/**
	 * Save the manual order inputs from the categories list page.
	 *
	 * @return      boolean  True on success
	 *
	 * @since       1.6
	 * @see         JControllerAdmin::saveorder()
	 * @deprecated  4.0
	 */
	public function saveorder()
	{
		$this->checkToken();

		try
		{
			JLog::add(sprintf('%s() is deprecated. Function will be removed in 4.0.', __METHOD__), JLog::WARNING, 'deprecated');
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		// Get the arrays from the Request
		$order = $this->input->post->get('order', null, 'array');
		$originalOrder = explode(',', $this->input->getString('original_order_values'));

		// Make sure something has changed
		if (!($order === $originalOrder))
		{
			parent::saveorder();
		}
		else
		{
			// Nothing to reorder
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list, false));

			return true;
		}
	}

	/**
	 * Deletes and returns correctly.
	 *
	 * @return  void
	 *
	 * @since   3.1.2
	 */
	public function delete()
	{
		$this->checkToken();

		// Get items to remove from the request.
		$cid = $this->input->get('cid', array(), 'array');
		$extension = $this->input->getCmd('extension', null);

		if (!is_array($cid) || count($cid) < 1)
		{
			JError::raiseWarning(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
		}
		else
		{
			// Get the model.
			/** @var CategoriesModelCategory $model */
			$model = $this->getModel();

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->delete($cid))
			{
				$this->setMessage(JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}

		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&extension=' . $extension, false));
	}

	/**
	 * Check in of one or more records.
	 *
	 * Overrides JControllerAdmin::checkin to redirect to URL with extension.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.6.0
	 */
	public function checkin()
	{
		// Process parent checkin method.
		$result = parent::checkin();

		// Override the redirect Uri.
		$redirectUri = 'index.php?option=' . $this->option . '&view=' . $this->view_list . '&extension=' . $this->input->get('extension', '', 'CMD');
		$this->setRedirect(JRoute::_($redirectUri, false), $this->message, $this->messageType);

		return $result;
	}
}
