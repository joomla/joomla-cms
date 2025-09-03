<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
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

$workflowId = $field ? $field->getAttribute('workflow_id') : null;
if (!$workflowId) {
    return;
}
$popupId = 'workflow-graph-modal-content';
$popupOptions = json_encode([
    'src'             => '#' . $popupId,
    'height'          => 'fit-content',
    'textHeader'      => Text::_('COM_WORKFLOW_GRAPH'),
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
                <span class="fa fa-diagram-project" aria-hidden="true"></span>
            </button>
            <div role="tooltip" id="tip-graph">
                <?php echo Text::_('COM_WORKFLOW_GRAPH'); ?>
            </div>
        </div>
    </div>
</div>

<template id="workflow-graph-modal-content">
<div class="p-3">
    <section
        class="d-flex flex-wrap align-items-center justify-content-between"
        aria-labelledby="workflow-main-title"
    >
        <div class="col-md-6 d-flex flex-column">
            <h3 id="workflow-main-title" class="mb-2"></h3>
            <dl class="d-flex align-items-center flex-wrap mb-0">
                <dt class="visually-hidden">Status:</dt>
                <dd class="me-3 mb-1 d-flex mb-0">
                    <span class="badge" role="status"></span>
                </dd>
                <dt class="visually-hidden">Stage Count:</dt>
                <dd class="me-3 mb-1 d-flex mb-0">
                    <span></span>
                </dd>
                <dt class="visually-hidden">Transition Count:</dt>
                <dd class="me-3 mb-1 d-flex mb-0">
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
                <ul class="d-flex flex-column gap-1 list-unstyled mb-0">
                  <li>
                    <button
                      class="zoom-btn zoom-in"
                      title="<?php echo Text::_('COM_WORKFLOW_GRAPH_ZOOM_IN'); ?>"
                    >
                      <span class="icon icon-plus" aria-hidden="true" />
                      <span class="visually-hidden"><?php echo Text::_('COM_WORKFLOW_GRAPH_ZOOM_IN'); ?></span>
                    </button>
                  </li>
                  <li>
                    <button
                      class="zoom-btn zoom-out"
                      title="<?php echo Text::_('COM_WORKFLOW_GRAPH_ZOOM_OUT'); ?>"
                      @click="zoomOut"
                      @keydown.enter="zoomOut"
                      @keydown.space.prevent="zoomOut"
                    >
                      <span class="icon icon-minus" aria-hidden="true" />
                      <span class="visually-hidden"><?php echo Text::_('COM_WORKFLOW_GRAPH_ZOOM_OUT'); ?></span>
                    </button>
                  </li>
                  <li>
                    <button
                      class="zoom-btn fit-screen"
                      title="<?php echo Text::_('COM_WORKFLOW_GRAPH_FIT_VIEW'); ?>"
                      @click="customFitView"
                      @keydown.enter="customFitView"
                      @keydown.space.prevent="customFitView"
                    >
                      <span class="icon icon-expand" aria-hidden="true" />
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
