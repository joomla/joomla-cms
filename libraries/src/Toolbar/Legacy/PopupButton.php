<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Legacy;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Toolbar\ToolbarButton;

/**
 * Renders a modal window button
 *
 * @method self    url(string $value)
 * @method self    iframeWidth(int $value)
 * @method self    iframeHeight(int $value)
 * @method self    bodyHeight(int $value)
 * @method self    modalWidth(int $value)
 * @method self    onclose(string $value)
 * @method self    title(string $value)
 * @method self    footer(string $value)
 * @method self    selector(string $value)
 * @method string  getUrl()
 * @method int     getIframeWidth()
 * @method int     getIframeHeight()
 * @method int     getBodyHeight()
 * @method int     getModalWidth()
 * @method string  getOnclose()
 * @method string  getTitle()
 * @method string  getFooter()
 * @method string  getSelector()
 *
 * @since  3.0
 */
class PopupButton extends ToolbarButton
{
	/**
	 * Property layout.
	 *
	 * @var  string
	 */
	protected $layout = 'joomla.toolbar.popup';

	/**
	 * prepareOptions
	 *
	 * @param array $options
	 *
	 * @return  void
	 */
	protected function prepareOptions(array &$options)
	{
		$options['doTask'] = $this->_getCommand($this->getUrl());

		$options['selector'] = $options['selector'] ?? 'modal-' . $this->getName();
	}

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
		$this->name($name)
			->text(Text::_($text))
			->task($this->_getCommand($url))
			->url($url)
			->iframeWidth($iframeWidth)
			->iframeHeight($iframeHeight)
			->bodyHeight($bodyHeight)
			->modalWidth($modalWidth)
			->onclose($onClose)
			->title($title)
			->footer($footer);

		return $this->renderButton($this->options);
	}

	/**
	 * renderButton
	 *
	 * @param array $options
	 *
	 * @return  string
	 */
	protected function renderButton(array &$options): string
	{
		$html = [];
		$html[] = parent::renderButton($options);

		// Build the options array for the modal
		$params = array();
		$params['title']      = Text::_($options['title'] ?? $options['text']);
		$params['height']     = $options['iframeHeight'] ?? 480;
		$params['width']      = $options['iframeWidth'] ?? 640;
		$params['bodyHeight'] = $options['bodyHeight'] ?? null;
		$params['modalWidth'] = $options['modalWidth'] ?? null;

		if ((string) $this->getUrl() !== '')
		{
			$params['url'] = $this->getUrl();

			// Place modal div and scripts in a new div
			$html[] = '<div class="btn-group" style="width: 0; margin: 0; padding: 0;">';

			$selector = $options['selector'];

			$html[] = HTMLHelper::_('bootstrap.renderModal', $selector, $params);

			$html[] = '</div>';
		}

		$footer = $this->getFooter();

		if ($footer !== null)
		{
			$params['footer'] = $footer;
		}

		// If an $onClose event is passed, add it to the modal JS object
		if ((string) $this->getOnclose() !== '')
		{
			Factory::getDocument()->addScriptDeclaration(
				<<<JS
window.addEventListener('DOMContentLoaded', function() {
	jQuery('{$options['selector']}').on('hide', function () {
	    {$options['onclose']}
	});
});
JS
			);
		}

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
	public function fetchId()
	{
		return $this->parent->getName() . '-popup-' . $this->getName();
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
				'iframeWidth',
				'iframeHeight',
				'bodyHeight',
				'modalWidth',
				'onclose',
				'title',
				'footer',
				'selector'
			]
		);
	}

}
