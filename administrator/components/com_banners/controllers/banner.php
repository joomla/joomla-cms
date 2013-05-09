<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Banner controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 * @since       1.6
 */
class BannersControllerBanner extends JControllerForm
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_BANNERS_BANNER';

	/**
	 * Method to run batch operations.
	 *
	 * @param   string  $model  The model
	 *
	 * @return  boolean  True on success.
	 *
	 * @since	2.5
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model	= $this->getModel('Banner', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_banners&view=banners' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

}
