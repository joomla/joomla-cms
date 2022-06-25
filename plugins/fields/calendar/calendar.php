<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Calendar
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Form\Form;

/**
 * Fields Calendar Plugin
 *
 * @since  3.7.0
 */
class PlgFieldsCalendar extends \Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin
{
    /**
     * Transforms the field into a DOM XML element and appends it as a child on the given parent.
     *
     * @param   stdClass    $field   The field.
     * @param   DOMElement  $parent  The field node parent.
     * @param   Form        $form    The form.
     *
     * @return  DOMElement
     *
     * @since   3.7.0
     */
    public function onCustomFieldsPrepareDom($field, DOMElement $parent, Form $form)
    {
        $fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

        if (!$fieldNode) {
            return $fieldNode;
        }

        // Set filter to user UTC
        $fieldNode->setAttribute('filter', 'USER_UTC');

        // Set field to use translated formats
        $fieldNode->setAttribute('translateformat', '1');
        $fieldNode->setAttribute('showtime', $field->fieldparams->get('showtime', 0) ? 'true' : 'false');

        return $fieldNode;
    }
}
