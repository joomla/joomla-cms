<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

use Joomla\CMS\Toolbar\ToolbarButton;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

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
     * Render button HTML.
     *
     * @param   array  $options  The button options.
     *
     * @return  string  The button HTML.
     *
     * @since  4.0.0
     */
    protected function renderButton(array &$options): string
    {
        return (string) ($options['html'] ?? '');
    }

    /**
     * Fetch the HTML for the button
     *
     * @param   string  $type  Button type, unused string.
     * @param   string  $html  HTML string for the button
     * @param   string  $id    CSS id for the button
     *
     * @return  string   HTML string for the button
     *
     * @since   3.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use render() instead.
     */
    public function fetchButton($type = 'Custom', $html = '', $id = 'custom')
    {
        return $html;
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
                'html',
            ]
        );
    }
}
