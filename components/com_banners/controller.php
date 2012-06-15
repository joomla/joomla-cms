<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_banners
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Banners Controller
 *
 * @package		Joomla.Site
 * @subpackage	com_banners
 * @since		1.5
 */
class BannersController extends JController
{
	function click()
	{
		$id = JRequest::getInt('id', 0);

		if ($id) {
			$model = $this->getModel('Banner', 'BannersModel', array('ignore_request'=>true));
			$model->setState('banner.id', $id);
			$model->click();
			$this->setRedirect($model->getUrl());
		}
	}
}
