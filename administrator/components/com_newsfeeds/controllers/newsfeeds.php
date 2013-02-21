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
class NewsfeedsControllerNewsfeeds extends JControllerAdmincontent
{
	/*
	 * @var  string Model name
	 */
	protected $name = 'Newsfeed';
	/*
	 * @var  string   Model prefix
	 */
	protected  $prefix = 'NewsfeedsModel';

	/**
	 * The URL option for the component.
	 *
	 * @var    string
	 * @since  12.2
	 */
	protected $option = 'com_newsfeeds';
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the PHP class name.
	 * @param   string  $config  Array of configuration options
	 *
	 * @return  JModel
	 * @since   1.6
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
