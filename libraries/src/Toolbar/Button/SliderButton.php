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
use Joomla\CMS\Toolbar\ToolbarButton;
use Joomla\CMS\Uri\Uri;

/**
 * Renders a button to render an HTML element in a slider container
 *
 * @method self url(string $value)
 * @method self height(integer $value)
 * @method self width(integer $value)
 * @method self onClose(string $value)
 * @method integer getHeight()
 * @method integer getWidth
 * @method string getUrl
 * @method string getOnClose
 *
 * @since  3.0
 */
class SliderButton extends ToolbarButton
{
	/**
	 * Property layout.
	 *
	 * @var  string
	 *
	 * @since  4.0.0
	 */
	protected $layout = 'joomla.toolbar.slider';

	/**
	 * Prepare options for this button.
	 *
	 * @param   array  &$options  The options about this button.
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	protected function prepareOptions(array &$options)
	{
		$options['doTask'] = 'Joomla.setcollapse(\'' . $this->_getCommand($this->getUrl()) . '\', \'' .
			$this->getName() . '\', \'' . $this->getHeight() . '\');';

		parent::prepareOptions($options);
	}

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

		$this->text(Text::_($text))
			->name($name)
			->buttonClass($this->fetchIconClass($name))
			->width($width)
			->height($height)
			->url($url)
			->onClose(!empty($options['onClose']) ? ' rel="{onClose: function() {' . $onClose . '}}"' : '');

		return $this->renderButton($this->options);
	}

	/**
	 * Get the button id
	 *
	 * @return  string	Button CSS Id
	 *
	 * @since   3.0
	 */
	public function fetchId()
	{
		return $this->parent->getName() . '-slider-' . $this->getName();
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

	/**
	 * Method to configure available option accessors.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	protected static function getAccessors(): array
	{
		return array_merge(
			parent::getAccessors(),
			[
				'width',
				'height',
				'url',
				'onClose',
			]
		);
	}
}
