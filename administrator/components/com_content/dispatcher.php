<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Cms\Dispatcher\Dispatcher;

/**
 * Dispatcher class for com_content
 *
 * @since  __DEPLOY_VERSION__
 */
class ContentDispatcher extends Dispatcher
{
	/**
	 * Returns the namespace of the extension this dispatcher belongs to. If
	 * the returned string is empty, then a none namespaced extension is assumed.
	 *
	 * @return  string|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getNamespace()
	{
		return 'Joomla\\Component\\Content\\Admin';
	}
}