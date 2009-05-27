<?php
/**
 * @version		$Id: controller.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package  	Joomla
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Banners Controller
 *
 * @package  	Joomla
 * @subpackage	Banners
 * @since		1.5
 */
class BannersController extends JController
{
	function click()
	{
		$bid = JRequest::getInt('bid', 0);
		if ($bid)
		{
			$model = &$this->getModel('Banner');
			$model->click($bid);
			$this->setRedirect($model->getUrl($bid));
		}
	}
}