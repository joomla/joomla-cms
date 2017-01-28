<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Cms\Dispatcher\Dispatcher;

class BannersDispatcher extends Dispatcher
{
	public function dispatch()
	{
		JHtml::_('behavior.tabstate');

		parent::dispatch();
	}
}