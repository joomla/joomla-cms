<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Banners Controller
 *
 * @since  1.5
 */
class BannersController extends JControllerLegacy
{
	/**
	 * Method when a banner is clicked on.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function click()
	{
		$id = $this->input->getInt('id', 0);

		if ($id)
		{
			$model = $this->getModel('Banner', 'BannersModel', array('ignore_request' => true));
			$model->setState('banner.id', $id);
			$model->click();
			$this->setRedirect($model->getUrl());
		}
	}
}
