<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Provides a horizontal scroll bar to specify a value in a range.
 *
 * @link   https://html.spec.whatwg.org/multipage/input.html#range-state-(type=range)
 * @since  3.2
 */
class RangeField extends NumberField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.2
     */
    protected $type = 'Range';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  3.7
     */
    protected $layout = 'joomla.form.field.range';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   3.2
     */
    protected function getInput()
    {
        return $this->getRenderer($this->layout)->render($this->getLayoutData());
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since 3.7
     */
    protected function getLayoutData()
    {
        $data = parent::getLayoutData();

        // Initialize some field attributes.
        $extraData = [
            'max'  => $this->max,
            'min'  => $this->min,
            'step' => $this->step,
        ];

        return array_merge($data, $extraData);
    }
}
