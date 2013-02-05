<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * This view is displayed after successfull saving of config data.
 * Use it to show a message informing about success or simply close a modal window.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_config
 */
class ConfigViewClose extends JViewLegacy
{
	/**
	 * Display the view
	 */
	function display($tpl = null)
	{
		// close a modal window
		JFactory::getDocument()->addScriptDeclaration('
			window.parent.location.href=window.parent.location.href;
			window.parent.SqueezeBox.close();
		');
	}
}
