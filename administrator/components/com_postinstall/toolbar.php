<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Toolbar class renders the component title area and the toolbar.
 *
 * @since  3.2
 */
class PostinstallToolbar extends FOFToolbar
{
	/**
	 * Setup the toolbar and title
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function onMessages()
	{
		JToolbarHelper::preferences($this->config['option'], 550, 875);
		JToolbarHelper::help('JHELP_COMPONENTS_POST_INSTALLATION_MESSAGES');
	}
}
