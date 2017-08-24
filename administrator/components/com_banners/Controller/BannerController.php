<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Banners\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Controller\Form;
use Joomla\Utilities\ArrayHelper;

/**
 * Banner controller class.
 *
 * @since  1.6
 */
class BannerController extends Form
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_BANNERS_BANNER';

	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowAdd($data = array())
	{
		$filter     = $this->input->getInt('filter_category_id');
		$categoryId = ArrayHelper::getValue($data, 'catid', $filter, 'int');
		$allow      = null;

		if ($categoryId)
		{
			// If the category has been passed in the URL check it.
			$allow = $this->app->getIdentity()->authorise('core.create', $this->option . '.category.' . $categoryId);
		}

		if ($allow !== null)
		{
			return $allow;
		}

		// In the absence of better information, revert to the component permissions.
		return parent::allowAdd($data);
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId   = (int) isset($data[$key]) ? $data[$key] : 0;
		$categoryId = 0;

		if ($recordId)
		{
			$categoryId = (int) $this->getModel()->getItem($recordId)->catid;
		}

		if ($categoryId)
		{
			// The category has been set. Check the category permissions.
			return $this->app->getIdentity()->authorise('core.edit', $this->option . '.category.' . $categoryId);
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   string  $model  The model
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function batch($model = null)
	{
		\JSession::checkToken() or jexit(\JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Banner', '', array());

		// Preset the redirect
		$this->setRedirect(\JRoute::_('index.php?option=com_banners&view=banners' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}
}
