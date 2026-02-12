<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

extract($displayData);
$wa   = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_workflow');

$wa->useScript('joomla.dialog-autocreate');
$wa->useScript('com_workflow.workflowgraphclient')
    ->useStyle('com_workflow.workflowgraphclient');


// Populate the language
$translationStrings = [
    'COM_WORKFLOW_GRAPH',
    'COM_WORKFLOW_GRAPH_ADD_STAGE',
    'COM_WORKFLOW_GRAPH_ADD_STAGE_DIALOG_OPENED',
    'COM_WORKFLOW_GRAPH_ADD_TRANSITION_DIALOG_OPENED',
    'COM_WORKFLOW_GRAPH_ADD_TRANSITION',
    'COM_WORKFLOW_GRAPH_API_NOT_SET',
    'COM_WORKFLOW_GRAPH_BACKGROUND',
    'COM_WORKFLOW_GRAPH_CANVAS_DESCRIPTION',
    'COM_WORKFLOW_GRAPH_CANVAS_LABEL',
    'COM_WORKFLOW_GRAPH_CANVAS_VIEW_CONTROLS',
    'COM_WORKFLOW_GRAPH_CLEAR_SELECTION',
    'COM_WORKFLOW_GRAPH_CLOSE_ACTIONS_MENU',
    'COM_WORKFLOW_GRAPH_CONTROLS',
    'COM_WORKFLOW_GRAPH_CREATING_TRANSITION',
    'COM_WORKFLOW_GRAPH_DEFAULT',
    'COM_WORKFLOW_GRAPH_DISABLED',
    'COM_WORKFLOW_GRAPH_EDIT_ITEM',
    'COM_WORKFLOW_GRAPH_EDIT_STAGE',
    'COM_WORKFLOW_GRAPH_EDIT_TRANSITION',
    'COM_WORKFLOW_GRAPH_ENABLED',
    'COM_WORKFLOW_GRAPH_ERROR_API_RETURNED_ERROR',
    'COM_WORKFLOW_GRAPH_ERROR_CSRF_TOKEN_NOT_SET',
    'COM_WORKFLOW_GRAPH_ERROR_EXTENSION_NOT_SET',
    'COM_WORKFLOW_GRAPH_ERROR_FAILED_TO_UPDATE_STAGE_POSITIONS',
    'COM_WORKFLOW_GRAPH_ERROR_FETCHING_MODEL',
    'COM_WORKFLOW_GRAPH_ERROR_INVALID_ID',
    'COM_WORKFLOW_GRAPH_ERROR_INVALID_POSITION_DATA',
    'COM_WORKFLOW_GRAPH_ERROR_INVALID_STAGE_POSITIONS',
    'COM_WORKFLOW_GRAPH_ERROR_NOT_AUTHENTICATED',
    'COM_WORKFLOW_GRAPH_ERROR_NO_PERMISSION',
    'COM_WORKFLOW_GRAPH_ERROR_REQUEST_FAILED',
    'COM_WORKFLOW_GRAPH_ERROR_STAGES_NOT_FOUND',
    'COM_WORKFLOW_GRAPH_ERROR_STAGE_DEFAULT_CANT_DELETED',
    'COM_WORKFLOW_GRAPH_ERROR_STAGE_HAS_TRANSITIONS',
    'COM_WORKFLOW_GRAPH_ERROR_UNKNOWN',
    'COM_WORKFLOW_GRAPH_ERROR_WORKFLOW_ID_NOT_SET',
    'COM_WORKFLOW_GRAPH_ERROR_WORKFLOW_NOT_FOUND',
    'COM_WORKFLOW_GRAPH_FIT_VIEW',
    'COM_WORKFLOW_GRAPH_FOCUS_TYPE_CHANGE',
    'COM_WORKFLOW_GRAPH_LOADING',
    'COM_WORKFLOW_GRAPH_MINIMAP_HIDE',
    'COM_WORKFLOW_GRAPH_MINIMAP_LABEL',
    'COM_WORKFLOW_GRAPH_MINIMAP_SHOW',
    'COM_WORKFLOW_GRAPH_MOVE_STAGE',
    'COM_WORKFLOW_GRAPH_MOVE_VIEW',
    'COM_WORKFLOW_GRAPH_NAVIGATE_NODES',
    'COM_WORKFLOW_GRAPH_OPEN_ACTIONS_MENU',
    'COM_WORKFLOW_GRAPH_SELECTION_CLEARED',
    'COM_WORKFLOW_GRAPH_SELECT_ITEM',
    'COM_WORKFLOW_GRAPH_SHORTCUTS',
    'COM_WORKFLOW_GRAPH_SHORTCUTS_TITLE',
    'COM_WORKFLOW_GRAPH_STAGE',
    'COM_WORKFLOW_GRAPH_STAGES',
    'COM_WORKFLOW_GRAPH_STAGE_ACTIONS',
    'COM_WORKFLOW_GRAPH_STAGE_COUNT',
    'COM_WORKFLOW_GRAPH_STAGE_DESCRIPTION',
    'COM_WORKFLOW_GRAPH_STAGE_POSITIONS_UPDATED',
    'COM_WORKFLOW_GRAPH_STAGE_REF',
    'COM_WORKFLOW_GRAPH_STAGE_SELECTED',
    'COM_WORKFLOW_GRAPH_STAGE_STATUS_ENABLED',
    'COM_WORKFLOW_GRAPH_STAGE_STATUS_DISABLED',
    'COM_WORKFLOW_GRAPH_STATUS',
    'COM_WORKFLOW_GRAPH_TRANSITION',
    'COM_WORKFLOW_GRAPH_TRANSITIONS',
    'COM_WORKFLOW_GRAPH_TRANSITION_ACTIONS',
    'COM_WORKFLOW_GRAPH_TRANSITION_COUNT',
    'COM_WORKFLOW_GRAPH_TRANSITION_DESCRIPTION',
    'COM_WORKFLOW_GRAPH_TRANSITION_PATH',
    'COM_WORKFLOW_GRAPH_TRANSITION_REF',
    'COM_WORKFLOW_GRAPH_TRANSITION_SELECTED',
    'COM_WORKFLOW_GRAPH_TRANSITION_STATUS_ENABLED',
    'COM_WORKFLOW_GRAPH_TRANSITION_STATUS_DISABLED',
    'COM_WORKFLOW_GRAPH_TRASH_ITEM',
    'COM_WORKFLOW_GRAPH_TRASH_STAGE',
    'COM_WORKFLOW_GRAPH_TRASH_STAGE_CONFIRM',
    'COM_WORKFLOW_GRAPH_TRASH_STAGE_FAILED',
    'COM_WORKFLOW_GRAPH_TRASH_TRANSITION',
    'COM_WORKFLOW_GRAPH_TRASH_TRANSITION_CONFIRM',
    'COM_WORKFLOW_GRAPH_TRASH_TRANSITION_FAILED',
    'COM_WORKFLOW_GRAPH_UNSAVED_CHANGES',
    'COM_WORKFLOW_GRAPH_UPDATE_STAGE_POSITION_FAILED',
    'COM_WORKFLOW_GRAPH_UP_TO_DATE',
    'COM_WORKFLOW_GRAPH_WORKFLOWS_EDIT',
    'COM_WORKFLOW_GRAPH_ZOOM_IN',
    'COM_WORKFLOW_GRAPH_ZOOM_OUT',
];

