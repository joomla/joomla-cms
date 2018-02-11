<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Help\Help;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\ToolbarButton;

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
	 * prepareOptions
	 *
	 * @param array $options
	 *
	 * @return  void
	 */
	protected function prepareOptions(array &$options)
	{
		$options['text'] = $options['text'] ?: \JText::_('JTOOLBAR_HELP');
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
	 * @param   string   $ref        The name of the help screen (its key reference).
	 * @param   boolean  $com        Use the help file in the component directory.
	 * @param   string   $override   Use this URL instead of any other.
	 * @param   string   $component  Name of component to get Help (null for current component)
	 *
	 * @return  string   JavaScript command string
	 *
	 * @since   3.0
	 */
	protected function _getCommand()
	{
		// Get Help URL
		$url = Help::createUrl($this->getRef(), $this->getUseComponent(), $this->getUrl(), $this->getComponent());
		$url = htmlspecialchars($url, ENT_QUOTES);
		$cmd = "Joomla.popupWindow('$url', '" . \JText::_('JHELP', true) . "', 700, 500, 1)";

		return $cmd;
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
				'ref',
				'useComponent',
				'component',
				'url'
			]
		);
	}
}
