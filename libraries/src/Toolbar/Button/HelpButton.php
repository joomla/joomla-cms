<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Help\Help;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\ToolbarButton;

/**
 * Renders a help popup window button
 *
 * @since  3.0
 */
class HelpButton extends ToolbarButton
{
	/**
	 * @var    string	Button type
	 */
	protected $_name = 'Help';

	/**
	 * Fetches the button HTML code.
	 *
	 * @param   string   $type       Unused string.
	 * @param   string   $ref        The name of the help screen (its key reference).
	 * @param   boolean  $com        Use the help file in the component directory.
	 * @param   string   $override   Use this URL instead of any other.
	 * @param   string   $component  Name of component to get Help (null for current component)
	 *
	 * @return  string
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Help', $ref = '', $com = false, $override = null, $component = null)
	{
		// Store all data to the options array for use with JLayout
		$options = array();
		$options['text']   = \JText::_('JTOOLBAR_HELP');
		$options['doTask'] = $this->_getCommand($ref, $com, $override, $component);

		// Instantiate a new JLayoutFile instance and render the layout
		$layout = new FileLayout('joomla.toolbar.help');

		return $layout->render($options);
	}

	/**
	 * Get the button id
	 *
	 * Redefined from JButton class
	 *
	 * @return  string	Button CSS Id
	 *
	 * @since   3.0
	 */
	public function fetchId()
	{
		return $this->_parent->getName() . '-help';
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param   string   $ref        The name of the help screen (its key reference).
	 * @param   boolean  $com        Use the help file in the component directory.
	 * @param   string   $override   Use this URL instead of any other.
	 * @param   string   $component  Name of component to get Help (null for current component)
	 *
	 * @return  string   JavaScript command string
	 *
	 * @since   3.0
	 */
	protected function _getCommand($ref, $com, $override, $component)
	{
		// Get Help URL
		$url = Help::createUrl($ref, $com, $override, $component);
		$url = json_encode(htmlspecialchars($url, ENT_QUOTES), JSON_HEX_APOS);
		$url = substr($url, 1, -1);
		$cmd = "Joomla.popupWindow('$url', '" . \JText::_('JHELP', true) . "', 700, 500, 1)";

		return $cmd;
	}
}