foreach ($translationStrings as $string) {
    Text::script($string);
}

$workflowId = $field ? $field->getAttribute('workflow_id') : null;
if (!$workflowId) {
    return;
}
$popupId = 'workflow-graph-modal-content';
$popupOptions = json_encode([
    'src'             => '#' . $popupId,
    'height'          => 'fit-content',
    'textHeader'      => Text::_('COM_WORKFLOW_GRAPH_FULL'),
    'preferredParent' => 'body',
    'modal'           => true,
]);

?>
<div class="d-flex align-items-center gap-3">
    <div class="flex-grow-1">
        <?php echo LayoutHelper::render('joomla.form.field.groupedlist-fancy-select', $displayData); ?>
    </div>
    <div class="flex-shrink-0">
        <div class="align-center text-center btns">
            <button type="button" class="btn btn-primary px-3 py-2" data-joomla-dialog="<?php echo htmlspecialchars($popupOptions, ENT_QUOTES, 'UTF-8'); ?>">
                <span class="fa fa-diagram-project" aria-hidden="true"></span> <?php echo Text::_('COM_WORKFLOW_GRAPH_VIEW'); ?>
            </button>
        </div>
    </div>
</div>

<template id="workflow-graph-modal-content">
<div class="p-3">
    <section
        class="d-flex flex-wrap align-items-center justify-content-between"
    >
        <div class="col-md-6 d-flex flex-column">
            <dl class="d-flex align-items-center flex-wrap mb-0">
                <dt class="visually-hidden"><?php echo Text::_('COM_WORKFLOW_GRAPH_STATUS'); ?>:</dt>
                <dd class="me-3 mb-1 d-flex mb-0">
                    <span class="badge" role="status" id="workflow-status-badge"></span>
                </dd>
                <dt class="visually-hidden"><?php echo Text::_('COM_WORKFLOW_GRAPH_STAGE_COUNT'); ?>:</dt>
                <dd class="me-3 mb-1 d-flex mb-0" id="workflow-stage-count">
                    <span></span>
                </dd>
                <dt class="visually-hidden"><?php echo Text::_('COM_WORKFLOW_GRAPH_TRANSITION_COUNT'); ?>:</dt>
                <dd class="me-3 mb-1 d-flex mb-0" id="workflow-transition-count">
                    <span></span>
                </dd>
            </dl>
        </div>
    </section>
    <div id="workflow-graph">
        <div id="workflow-container" data-workflow-id="<?php echo (int) $workflowId; ?>">
            <div id="graph">
                <div id="stages"></div>
                <svg id="connections"></svg>
            </div>
            <div class="zoom-controls">
              <div
                ref="controlsContainer"
                class="custom-controls z-10"
                aria-labelledby="canvas-controls-title"
              >
                <h2 id="canvas-controls-title" class="visually-hidden"><?php echo Text::_('COM_WORKFLOW_GRAPH_CANVAS_VIEW_CONTROLS'); ?></h2>
                <ul class="d-flex flex-column gap-1 list-unstyled mb-0 px-0">
                  <li>
                    <button class="toolbar-button custom-controls-button zoom-btn zoom-in" title="<?php echo Text::_('COM_WORKFLOW_GRAPH_ZOOM_IN'); ?>">
                      <span class="icon icon-plus" aria-hidden="true"></span>
                      <span class="visually-hidden"><?php echo Text::_('COM_WORKFLOW_GRAPH_ZOOM_IN'); ?></span>
                    </button>
                  </li>
                  <li>
                    <button class="toolbar-button custom-controls-button zoom-btn zoom-out" title="<?php echo Text::_('COM_WORKFLOW_GRAPH_ZOOM_OUT'); ?>">
                      <span class="icon icon-minus" aria-hidden="true"></span>
                      <span class="visually-hidden"><?php echo Text::_('COM_WORKFLOW_GRAPH_ZOOM_OUT'); ?></span>
                    </button>
                  </li>
                  <li>
                    <button class="toolbar-button custom-controls-button zoom-btn fit-screen" title="<?php echo Text::_('COM_WORKFLOW_GRAPH_FIT_VIEW'); ?>">
                      <span class="icon icon-expand" aria-hidden="true"></span>
                      <span class="visually-hidden"><?php echo Text::_('COM_WORKFLOW_GRAPH_FIT_VIEW'); ?></span>
                    </button>
                  </li>
                </ul>
              </div>
            </div>
        </div>
    </div>
</div>
</template>
