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

<div class="subform-wrapper">
    <?php
    if ($groupByFieldset)
    {
        foreach($form->getFieldsets() as $fieldSet)
        {
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
             */

            $fields = $form->getFieldset($fieldSet->name);

            $cols = implode(' ', array_fill(0, count($fields), '1fr'));

            $fieldsetStyle=[];

            $fieldsetStyle[] = 'display:' . ($fieldSet->display ?? 'grid') . ';';

            $fieldsetStyle[] = 'grid-template-columns:' . ($fieldSet->gridtemplatecolumns ?? $cols)  . ';';

            $fieldsetStyle[] = 'column-gap:' . ($fieldSet->columngap ?? '1rem') . ';';

            $fieldsetStyle[] = $fieldSet->style ?? '';

            ?>
            <div class="<?php echo $fieldSet->class ?? ''; ?>"
                 style="<?php echo implode('', $fieldsetStyle); ?>">
                <?php
                foreach ($fields as $field)
                {
                    ?>
                    <div class="<?php echo $fieldSet->name . '-fieldwrapper ' .
                                           $fieldSet->name . '-' . $field->fieldname . '-fieldwrapper'; ?>"
                         style="<?php echo $fieldSet->fieldwrapperstyle ?? ''; ?>"
                    >
                        <?php echo $field->renderField(); ?>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
    }
    else
    {
        foreach ($form->getGroup('') as $field)
        {
            echo $field->renderField();
        }
    }
    ?>
</div>
