<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

$app    = Factory::getApplication();
$form   = $displayData->getForm();
$input  = $app->input;

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

if (!ModuleHelper::isAdminMultilang()) {
    $hiddenFields[] = 'language';
    $form->setFieldAttribute('language', 'default', '*');
}

$html   = [];
$html[] = '<fieldset class="form-vertical">';

foreach ($fields as $field) {
    foreach ((array) $field as $f) {
        if ($form->getField($f)) {
            if (in_array($f, $hiddenFields)) {
                $form->setFieldAttribute($f, 'type', 'hidden');
            }

            $html[] = $form->renderField($f);
            break;
        }
    }
}

$html[] = '</fieldset>';

echo implode('', $html);
