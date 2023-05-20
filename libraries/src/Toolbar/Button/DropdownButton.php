<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Toolbar\Button;

use Joomla\CMS\Toolbar\ToolbarButton;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Render dropdown buttons.
 *
 * @method self toggleSplit(bool $value)
 * @method self toggleButtonClass(string $value)
 * @method bool getToggleSplit()
 * @method string getToggleButtonClass()
 *
 * @since  4.0.0
 */
class DropdownButton extends AbstractGroupButton
{
    /**
     * Property layout.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $layout = 'joomla.toolbar.dropdown';

    /**
     * Prepare options for this button.
     *
     * @param   array  $options  The options about this button.
     *
     * @return  void
     *
     * @since   4.0.0
     * @throws  \Exception
     */
    protected function prepareOptions(array &$options)
    {
        parent::prepareOptions($options);

        $childToolbar          = $this->getChildToolbar();
        $options['hasButtons'] = \count($childToolbar->getItems()) > 0;
        $buttons               = $childToolbar->getItems();

        if ($options['hasButtons']) {
            if ($this->getOption('toggleSplit', true)) {
                /** @var ToolbarButton $button */
                $button = array_shift($buttons);

                $childToolbar->setItems($buttons);

                $options['button']        = $button->render();
                $options['caretClass']    = $options['toggleButtonClass'] ?? $button->getButtonClass();
                $options['dropdownItems'] = $childToolbar->render(['is_child' => true]);
            } else {
                $options['dropdownItems'] = $childToolbar->render(['is_child' => true]);

                $button = new BasicButton($this->getName(), $this->getText(), $options);

                $options['button'] = $button
                    ->setParent($this->parent)
                    ->buttonClass($button->getButtonClass() . ' dropdown-toggle')
                    ->attributes(
                        [
                            'data-bs-toggle' => 'dropdown',
                            'data-bs-target' => '#' . $this->fetchId(),
                            'aria-haspopup'  => 'true',
                            'aria-expanded'  => 'false',
                        ]
                    )
                    ->render();
            }
        }
    }

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
        return parent::renderButton($options);
    }

    /**
     * Get the button CSS Id.
     *
     * @return  string  Button CSS Id
     *
     * @since   4.0.0
     */
    protected function fetchId()
    {
        return $this->parent->getName() . '-dropdown-' . $this->getName();
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
                'toggleSplit',
                'toggleButtonClass',
            ]
        );
    }
}
