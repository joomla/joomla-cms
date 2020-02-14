<?php
/**
 * @package     JCE
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @copyright   Copyright (C) 2006 - 2019 Ryan Demmer. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the JCE.
 * Display a field with a repeatable set of defined sub fields
 *
 * @since       2.7
 */
class JFormFieldRepeatable extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  2.7
     */
    protected $type = 'Repeatable';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   2.7
     */
    protected function getInput()
    {
        // Initialize variables.
        $subForm = new JForm($this->name, array('control' => 'jform'));
        $xml = $this->element->children()->asXml();
        $subForm->load($xml);

        $children = $this->element->children();
        $subForm->setFields($children);

        // And finaly build a main container
        $str = array();

        $values = $this->value;

        // explode to array if string
        if (is_string($values)) {
            $values = explode(',', $values);
        }

        foreach ($values as $index => $value) {

            $str[] = '<div class="form-field-repeatable-item">';

            $n = 0;
            
            foreach ($subForm->getFieldset() as $field) {
                $field->element['multiple'] = true;

                $field->element['name'] = (string) $this->element['name'];

                if (is_array($value)) {
                    $value = isset($value[$n]) ? $value[$n] : $value[0];
                }
 
                // escape value
                $field->value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');

                $field->setup($field->element, $field->value, $this->group);

                // reset id
                $field->id = '';

                if (strpos($field->name, '[]') === false) {
                    $field->name .= '[]';
                }

                $str[] = $field->getInput();
                $str[] = '<button class="btn btn-link form-field-repeatable-add" aria-label="' . JText::_('JGLOBAL_FIELD_ADD') . '"><i class="icon icon-plus pull-right float-right"></i></button>';
                $str[] = '<button class="btn btn-link form-field-repeatable-remove" aria-label="' . JText::_('JGLOBAL_FIELD_REMOVE') . '"><i class="icon icon-trash pull-right float-right"></i></button>';

                $n++;
            }

            $str[] = '</div>';
        }

        return implode("", $str);
    }
}
