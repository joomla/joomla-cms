<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla Platform.
 * Provides a select list of integers with specified first, last and step values.
 *
 * @since  1.7.0
 */
class IntegerField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Integer';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   1.7.0
     */
    protected function getOptions()
    {
        $options = [];

        // Initialize some field attributes.
        $first = (int) $this->element['first'];
        $last  = (int) $this->element['last'];
        $step  = (int) $this->element['step'];

        // Sanity checks.
        if ($step == 0) {
            // Step of 0 will create an endless loop.
            return $options;
        } elseif ($first < $last && $step < 0) {
            // A negative step will never reach the last number.
            return $options;
        } elseif ($first > $last && $step > 0) {
            // A position step will never reach the last number.
            return $options;
        } elseif ($step < 0) {
            // Build the options array backwards.
            for ($i = $first; $i >= $last; $i += $step) {
                $options[] = HTMLHelper::_('select.option', $i);
            }
        } else {
            // Build the options array.
            for ($i = $first; $i <= $last; $i += $step) {
                $options[] = HTMLHelper::_('select.option', $i);
            }
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);

        return $options;
    }
}
