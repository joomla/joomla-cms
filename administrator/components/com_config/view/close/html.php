<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This view is displayed after successfull saving of config data.
 * Use it to show a message informing about success or simply close a modal window.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.2
 */
class ConfigViewCloseHtml extends JViewCms
{
	/**
	 * Display the view
	 * 
	 * @param   string  $tpl  Layout
	 * 
	 * @return  void
	 * 
	 * 
	 */
	public function render()
	{
		// Close a modal window
		JFactory::getDocument()->addScriptDeclaration('
			window.parent.location.href=window.parent.location.href;
			window.parent.SqueezeBox.close();
		');
	}
}
