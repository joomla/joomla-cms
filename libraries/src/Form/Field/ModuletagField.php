<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Module Tag field.
 *
 * @since  3.0
 */
class ModuletagField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.0
     */
    protected $type = 'ModuleTag';

    /**
     * Method to get the field options.
     *
     * @return  array  The field option objects.
     *
     * @since   3.0
     */
    protected function getOptions()
    {
        $options = array();
        $tags    = array('address', 'article', 'aside', 'details', 'div', 'footer', 'header', 'main', 'nav', 'section', 'summary');

        // Create one new option object for each tag
        foreach ($tags as $tag) {
            $tmp = HTMLHelper::_('select.option', $tag, $tag);
            $options[] = $tmp;
        }

        reset($options);

        return $options;
    }
}
