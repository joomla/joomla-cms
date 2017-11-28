<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\Dispatcher;

/**
 * Dispatcher class for com_config
 *
 * @since  4.0.0
 */
class ConfigDispatcher extends Dispatcher
{
	/**
	 * The extension namespace
	 *
	 * @var    string
	 *
	 * @since  4.0.0
	 */
	protected $namespace = 'Joomla\\Component\\Config';

	/**
	 * Method to check component access permission
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 *
	 * @throws  Exception|Notallowed
	 */
	protected function checkAccess()
	{
		parent::checkAccess();

		if (!$this->app->getIdentity()->authorise('core.admin'))
		{
			throw new Notallowed($this->app->getLanguage()->_('JERROR_ALERTNOAUTHOR'), 403);
		}
	}
}
