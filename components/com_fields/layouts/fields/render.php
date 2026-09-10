<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

// Check if we have all the data
if (!array_key_exists('item', $displayData) || !array_key_exists('context', $displayData)) {
    return;
}

// Setting up for display
$item = $displayData['item'];

if (!$item) {
    return;
}

$context = $displayData['context'];

if (!$context) {
    return;
}

$parts     = explode('.', $context);
$component = $parts[0];
$fields    = null;

if (array_key_exists('fields', $displayData)) {
    $fields = $displayData['fields'];
} else {
    $fields = $item->jcfields ?: FieldsHelper::getFields($context, $item, true);
}

if (empty($fields)) {
    return;
}

// Prepare the output arrays for grouped and ungrouped field HTML
$output = [];
$groups = [];

foreach ($fields as $field) {
    // If the value is empty do nothing
    if (!isset($field->value) || trim($field->value) === '') {
        continue;
    }

    $class = $field->name . ' ' . $field->params->get('render_class');
    $layout = $field->params->get('layout', 'render');
    $content = FieldsHelper::render($context, 'field.' . $layout, ['field' => $field]);

    // If the content is empty do nothing
    if (trim($content) === '') {
        continue;
    }

    $entry = '<li class="field-entry ' . $class . '">' . $content . '</li>';

    // Make a Group array for fields that belong to a group
    if (!empty($field->group_params)) {
        $groupParams = json_decode($field->group_params, true);

        if (!empty($groupParams['group_fields'])) {
            $groupId = $field->group_id ?? 0;

            if (!isset($groups[$groupId])) {
                $groups[$groupId] = [
                    'class'  => $groupParams['class'] ?? '',
                    'fields' => [],
                ];
            }

            $groups[$groupId]['fields'][] = $entry;

            continue;
        }
    }

    $output[] = $entry;
}

if (empty($output) && empty($groups)) {
    return;
}
?>
<?php if (!empty($output)) : ?>
    <ul class="fields-container">
        <?php echo implode("\n", $output); ?>
    </ul>
<?php endif; ?>

<?php // Render grouped fields as separate lists ?>
<?php foreach ($groups as $group) : ?>
    <ul class="fields-container<?php echo $group['class'] ? ' ' . htmlspecialchars($group['class'], ENT_QUOTES, 'UTF-8') : ''; ?>">
        <?php echo implode("\n", $group['fields']); ?>
    </ul>
<?php endforeach; ?>
