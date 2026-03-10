<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

$app       = Factory::getApplication();
$form      = $displayData->getForm();
$input     = $app->getInput();
$component = $input->getCmd('option', 'com_content');

if ($component == 'com_categories') {
    $extension = $input->getCmd('extension', 'com_content');
    $parts     = explode('.', $extension);
    $component = $parts[0];
}

$saveHistory = ComponentHelper::getParams($component)->get('save_history', 0);

$fields = $displayData->get('fields') ?: [
    ['parent', 'parent_id'],
    ['published', 'state', 'enabled'],
    ['category', 'catid'],
    'featured',
    'sticky',
    'access',
    'language',
    'tags',
    'note',
    'version_note',
];

$hiddenFields = $displayData->get('hidden_fields') ?: [];

if (!$saveHistory) {
    $hiddenFields[] = 'version_note';
}

$html   = [];
$html[] = '<fieldset><ul class="list-unstyled">';

foreach ($fields as $field) {
    $field = is_array($field) ? $field : [$field];

    foreach ($field as $f) {
        if ($form->getField($f)) {
            if (in_array($f, $hiddenFields)) {
                $form->setFieldAttribute($f, 'type', 'hidden');
            }

            $html[] = '<li>' . $form->renderField($f) . '</li>';
            break;
        }
    }
}

$html[] = '</ul></fieldset>';

echo implode('', $html);
