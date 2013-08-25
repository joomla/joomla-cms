<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Newsfeeds list controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 * @since       1.6
 */
class NewsfeedsControllerNewsfeeds extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 */
	public function getModel($name = 'Newsfeed', $prefix = 'NewsfeedsModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	protected function postDeleteHook(JModelLegacy $model, $ids = null)
	{
	}
}
