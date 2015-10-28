<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Cron job to trash expired cache data.
 *
 * @since  3.5
 */
class CliCommandGarbagecron extends JControllerBase
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	public function execute()
	{
		JFactory::getCache()->gc();

		return true;
	}
}
