<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\ToolbarButton;

/**
 * Renders a modal window button
 *
 * @since  3.0
 */
class PopupButton extends ToolbarButton
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
	 * @param   string   $footer   The footer html
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Modal', $name = '', $text = '', $url = '', $width = 640, $height = 480, $top = 0, $left = 0,
		$onClose = '', $title = '', $footer = null)
	{
		// If no $title is set, use the $text element
		if ($title === '')
		{
			$title = $text;
		}

		// Store all data to the options array for use with JLayout
		$options = array();
		$options['name'] = $name;
		$options['text'] = \JText::_($text);
		$options['title'] = \JText::_($title);
		$options['class'] = $this->fetchIconClass($name);
		$options['doTask'] = $this->_getCommand($url);

		// Instantiate a new JLayoutFile instance and render the layout
		$layout = new FileLayout('joomla.toolbar.popup');

		$html = array();
		$html[] = $layout->render($options);

		// Place modal div and scripts in a new div
		$html[] = '<div class="btn-group" style="width: 0; margin: 0">';

		// Build the options array for the modal
		$params = array();
		$params['title']  = $options['title'];
		$params['url']    = $options['doTask'];
		$params['height'] = $height;
		$params['width']  = $width;

		if (isset($footer))
		{
			$params['footer'] = $footer;
		}

		$html[] = \JHtml::_('bootstrap.renderModal', 'modal-' . $name, $params);

		// If an $onClose event is passed, add it to the modal JS object
		if ($onClose !== '')
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
		if (strpos($url, 'http') !== 0)
		{
			$url = \JUri::base() . $url;
		}

		return $url;
	}
}
