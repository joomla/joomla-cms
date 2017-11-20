<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Categories\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Utilities\ArrayHelper;

/**
 * The Categories List Controller
 *
 * @since  1.6
 */
class CategoriesController extends AdminController
{
	/**
	 * Proxy for getModel
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
	 *
	 * @since   1.6
	 */
	public function getModel($name = 'Category', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}

	/**
	 * Rebuild the nested set tree.
	 *
	 * @return  bool  False on failure or error, true on success.
	 *
	 * @since   1.6
	 */
	public function rebuild()
	{
		\JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		$extension = $this->input->get('extension');
		$this->setRedirect(\JRoute::_('index.php?option=com_categories&view=categories&extension=' . $extension, false));

		/** @var \Joomla\Component\Categories\Administrator\Model\CategoryModel $model */
		$model = $this->getModel();

		if ($model->rebuild())
		{
			// Rebuild succeeded.
			$this->setMessage(\JText::_('COM_CATEGORIES_REBUILD_SUCCESS'));

			return true;
		}

		// Rebuild failed.
		$this->setMessage(\JText::_('COM_CATEGORIES_REBUILD_FAILURE'));

		return false;
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
		\JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		// Get items to remove from the request.
		$cid = $this->input->get('cid', array(), 'array');
		$extension = $this->input->getCmd('extension', null);

		if (!is_array($cid) || count($cid) < 1)
		{
			$this->app->enqueueMessage(\JText::_($this->text_prefix . '_NO_ITEM_SELECTED'), 'warning');
		}
		else
		{
			// Get the model.
			/** @var \Joomla\Component\Categories\Administrator\Model\CategoryModel $model */
			$model = $this->getModel();

			// Make sure the item ids are integers
			$cid = ArrayHelper::toInteger($cid);

			// Remove the items.
			if ($model->delete($cid))
			{
				$this->setMessage(\JText::plural($this->text_prefix . '_N_ITEMS_DELETED', count($cid)));
			}
			else
			{
				$this->setMessage($model->getError());
			}
		}

		$this->setRedirect(\JRoute::_('index.php?option=' . $this->option . '&extension=' . $extension, false));
	}

	/**
	 * Check in of one or more records.
	 *
	 * Overrides \JControllerAdmin::checkin to redirect to URL with extension.
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
		$this->setRedirect(\JRoute::_($redirectUri, false), $this->message, $this->messageType);

		return $result;
	}
}
