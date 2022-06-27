<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\ToolbarButton;
use Joomla\CMS\Uri\Uri;

/**
 * Renders a modal window button
 *
 * @method self    url(string $value)
 * @method self    icon(string $value)
 * @method self    iframeWidth(int $value)
 * @method self    iframeHeight(int $value)
 * @method self    bodyHeight(int $value)
 * @method self    modalWidth(int $value)
 * @method self    onclose(string $value)
 * @method self    title(string $value)
 * @method self    footer(string $value)
 * @method self    selector(string $value)
 * @method self    listCheck(bool $value)
 * @method string  getUrl()
 * @method int     getIframeWidth()
 * @method int     getIframeHeight()
 * @method int     getBodyHeight()
 * @method int     getModalWidth()
 * @method string  getOnclose()
 * @method string  getTitle()
 * @method string  getFooter()
 * @method string  getSelector()
 * @method bool    getListCheck()
 *
 * @since  3.0
 */
class PopupButton extends ToolbarButton
{
    /**
     * Property layout.
     *
     * @var  string
     *
     * @since  4.0.0
     */
    protected $layout = 'joomla.toolbar.popup';

    /**
     * Prepare options for this button.
     *
     * @param   array  $options  The options about this button.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function prepareOptions(array &$options)
    {
        $options['icon'] = $options['icon'] ?? 'icon-square';

        parent::prepareOptions($options);

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
    public function fetchButton(
        $type = 'Modal',
        $name = '',
        $text = '',
        $url = '',
        $iframeWidth = 640,
        $iframeHeight = 480,
        $bodyHeight = null,
        $modalWidth = null,
        $onClose = '',
        $title = '',
        $footer = null
    ) {
        $this->name($name)
            ->text($text)
            ->task($this->_getCommand($url))
            ->url($url)
            ->icon('icon-' . $name)
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
     * Render button HTML.
     *
     * @param   array  $options  The button options.
     *
     * @return  string  The button HTML.
     *
     * @since   4.0.0
     */
    protected function renderButton(array &$options): string
    {
        $html = [];

        $html[] = parent::renderButton($options);

        if ((string) $this->getUrl() !== '') {
            // Build the options array for the modal
            $params = array();
            $params['title']      = $options['title'] ?? $options['text'];
            $params['url']        = $this->getUrl();
            $params['height']     = $options['iframeHeight'] ?? 480;
            $params['width']      = $options['iframeWidth'] ?? 640;
            $params['bodyHeight'] = $options['bodyHeight'] ?? null;
            $params['modalWidth'] = $options['modalWidth'] ?? null;

            // Place modal div and scripts in a new div
            $html[] = '<div class="btn-group" style="width: 0; margin: 0; padding: 0;">';

            $selector = $options['selector'];

            $footer = $this->getFooter();

            if ($footer !== null) {
                $params['footer'] = $footer;
            }

            $html[] = HTMLHelper::_('bootstrap.renderModal', $selector, $params);

            $html[] = '</div>';

            // We have to move the modal, otherwise we get problems with the backdrop
            // @todo: There should be a better workaround than this
            Factory::getDocument()->addScriptDeclaration(
                <<<JS
document.addEventListener('DOMContentLoaded', function() {
  var modal =document.getElementById('{$options['selector']}');
  document.body.appendChild(modal);
  if (Joomla && Joomla.Bootstrap && Joomla.Bootstrap.Methods && Joomla.Bootstrap.Methods.Modal) {
    Joomla.Bootstrap.Methods.Initialise.Modal(modal);
  }
});
JS
            );
        }

        // If an $onClose event is passed, add it to the modal JS object
        if ((string) $this->getOnclose() !== '') {
            Factory::getDocument()->addScriptDeclaration(
                <<<JS
document.addEventListener('DOMContentLoaded', function() {
	document.querySelector('#{$options['selector']}').addEventListener('hide.bs.modal', function() {
	    {$options['onclose']}
	});
});
JS
            );
        }

        return implode("\n", $html);
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
        $url = $url ?? '';

        if (strpos($url, 'http') !== 0) {
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
                'url',
                'iframeWidth',
                'iframeHeight',
                'bodyHeight',
                'modalWidth',
                'onclose',
                'title',
                'footer',
                'selector',
                'listCheck',
            ]
        );
    }
}
