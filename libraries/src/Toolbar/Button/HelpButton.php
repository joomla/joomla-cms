<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Help\Help;
use Joomla\CMS\Language\Text;

/**
 * Renders a help popup window button
 *
 * @method self ref(string $value)
 * @method self component(string $value)
 * @method self useComponent(bool $value)
 * @method self url(string $value)
 * @method string getRef()
 * @method string getComponent()
 * @method bool   getUseComponent()
 * @method string getUrl()
 *
 * @since  3.0
 */
class HelpButton extends BasicButton
{
	/**
	 * Prepare options for this button.
	 *
	 * @param   array  &$options  The options about this button.
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function prepareOptions(array &$options)
	{
		$options['text'] = $options['text'] ?: Text::_('JTOOLBAR_HELP');
		$options['icon'] = $options['icon'] ?? 'fa fa-question';
		$options['button_class'] = $options['button_class'] ?? 'btn btn-outline-info btn-sm';
		$options['onclick'] = $options['onclick'] ?? $this->_getCommand();

		parent::prepareOptions($options);
	}

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
	 *
	 * @deprecated  5.0 Use render() instead.
	 */
	public function fetchButton($type = 'Help', $ref = '', $com = false, $override = null, $component = null)
	{
		$this->name('help')
			->ref($ref)
			->useComponent($com)
			->component($component)
			->url($override);

		return $this->renderButton($this->options);
	}

	/**
	 * Get the JavaScript command for the button
	 *
	 * @return  string  JavaScript command string
	 *
	 * @since   3.0
	 */
	protected function _getCommand()
	{
		// Get Help URL
		$url = Help::createUrl($this->getRef(), $this->getUseComponent(), $this->getUrl(), $this->getComponent());
		$url = htmlspecialchars($url, ENT_QUOTES);
		$cmd = "Joomla.popupWindow('$url', '" . Text::_('JHELP', true) . "', 700, 500, 1)";

		return $cmd;
	}

	/**
	 * Method to configure available option accessors.
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected static function getAccessors(): array
	{
		return array_merge(
			parent::getAccessors(),
			[
				'ref',
				'useComponent',
				'component',
				'url'
			]
		);
	}
}
