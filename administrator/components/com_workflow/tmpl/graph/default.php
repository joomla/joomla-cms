<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       6.1.0
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Language\Text;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('joomla.dialog-autocreate')
    ->useScript('com_workflow.workflowgraph')
    ->useStyle('com_workflow.workflowgraph');

// Populate the language
$this->loadTemplate('texts');

$shortcuts = [
    ['key' => 'Alt + N',                 'description' => Text::_('COM_WORKFLOW_GRAPH_ADD_STAGE')],
    ['key' => 'Alt + M',                 'description' => Text::_('COM_WORKFLOW_GRAPH_ADD_TRANSITION')],
    ['key' => 'Enter / SpaceBar',        'description' => Text::_('COM_WORKFLOW_GRAPH_SELECT_ITEM')],
    ['key' => 'Select + E',              'description' => Text::_('COM_WORKFLOW_GRAPH_EDIT_ITEM')],
    ['key' => 'Select + Delete',         'description' => Text::_('COM_WORKFLOW_GRAPH_TRASH_ITEM')],
    ['key' => 'Select + Backspace',      'description' => Text::_('COM_WORKFLOW_GRAPH_TRASH_ITEM')],
    ['key' => 'Select + Shift + Arrows', 'description' => Text::_('COM_WORKFLOW_GRAPH_MOVE_STAGE')],
    ['key' => 'Escape',                  'description' => Text::_('COM_WORKFLOW_GRAPH_CLEAR_SELECTION')],
    ['key' => '+ / =',                   'description' => Text::_('COM_WORKFLOW_GRAPH_ZOOM_IN')],
    ['key' => '- / _',                   'description' => Text::_('COM_WORKFLOW_GRAPH_ZOOM_OUT')],
    ['key' => 'F',                       'description' => Text::_('COM_WORKFLOW_GRAPH_FIT_VIEW')],
    ['key' => 'Tab',                     'description' => Text::_('COM_WORKFLOW_GRAPH_FOCUS_TYPE_CHANGE')],
    ['key' => 'Arrows',                  'description' => Text::_('COM_WORKFLOW_GRAPH_NAVIGATE_NODES')],
    ['key' => 'Shift + Arrows',          'description' => Text::_('COM_WORKFLOW_GRAPH_MOVE_VIEW')],
];

$col1 = \array_slice($shortcuts, 0, ceil(\count($shortcuts) / 2));
$col2 = \array_slice($shortcuts, ceil(\count($shortcuts) / 2));

$shortcutsHtml   = [];
$shortcutsHtml[] = '<section class="p-3">';
$shortcutsHtml[] = '<div class="row">';
$renderColumn    = function ($column) {
    $html = '<div class="col-md-6">';
    $html .= '<table class="table table-borderless mb-0">';
    $html .= '<legend class="fw-bold mb-2 d-none">' . Text::_('COM_WORKFLOW_GRAPH_SHORTCUTS_TITLE') . '</legend>';
    foreach ($column as $item) {
        $html .= '<tr>';
        $html .= '<th scope="row" class="fw-bold text-nowrap"><kbd>' . htmlspecialchars($item['key']) . '</kbd></th>';
        $html .= '<td>' . $item['description'] . '</td>';
        $html .= '</tr>';
    }
    $html .= '</table></div>';
    return $html;
};

$shortcutsHtml[] = $renderColumn($col1);
$shortcutsHtml[] = $renderColumn($col2);

$shortcutsHtml[] = '</div>';
$shortcutsHtml[] = '</section>';
?>

<template id="shortcuts-popup-content">
    <?php echo implode($shortcutsHtml); ?>
</template>
<section id="workflow-graph-root" aria-label="<?php echo Text::_('COM_WORKFLOW_GRAPH'); ?>"></section>
