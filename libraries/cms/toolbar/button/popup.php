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
 * Renders a modal window button
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
	 * @param   string   $name     Modal name, used to generate element ID
	 * @param   string   $text     The link text
	 * @param   string   $url      URL for popup
	 * @param   integer  $width    Width of popup
	 * @param   integer  $height   Height of popup
	 * @param   integer  $top      Top attribute.  [@deprecated  Unused, will be removed in 4.0]
	 * @param   integer  $left     Left attribute. [@deprecated  Unused, will be removed in 4.0]
	 * @param   string   $onClose  JavaScript for the onClose event.
	 * @param   string   $title    The title text
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Modal', $name = '', $text = '', $url = '', $width = 640, $height = 480, $top = 0, $left = 0,
		$onClose = '', $title = '')
	{
		// If no $title is set, use the $text element
		if (strlen($title) == 0)
		{
			$title = $text;
		}

		$text = JText::_($text);
		$title = JText::_($title);
		$class = 'out-2';
		$doTask = $this->_getCommand($url);

		$html = "<button class=\"btn btn-small modal\" data-toggle=\"modal\" data-target=\"#modal-" . $name . "\">\n";
		$html .= "<i class=\"icon-" . $class . "\">\n";
		$html .= "</i>\n";
		$html .= "$text\n";

		$html .= "</button>\n";

		// Build the options array for the modal
		$params = array();
		$params['title']  = $title;
		$params['url']    = $doTask;
		$params['height'] = $height;
		$params['width']  = $width;
		$html .= JHtml::_('bootstrap.renderModal', 'modal-' . $name, $params);

		// If an $onClose event is passed, add it to the modal JS object
		if (strlen($onClose) >= 1)
		{
			$html .= "<script>\n";
			$html .= "jQuery('#modal-" . $name . "').on('hide', function () {\n";
			$html .= $onClose . ";\n";
			$html .= "}";
			$html .= ");";
			$html .= "</script>\n";
		}

		return $html;
	}

	/**
	 * Get the button id
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
	 * @param   string  $url  URL for popup
	 *
	 * @return  string  JavaScript command string
	 *
	 * @since   3.0
	 */
	private function _getCommand($url)
	{
		if (substr($url, 0, 4) !== 'http')
		{
			$url = JURI::base() . $url;
		}

		return $url;
	}
}
