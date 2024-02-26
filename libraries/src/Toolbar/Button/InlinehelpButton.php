<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Renders a button to show / hide the inline help text
 *
 * @method self targetclass(string $value)
 * @method string getTargetclass()
 *
 * @since  4.1.0
 */
class InlinehelpButton extends BasicButton
{
    /**
     * Property layout.
     *
     * @var  string
     *
     * @since  4.1.3
     */
    protected $layout = 'joomla.toolbar.inlinehelp';

    /**
     * Prepare options for this button.
     *
     * @param   array  $options  The options for this button.
     *
     * @return  void
     *
     * @since  4.1.0
     */
    protected function prepareOptions(array &$options)
    {
        $options['text']         = $options['text'] ?: 'JINLINEHELP';
        $options['icon']         = $options['icon'] ?? 'fa-question-circle';
        $options['button_class'] = $options['button_class'] ?? 'btn btn-info';
        $options['attributes']   = array_merge(
            $options['attributes'] ?? [],
            [
                'data-class' => $options['targetclass'] ?? 'hide-aware-inline-help',
            ]
        );

        parent::prepareOptions($options);
    }

    /**
     * Fetches the button HTML code.
     *
     * @param   string  $type         Unused string.
     * @param   string  $targetClass  The class of the DIVs holding the descriptions to toggle.
     * @param   string  $text         Button label
     * @param   string  $icon         Button icon
     * @param   string  $buttonClass  Button class
     *
     * @return  string
     *
     * @since       4.1.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use render() instead.
     */
    public function fetchButton(
        $type = 'Inlinehelp',
        string $targetClass = 'hide-aware-inline-help',
        string $text = 'JINLINEHELP',
        string $icon = 'fa fa-question-circle',
        string $buttonClass = 'btn btn-info'
    ) {
        $this->name('inlinehelp')
            ->targetclass($targetClass)
            ->text($text)
            ->icon($icon)
            ->buttonClass($buttonClass);

        return $this->renderButton($this->options);
    }

    /**
     * Method to configure available option accessors.
     *
     * @return  array
     *
     * @since   4.1.0
     */
    protected static function getAccessors(): array
    {
        return array_merge(
            parent::getAccessors(),
            [
                'targetclass',
            ]
        );
    }
}
