<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\ToolbarButton;

/**
 * Renders a link button
 *
 * @method self    url(string $value)
 * @method self    target(string $value)
 * @method string  getUrl()
 * @method string  getTarget()
 *
 * @since  3.0
 */
class LinkButton extends ToolbarButton
{
	/**
	 * Property layout.
	 *
	 * @var  string
	 */
	protected $layout = 'joomla.toolbar.link';

	/**
	 * prepareOptions
	 *
	 * @param array $options
	 *
	 * @return  void
	 */
	protected function prepareOptions(array &$options)
	{
		parent::prepareOptions($options);

		unset($options['attributes']['type']);
	}

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string  $type  Unused string.
	 * @param   string  $name  Name to be used as apart of the id
	 * @param   string  $text  Button text
	 * @param   string  $url   The link url
	 *
	 * @return  string  HTML string for the button
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Link', $name = 'back', $text = '', $url = null)
	{
		$this->name($name)
			->text($text)
			->url($url);

		return $this->renderButton($this->options);
	}

	/**
	 * getAccessors
	 *
	 * @return  array
	 */
	protected static function getAccessors(): array
	{
		return array_merge(
			parent::getAccessors(),
			[
				'url',
				'target'
			]
		);
	}
}
