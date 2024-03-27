<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;

$app       = Factory::getApplication();
$form      = $displayData->getForm();
$input     = $app->getInput();
$component = $input->getCmd('option', 'com_content');

if ($component === 'com_categories') {
    $extension = $input->getCmd('extension', 'com_content');
    $parts     = explode('.', $extension);
    $component = $parts[0];
}

$saveHistory = ComponentHelper::getParams($component)->get('save_history', 0);

$fields = $displayData->get('fields') ?: [
    'transition',
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

$hiddenFields   = $displayData->get('hidden_fields') ?: [];

if (!$saveHistory) {
    $hiddenFields[] = 'version_note';
}

if (!Multilanguage::isEnabled()) {
    $hiddenFields[] = 'language';
    $form->setFieldAttribute('language', 'default', '*');
}

$html   = [];
$html[] = '<fieldset class="form-vertical">';
$html[] = '<legend class="visually-hidden">' . Text::_('JGLOBAL_FIELDSET_GLOBAL') . '</legend>';

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
