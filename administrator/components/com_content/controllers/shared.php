<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Shared drafts list controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentControllerShared extends JControllerAdmin
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var     string  The prefix to use with controller messages.
	 * @since   __DEPLOY_VERSION__
	 */
	protected $text_prefix = 'COM_CONTENT_SHARE';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  The array of possible config values. Optional.
	 *
	 * @return  JModel
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Share', $prefix = 'ContentModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
