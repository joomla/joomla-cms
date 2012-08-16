<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a popup window button
 *
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 * @since       3.0
 */
class JToolbarButtonPopup extends JToolbarButton
{
	/**
	 * Button type
	 *
	 * @var    string
	 */
	protected $_name = 'Popup';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string   $type     Unused string, formerly button type.
	 * @param   string   $name     Button name
	 * @param   string   $text     The link text
	 * @param   string   $url      URL for popup
	 * @param   integer  $width    Width of popup
	 * @param   integer  $height   Height of popup
	 * @param   integer  $top      Top attribute.
	 * @param   integer  $left     Left attribute
	 * @param   string   $onClose  JavaScript for the onClose event.
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Popup', $name = '', $text = '', $url = '', $width = 640, $height = 480, $top = 0, $left = 0, $onClose = '')
	{
		JHtml::script('jui/cms.js', false, true);

		$text = JText::_($text);
		$class = 'cog';
		$doTask = $this->_getCommand($name, $url, $width, $height, $top, $left);

		$html = "<button class=\"btn\" data-toggle=\"collapse\" data-target=\"#modal-" . $name . "\" rel=\"{onClose: function() {" . $onClose
			. "}}\" onClick=\"setcollapse('$url', '$name', '$height');\">\n";
		$html .= "<i class=\"icon-$class\">\n";
		$html .= "</i>\n";
		$html .= "$text\n";

		$html .= "</button>\n";

		return $html;
	}

	/**
	 * Get the button id
	 *
	 * Redefined from JButton class
	 *
	 * @param   string  $type  Button type
	 * @param   string  $name  Button name
	 *
	 * @return  string	Button CSS Id
	 *
	 * @since   3.0
	 */
	public function fetchId($type, $name)
	{
		return $this->_parent->getName() . '-' . "popup-$name";
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @param   string   $name    Button name
	 * @param   string   $url     URL for popup
	 * @param   integer  $width   Unused formerly width.
	 * @param   integer  $height  Unused formerly height.
	 * @param   integer  $top     Unused formerly top attribute.
	 * @param   integer  $left    Unused formerly left attribure.
	 *
	 * @return  string   JavaScript command string
	 *
	 * @since   3.0
	 */
	protected function _getCommand($name, $url, $width, $height, $top, $left)
	{
		if (substr($url, 0, 4) !== 'http')
		{
			$url = JURI::base() . $url;
		}

		return $url;
	}
}
