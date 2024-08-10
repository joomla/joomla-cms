<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.list
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Fields\ListField\Extension;

use Joomla\Component\Fields\Administrator\Plugin\FieldsListPlugin;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Fields List Plugin
 *
 * @since  3.7.0
 */
final class ListPlugin extends FieldsListPlugin
{
    /**
     * Before prepares the field value.
     *
     * @param   string     $context  The context.
     * @param   \stdclass  $item     The item.
     * @param   \stdclass  $field    The field.
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public function onCustomFieldsBeforePrepareField($context, $item, $field)
    {
        if (!$this->getApplication()->isClient('api')) {
            return;
        }

        if (!$this->isTypeSupported($field->type)) {
            return;
        }

        $options         = $this->getOptionsFromField($field);
        $field->apivalue = [];

        if (\is_array($field->value)) {
            foreach ($field->value as $value) {
                $field->apivalue[$value] = $options[$value];
            }
        } elseif (!empty($field->value)) {
            $field->apivalue[$field->value] = $options[$field->value];
        }
    }

    /**
     * Prepares the field
     *
     * @param   string    $context  The context.
     * @param   stdclass  $item     The item.
     * @param   stdclass  $field    The field.
     *
     * @return  object
     *
     * @since   3.9.2
     */
    public function onCustomFieldsPrepareField($context, $item, $field)
    {
        // Check if the field should be processed
        if (!$this->isTypeSupported($field->type)) {
            return;
        }

        // The field's rawvalue should be an array
        if (!is_array($field->rawvalue)) {
            $field->rawvalue = (array) $field->rawvalue;
        }

        return parent::onCustomFieldsPrepareField($context, $item, $field);
    }
}
