<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

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
     * @param   array  $options  The options about this button.
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function prepareOptions(array &$options)
    {
        $options['text'] = $options['text'] ?: 'JTOOLBAR_HELP';
        $options['icon'] = $options['icon'] ?? 'icon-question';
        $options['button_class'] = $options['button_class'] ?? 'btn btn-info';
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
        $url = json_encode(htmlspecialchars($url, ENT_QUOTES), JSON_HEX_APOS);
        $url = substr($url, 1, -1);
        $cmd = "Joomla.popupWindow('$url', '" . Text::_('JHELP', true) . "', 700, 500, 1)";

        return $cmd;
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
                'ref',
                'useComponent',
                'component',
                'url'
            ]
        );
    }
}
