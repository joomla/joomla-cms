<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       __DEPLOY_VERSION__
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive');
$wa->useScript('form.validate');
$wa->useScript('joomla.dialog');
$wa->useScript('joomla.dialog-autocreate');
$wa->useStyle('com_workflow.workflowgraph');

// Populate the language
$this->loadTemplate('texts');

// Get the URI for the JavaScript module
$script = $wa->getAsset('script', name: 'com_workflow.workflowgraph')->getUri(true);

$shortcuts = [
    ['key' => 'Alt + N',             'description' => 'Add Stage'],
    ['key' => 'Alt + M',             'description' => 'Add Transition'],
    ['key' => 'Enter / SpaceBar',    'description' => 'Select Item'],
    ['key' => 'Select + E',          'description' => 'Edit Item'],
    ['key' => 'Select + Delete',     'description' => 'Delete Item'],
    ['key' => 'Select + Backspace',  'description' => 'Delete Item'],
    ['key' => 'Select + Shift + Arrows', 'description' => 'Move Stage'],
    ['key' => 'Ctrl/Cmd + Z',        'description' => 'Undo'],
    ['key' => 'Ctrl/Cmd + Y',        'description' => 'Redo'],
    ['key' => 'Escape',              'description' => 'Clear Selection'],
    ['key' => '+ / =',               'description' => 'Zoom In'],
    ['key' => '- / _',               'description' => 'Zoom Out'],
    ['key' => 'F',                   'description' => 'Fit View'],
    ['key' => 'Tab',                 'description' => 'Focus Type Change'],
    ['key' => 'Arrows',              'description' => 'Navigate Nodes'],
    ['key' => 'Shift + Arrows',      'description' => 'Move View'],
];

$col1 = array_slice($shortcuts, 0, ceil(count($shortcuts) / 2));
$col2 = array_slice($shortcuts, ceil(count($shortcuts) / 2));

$shortcutsHtml = [];
$shortcutsHtml[] = '<section class="p-3">';
$shortcutsHtml[] = '<section class="row" role="group" aria-label="Keyboard shortcuts columns">';

$renderColumn = function ($column) {
    $html = '<section class="col-md-6">';
    $html .= '<table class="table table-borderless mb-0">';
    foreach ($column as $item) {
        $html .= '<tr>';
        $html .= '<th scope="row" class="fw-bold text-nowrap"><kbd>' . htmlspecialchars($item['key']) . '</kbd></th>';
        $html .= '<td>' . Text::_($item['description']) . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table></section>';
    return $html;
};

$shortcutsHtml[] = $renderColumn($col1);
$shortcutsHtml[] = $renderColumn($col2);

$shortcutsHtml[] = '</section>';
$shortcutsHtml[] = '</section>';
?>

<template id="shortcuts-popup-content">
    <?php echo implode($shortcutsHtml); ?>
</template>
<section id="workflow-graph-root" aria-label="Workflow graph"></section>
<script type="module" src="<?php echo $script ?>"></script>
