<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

\defined('JPATH_PLATFORM') or die;

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
	 *
	 * @since  4.0.0
	 */
	protected $layout = 'joomla.toolbar.link';

	/**
	 * Prepare options for this button.
	 *
	 * @param   array  $options  The options about this button.
	 *
	 * @return  void
	 *
	 * @since  4.0.0
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
	 *
	 * @deprecated  5.0 Use render() instead.
	 */
	public function fetchButton($type = 'Link', $name = 'back', $text = '', $url = null)
	{
		$this->name($name)
			->text($text)
			->url($url);

		return $this->renderButton($this->options);
	}

	/**
	 * Method to configure available option accessors.
	 *
	 * @return  array
	 *
	 * @since  4.0.0
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
