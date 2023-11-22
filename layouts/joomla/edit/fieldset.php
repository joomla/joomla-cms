<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

$app  = Factory::getApplication();
$form = $displayData->getForm();

$name = $displayData->get('fieldset');
$fieldSet = $form->getFieldset($name);

if (empty($fieldSet)) {
    return;
}

$ignoreFields = $displayData->get('ignore_fields') ? : [];
$extraFields  = $displayData->get('extra_fields') ? : [];

if (!empty($displayData->showOptions) || $displayData->get('show_options', 1)) {
    if (isset($extraFields[$name])) {
        foreach ($extraFields[$name] as $f) {
            if (in_array($f, $ignoreFields)) {
                continue;
            }
            if ($form->getField($f)) {
                $fieldSet[] = $form->getField($f);
            }
        }
    }

    $html = [];

    foreach ($fieldSet as $field) {
        $html[] = $field->renderField();
    }

    echo implode('', $html);
} else {
    $html = [];
    $html[] = '<div class="hidden">';
    foreach ($fieldSet as $field) {
        $html[] = $field->input;
    }
    $html[] = '</div>';

    echo implode('', $html);
}
