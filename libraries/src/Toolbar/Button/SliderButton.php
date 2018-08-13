<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\ToolbarButton;
use Joomla\CMS\Uri\Uri;

/**
 * Renders a button to render an HTML element in a slider container
 *
 * @since  3.0
 */
class SliderButton extends ToolbarButton
{
	/**
	 * Button type
	 *
	 * @var    string
	 */
	protected $_name = 'Slider';

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string   $type     Unused string, formerly button type.
	 * @param   string   $name     Button name
	 * @param   string   $text     The link text
	 * @param   string   $url      URL for popup
	 * @param   integer  $width    Width of popup
	 * @param   integer  $height   Height of popup
	 * @param   string   $onClose  JavaScript for the onClose event.
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Slider', $name = '', $text = '', $url = '', $width = 640, $height = 480, $onClose = '')
	{
		// @todo split the irrelevant code, this button doesn't need the showon...
		HTMLHelper::_('script', 'system/showon.min.js', array('version' => 'auto', 'relative' => true));

		// Store all data to the options array for use with Layout
		$options = array();
		$options['text']    = Text::_($text);
		$options['name']    = $name;
		$options['class']   = $this->fetchIconClass($name);
		$options['onClose'] = '';
		$options['id']      = $this->fetchId('Slider', $name);

		if ($options['id'])
		{
			$options['id'] = ' id="' . $options['id'] . '"';
		}

		$doTask = $this->_getCommand($url);
		$options['doTask'] = 'Joomla.setcollapse(\'' . $doTask . '\', \'' . $name . '\', \'' . $height . '\');';

		if ($onClose)
		{
			$options['onClose'] = ' rel="{onClose: function() {' . $onClose . '}}"';
		}

		// Instantiate a new LayoutFile instance and render the layout
		$layout = new FileLayout('joomla.toolbar.slider');

		return $layout->render($options);
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
		return $this->parent->getName() . '-slider-' . $name;
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
			$url = Uri::base() . $url;
		}

		return $url;
	}
}
