<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   Form    $tmpl             The Empty form for template
 * @var   array   $forms            Array of JForm instances for render the rows
 * @var   bool    $multiple         The multiple state for the form field
 * @var   int     $min              Count of minimum repeating in multiple mode
 * @var   int     $max              Count of maximum repeating in multiple mode
 * @var   string  $name             Name of the input field.
 * @var   string  $fieldname        The field name
 * @var   string  $fieldId          The field ID
 * @var   string  $control          The forms control
 * @var   string  $label            The field label
 * @var   string  $description      The field description
 * @var   array   $buttons          Array of the buttons that will be rendered
 * @var   bool    $groupByFieldset  Whether group the subform fields by it`s fieldset
 */
$form = $forms[0];
?>

<div class="subform-wrapper">
    <?php if ($groupByFieldset) : ?>
        <?php foreach ($form->getFieldsets() as $fieldSet) : ?>
            <?php
            /*
             * Fieldset attributes (see https://docs.joomla.org/Advanced_form_guide)
             *  name                Element name
             *                      Required value.
             *  display             Element display style property.
             *                      Default: "grid"
             *  gridtemplatecolumns Element grid-template-columns style property.
             *                      Default: 1fr for each grid field (i.e. all fields in one row)
             *                          If you have 6 fields then all 6 fields will be in one row.
             *                          If you set this to "1fr 1fr" then you will have 4 rows with 2 fields in each.
             *  columngap           Element column-gap style property.
             *                      Default: "1rem"
             *  style               Optional style properties for this field set.
             *                      Example: "border:1px dotted #ccc;padding:2px;box-shadow:2px 2px #888;"
             *  fieldwrapperstyle   Optional style properties for wrapper around each field in this field set.
             *                      Example: "border: 1px solid #f00;padding:2px;"
             */
            $fields = $form->getFieldset($fieldSet->name);

            $cols = implode(' ', array_fill(0, count($fields), '1fr'));

            $fieldsetStyle = [];

            $fieldsetStyle[] = 'display:' . ($fieldSet->display ?? 'grid') . ';';

            $fieldsetStyle[] = 'grid-template-columns:' . ($fieldSet->gridtemplatecolumns ?? $cols)  . ';';

            $fieldsetStyle[] = 'column-gap:' . ($fieldSet->columngap ?? '1rem') . ';';

            $fieldsetStyle[] = $fieldSet->style ?? '';

            $fieldSetOptions = '';

            if (!empty($fieldSet->class)) {
                $fieldSetOptions .= ' class="' . $fieldSet->class . '"';
            }

            if (!empty($fieldsetStyle)) {
                $fieldSetOptions .= ' style="' . implode('', $fieldsetStyle) . '"';
            }

            $fieldWrapperOptions = '';

            if (!empty($fieldSet->fieldwrapperstyle)) {
                $fieldWrapperOptions = ' style="' . $fieldSet->fieldwrapperstyle . '"';
            }

            ?>
            <div<?php echo $fieldSetOptions; ?>>
                <?php foreach ($fields as $field) : ?>
                    <div<?php echo $fieldWrapperOptions; ?>>
                        <?php
                        echo $field->renderField(
                            [
                                'class' => $fieldSet->name . ' ' . $fieldSet->name . '-' . $field->fieldname,
                            ]
                        );
                        ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <?php foreach ($form->getGroup('') as $field) : ?>
            <?php echo $field->renderField(); ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
