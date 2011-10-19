<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Finder
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

/**
 * System plugin class for Finder.
 *
 * @package     Joomla.Plugin
 * @subpackage  System.Finder
 * @since       2.5
 */
class PlgSystemFinder extends JPlugin
{
	/**
	 * Event to trigger the Finder update process
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function onAfterRender()
	{
		// Pause if the main menu is disabled.
		if (JFactory::getApplication()->input->get('hidemainmenu', '', 'bool'))
		{
			return;
		}

		$text = JText::_('MOD_FINDER_STATUS_WAITING');
		JHtml::_('behavior.framework');
		JHtml::script('mod_finder_status/status.js', false, true);

		// We need to add some CSS to fix the status bar display.
		$doc = &JFactory::getDocument();
		$doc->addStyleDeclaration(
			'div#module-status { background: none; }' .
			'#finder-status-message {' .
			'	background: transparent url(../media/com_finder/images/icon-16-jx.png) no-repeat scroll 2px 4px' .
			'}'
		);
		?>
		<span id="finder-status-message"><?php echo $text; ?></span><?php
	}
}
