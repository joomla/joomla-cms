<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Toolbar\ToolbarButton;

/**
 * Renders a custom button
 *
 * @method self html(string $value)
 * @method string getHtml()
 *
 * @since  3.0
 */
class CustomButton extends ToolbarButton
{
	/**
	 * renderButton
	 *
	 * @param array $options
	 *
	 * @return  string
	 */
	protected function renderButton(array &$options): string
	{
		return (string) ($options['html'] ?? '');
	}

	/**
	 * Fetch the HTML for the button
	 *
	 * @param   string  $type  Button type, unused string.
	 * @param   string  $html  HTML strng for the button
	 * @param   string  $id    CSS id for the button
	 *
	 * @return  string   HTML string for the button
	 *
	 * @since   3.0
	 */
	public function fetchButton($type = 'Custom', $html = '', $id = 'custom')
	{
		return $html;
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
				'html',
			]
		);
	}
}
