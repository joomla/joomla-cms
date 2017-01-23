<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Toolbar
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a modal window button
 *
 * @since  3.0
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
	 * @param   string   $type          Unused string, formerly button type.
	 * @param   string   $name          Modal name, used to generate element ID
	 * @param   string   $text          The link text
	 * @param   string   $url           URL for popup
	 * @param   integer  $iframeWidth   Width of popup
	 * @param   integer  $iframeHeight  Height of popup
	 * @param   integer  $bodyHeight    Optional height of the modal body in viewport units (vh)
	 * @param   integer  $modalWidth    Optional width of the modal in viewport units (vh)
	 * @param   string   $onClose       JavaScript for the onClose event.
	 * @param   string   $title         The title text
	 * @param   string   $footer        The footer html
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Modal', $name = '', $text = '', $url = '', $iframeWidth = 640,
	    $iframeHeight = 480, $bodyHeight = null, $modalWidth = null, $onClose = '', $title = '', $footer = null)
	{
		// If no $title is set, use the $text element
		if (strlen($title) == 0)
		{
			$title = $text;
		}

		// Store all data to the options array for use with JLayout
		$options = array();
		$options['name']   = $name;
		$options['text']   = JText::_($text);
		$options['title']  = JText::_($title);
		$options['class']  = $this->fetchIconClass($name);
		$options['doTask'] = $this->_getCommand($url);

		// Instantiate a new JLayoutFile instance and render the layout
		$layout = new JLayoutFile('joomla.toolbar.popup');

		$html = array();
		$html[] = $layout->render($options);

		// Place modal div and scripts in a new div
		$html[] = '<div class="btn-group" style="width: 0; margin: 0">';

		// Build the options array for the modal
		$params = array();
		$params['title']      = $options['title'];
		$params['url']        = $options['doTask'];
		$params['height']     = $iframeHeight;
		$params['width']      = $iframeWidth;
		$params['bodyHeight'] = $bodyHeight;
		$params['modalWidth'] = $modalWidth;

		if (isset($footer))
		{
			$params['footer'] = $footer;
		}

		$html[] = JHtml::_('bootstrap.renderModal', 'modal-' . $name, $params);

		// If an $onClose event is passed, add it to the modal JS object
		if (strlen($onClose) >= 1)
		{
			$html[] = '<script>'
				. 'jQuery(\'#modal-' . $name . '\').on(\'hide\', function () {' . $onClose . ';});'
				. '</script>';
		}

		$html[] = '</div>';

		return implode("\n", $html);
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
		return $this->_parent->getName() . '-popup-' . $name;
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
			$url = JUri::base() . $url;
		}

		return $url;
	}
}
