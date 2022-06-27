<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Radio
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */



/**
 * Fields Radio Plugin
 *
 * @since  3.7.0
 */
class PlgFieldsRadio extends \Joomla\Component\Fields\Administrator\Plugin\FieldsListPlugin
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
        if (!$this->app->isClient('api')) {
            return;
        }

        if (!$this->isTypeSupported($field->type)) {
            return;
        }

        $options = $this->getOptionsFromField($field);

        $field->apivalue = [$field->value => $options[$field->value]];
    }
}
