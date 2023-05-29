<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field class for the Joomla! CMS.
 *
 * @since  3.0
 */
class HeadertagField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.0
     */
    protected $type = 'HeaderTag';

    /**
     * Method to get the field options.
     *
     * @return  object[]  The field option objects.
     *
     * @since   3.0
     */
    protected function getOptions()
    {
        $options = [];
        $tags    = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'div'];

        // Create one new option object for each tag
        foreach ($tags as $tag) {
            $tmp       = HTMLHelper::_('select.option', $tag, $tag);
            $options[] = $tmp;
        }

        reset($options);

        return $options;
    }
}
