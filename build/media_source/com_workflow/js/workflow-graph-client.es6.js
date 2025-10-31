/**
 * @copyright (C) 2025 Open Source Matters
 * @license  GNU GPL v2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};
(() => {
  // --- Constants ---
  const STAGE_WIDTH = 200;
  const STAGE_HEIGHT = 100;
  const MIN_ZOOM = 0.5;
  const MAX_ZOOM = 2;
  const ZOOM_SENSITIVITY = 0.1;

  // This central state object holds all data needed for rendering.
  const state = {
    workflow: null,
    stages: [],
    transitions: [],
    scale: 1,
    panX: 0,
    panY: 0,
    isDraggingStage: false,
    highlightedEdge: null,
  };

  // --- API Communication & Error Handling ---
  const translate = (string) => {
    return Joomla.Text._(string);
  };

  const sprintf = (string, ...args) => {
    const base = Joomla.Text._(string, string);
    let i = 0;
    return base.replace(/%((%)|s|d)/g, (m) => {
      let val = args[i];

      if (m === '%d') {
        val = parseFloat(val);
        if (Number.isNaN(val)) {
          val = 0;
        }
      }
      i += 1;
      return val;
    });
  };

  function showMessageInModal(message, type) {
    const messages = {};
    messages[type] = [Joomla.Text._(message)];
    Joomla.renderMessages(messages);
    if (type === 'error') {
      const dialog = document.querySelector('joomla-dialog');
      if (dialog) {
        dialog.close();
      }
    }
  }

  async function makeRequest(url) {
    try {
      const paths = Joomla.getOptions('system.paths');
      const baseUri = `${paths ? `${paths.rootFull}/administrator/index.php` : window.location.pathname}`;
      const uri = `${baseUri}?option=com_workflow&extension=com_content&layout=modal&view=graph${url}`;
      const response = await fetch(uri, { credentials: 'same-origin' });

      if (!response.ok) {
        let message = 'COM_WORKFLOW_GRAPH_ERROR_UNKNOWN';
        if (response.status === 401) message = 'COM_WORKFLOW_GRAPH_ERROR_NOT_AUTHENTICATED';
        else if (response.status >= 403) message = 'COM_WORKFLOW_GRAPH_ERROR_NO_PERMISSION';
        else message = sprintf('COM_WORKFLOW_GRAPH_ERROR_REQUEST_FAILED', response.status);
        throw new Error(message);
      }
      const responseData = await response.json();
      if (responseData.success === false) {
        throw new Error(responseData.message || 'COM_WORKFLOW_GRAPH_ERROR_API_RETURNED_ERROR');
      }
      return responseData;
    } catch (err) {
      showMessageInModal(err.message, "error");
      return false;
    }
  }

  // --- Layout Calculation ---
  function calculateAutoLayout(stages) {
    const withNoPosition = stages.filter(stage => !stage.position || isNaN(stage.position.x) || isNaN(stage.position.y));
    if (withNoPosition.length === 0) return stages;

    // special node
    const fromAnyStage = stages.find(s => s.id === 'From Any');
    const transitionStages = stages.filter(s => s.id !== 'From Any');

    const verticalSpacing = 80;
    const horizontalOffset = 350;
    const startY = 50;

    transitionStages.forEach((stage, index) => {
      if (withNoPosition.some(s => s.id === stage.id)) {
        stage.position = {
          x: horizontalOffset,
          y: startY + (index * verticalSpacing)
        };
      }
    });

    if (fromAnyStage && withNoPosition.some(s => s.id === fromAnyStage.id)) {
      if (!fromAnyStage.position || isNaN(fromAnyStage.position.x) || isNaN(fromAnyStage.position.y)) {
        fromAnyStage.position = {
          x: 600,
          y: -200
        };
      }
    }

    return stages;
  }

  function getSmoothStepPath(sourceX, sourceY, targetX, targetY, sourcePosition, targetPosition, centerX, centerY) {
    let path = `M ${sourceX},${sourceY}`;
    const midX = centerX || (sourceX + targetX) / 2;
    const midY = centerY || (sourceY + targetY) / 2;
    const branchDistance = 30;

    if (sourcePosition === 'left' || sourcePosition === 'right') {
      const branchX = sourcePosition === 'left' ? sourceX - branchDistance : sourceX + branchDistance;
      path += ` L ${branchX},${sourceY} L ${branchX},${midY} L ${midX},${midY}`;
      const mergeX = targetPosition === 'left' ? targetX - branchDistance : targetX + branchDistance;
      path += ` L ${mergeX},${midY} L ${mergeX},${targetY} L ${targetX},${targetY}`;
    } else if (sourcePosition === 'top' || sourcePosition === 'bottom') {
      const branchY = sourcePosition === 'top' ? sourceY - branchDistance : sourceY + branchDistance;
      path += ` L ${sourceX},${branchY} L ${midX},${branchY} L ${midX},${midY}`;
      const mergeY = targetPosition === 'top' ? targetY - branchDistance : targetY + branchDistance;
      path += ` L ${midX},${mergeY} L ${midX},${targetY} L ${targetX},${targetY}`;
    } else {
      const branchX = sourceX + (targetX > sourceX ? branchDistance : -branchDistance);
      path += ` L ${branchX},${sourceY} L ${branchX},${midY} L ${midX},${midY}`;
      const mergeX = targetX + (targetX > sourceX ? -branchDistance : branchDistance);
      path += ` L ${mergeX},${midY} L ${mergeX},${targetY} L ${targetX},${targetY}`;
    }
    return [path, midX, midY];
  }

  function generateEdges(transitions, stages) {
    const stageMap = new Map(stages.map(s => [s.id, s]));
    const edgeGroups = {};

    transitions.forEach(tr => {
      const fromId = tr.from_stage_id === -1 ? 'From Any' : tr.from_stage_id;
      const toId = tr.to_stage_id;
      const key = `${fromId}->${toId}`;
      if (!edgeGroups[key]) edgeGroups[key] = [];
      edgeGroups[key].push(tr);
    });

    const edgePairs = new Set(transitions.map(tr => `${tr.from_stage_id}->${tr.to_stage_id}`));
    return transitions.flatMap(tr => {
      const fromId = tr.from_stage_id === -1 ? 'From Any' : tr.from_stage_id;
      const toId = tr.to_stage_id;
      const fromStage = stageMap.get(fromId);
      const toStage = stageMap.get(toId);

      if (!fromStage?.position || !toStage?.position) return [];

      // Calculate source and target positions for step-wise edges
      let sourceX = fromStage.position.x + STAGE_WIDTH / 2;
      let sourceY = fromStage.position.y + STAGE_HEIGHT / 2;
      let targetX = toStage.position.x + STAGE_WIDTH / 2;
      let targetY = toStage.position.y + STAGE_HEIGHT / 2;
      let sourcePosition = 'center', targetPosition = 'center';

      // If target is to the left/right, connect to left/right edge
      if (Math.abs(toStage.position.x - fromStage.position.x) > Math.abs(toStage.position.y - fromStage.position.y)) {
        if (toStage.position.x < fromStage.position.x) {
          sourceX = fromStage.position.x;
          targetX = toStage.position.x + STAGE_WIDTH;
          sourcePosition = 'left';
          targetPosition = 'right';
        } else {
          sourceX = fromStage.position.x + STAGE_WIDTH;
          targetX = toStage.position.x;
          sourcePosition = 'right';
          targetPosition = 'left';
        }
      } else {
        // If target is above/below, connect to top/bottom edge
        if (toStage.position.y < fromStage.position.y) {
          sourceY = fromStage.position.y;
          targetY = toStage.position.y + STAGE_HEIGHT;
          sourcePosition = 'top';
          targetPosition = 'bottom';
        } else {
          sourceY = fromStage.position.y + STAGE_HEIGHT;
          targetY = toStage.position.y;
          sourcePosition = 'bottom';
          targetPosition = 'top';
        }
      }

      const groupKey = `${fromId}->${toStage.id}`;
      const group = edgeGroups[groupKey] || [tr];
      const transitionIndex = group.findIndex(t => t.id === tr.id);
      const offsetIndex = transitionIndex - (group.length - 1) / 2;

      // Calculate perpendicular offset
      const dx = targetX - sourceX;
      const dy = targetY - sourceY;
      const length = Math.sqrt(dx * dx + dy * dy) || 1;
      const perpX = -dy / length;
      const perpY = dx / length;
      const curveMagnitude = 40 * offsetIndex;
      const centerX = (sourceX + targetX) / 2 + perpX * curveMagnitude;
      const centerY = (sourceY + targetY) / 2 + perpY * curveMagnitude;

      // Generate sharp step path
      const [pathData, labelX, labelY] = getSmoothStepPath(
        sourceX,
        sourceY,
        targetX,
        targetY,
        sourcePosition,
        targetPosition,
        centerX,
        centerY,
      );

      // Determine arrowhead direction based on final segment
      let arrowDirection = 'right';
      if (Math.abs(targetX - centerX) > Math.abs(targetY - centerY)) {
        arrowDirection = (targetX > centerX) ? 'right' : 'left';
      } else {
        arrowDirection = (targetY > centerY) ? 'down' : 'up';
      }

      // Mark as bidirectional if the reverse exists
      const isBidirectional = edgePairs.has(`${toId}->${tr.from_stage_id}`);

      return {
        id: `transition-${tr.id}`,
        pathData,
        label: tr.title,
        labelPosition: { x: labelX, y: labelY },
        fromId,
        toId,
        isBidirectional,
        arrowDirection
      };
    }).filter(Boolean);
  }

  function renderGraph(modal) {
    const graph = modal.querySelector('#graph');
    const stageContainer = modal.querySelector('#stages');
    const svg = modal.querySelector('#connections');
    if (!graph || !stageContainer || !svg) return;

    // Remove all existing stage elements before rendering to avoid duplicates
    stageContainer.querySelectorAll('[id^="stage-"]').forEach(el => el.remove());
    state.stages.forEach(stage => {
      let stageEl = document.createElement('div');
      stageEl.id = `stage-${stage.id}`;
      stageEl.addEventListener('mousedown', e => { if (e.button === 0) handleNodeDrag(e, stage); });
      const isVirtual = stage.id === 'From Any';
      stageEl.className = `stage ${stage.default ? 'default' : ''} ${isVirtual ? 'virtual' : ''}`;
      stageEl.style.left = `${stage.position.x}px`;
      stageEl.style.top = `${stage.position.y}px`;
      let newHTML;
      if (isVirtual) {
        newHTML = `
              <div class="stage-title text-truncate" title="${stage.title}">${stage.title}</div>
              <div class="d-flex align-items-center gap-2">
                <div class="stage-description text-truncate small"></div>
              </div>
              <div class="d-flex justify-content-between align-items-center mt-2">
                <div class="badge bg-info rounded-pill p-1"></div>
              </div>
              `;
      } else {
        newHTML = `
              <div class="stage-title text-truncate" title="${stage.title}">${stage.title}</div>
              <div class="d-flex align-items-center gap-2">
                ${stage.description ? `<div class="stage-description text-truncate small" title="${stage?.description}">${stage?.description}</div>` : ''}
              </div>
              <div class="d-flex justify-content-between align-items-center mt-2">
                ${typeof stage.published !== 'undefined' ? `<div class="badge ${stage.published == 1 ? 'bg-success' : 'bg-danger'} rounded-pill p-1">${stage.published === 1 ? translate('COM_WORKFLOW_GRAPH_ENABLED') : translate('COM_WORKFLOW_GRAPH_DISABLED')}</div>` : ''}
                ${stage.default ? `<div class="badge bg-warning rounded-pill p-1">${translate('COM_WORKFLOW_GRAPH_DEFAULT')}</div>` : ''}
              </div>`;
      }
      stageEl.innerHTML = newHTML;
      stageContainer.appendChild(stageEl);
    });

    const edges = generateEdges(state.transitions, state.stages);
    svg.querySelectorAll('g.edge-group').forEach(group => {
      if (!edges.find(e => e.id === group.dataset.edgeId)) group.remove();
    });

    edges.forEach(edge => {
      let group = svg.querySelector(`g.edge-group[data-edge-id="${edge.id}"]`);
      if (!group) {
        group = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        group.setAttribute('class', 'edge-group');
        group.dataset.edgeId = edge.id;

        const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.setAttribute('class', 'transition-path');
        path.setAttribute('marker-end', 'url(#arrowhead)');

        const foreignObject = document.createElementNS('http://www.w3.org/2000/svg', 'foreignObject');
        foreignObject.setAttribute('width', '1');
        foreignObject.setAttribute('height', '1');
        foreignObject.style.overflow = 'visible';

        const labelDiv = document.createElement('div');
        labelDiv.className = 'transition-label-content';
        labelDiv.addEventListener('click', e => {
          e.stopPropagation();
          state.highlightedEdge = state.highlightedEdge === edge.id ? null : edge.id;
          renderGraph(modal);
        });

        foreignObject.appendChild(labelDiv);
        group.appendChild(path);
        group.appendChild(foreignObject);
        svg.appendChild(group);
      }

      const path = group.querySelector('path');
      const foreignObject = group.querySelector('foreignObject');
      const labelDiv = foreignObject.querySelector('div');

      path.setAttribute('d', edge.pathData);
      path.classList.toggle('highlighted', state.highlightedEdge === edge.id);
      // Update marker for existing path as well
      let markerId = 'arrowhead';
      if (edge.arrowDirection === 'up') markerId = 'arrowhead-up';
      else if (edge.arrowDirection === 'down') markerId = 'arrowhead-down';
      else if (edge.arrowDirection === 'left') markerId = 'arrowhead-left';
      path.setAttribute('marker-end', `url(#${markerId})`);

      labelDiv.textContent = edge.label;
      labelDiv.classList.toggle('highlighted', state.highlightedEdge === edge.id);

      requestAnimationFrame(() => {
        // Use max-content for label width
        labelDiv.style.width = 'max-content';
        const measuredWidth = labelDiv.getBoundingClientRect().width;
        foreignObject.setAttribute('width', measuredWidth);
        foreignObject.setAttribute('height', '32');
        // Center the label at the control point
        let labelY = edge.labelPosition.y - 16;
        if (edge.isBidirectional && typeof edge.fromId !== 'undefined' && typeof edge.toId !== 'undefined') {
          labelY += (edge.fromId < edge.toId ? -18 : 18);
        }
        foreignObject.setAttribute('x', edge.labelPosition.x - measuredWidth / 2);
        foreignObject.setAttribute('y', labelY);
      });
    });

    // Apply transform to graph
    graph.style.transform = `translate(${state.panX}px, ${state.panY}px) scale(${state.scale})`;

    // Apply transforms to background pattern if it exists
    const workflowGraph = modal.querySelector('#workflow-graph');
    if (workflowGraph) {
      // Create a dynamic radial gradient where both dot size and spacing scale with zoom
      const dotSize = Math.max(0.5, Math.min(1, state.scale)) * 1; // Dot size scales but has limits
      const spacing = 15 * state.scale; // Grid spacing scales with zoom
      workflowGraph.style.backgroundImage = `radial-gradient(circle at 1px 1px, var(--wf-dot-color) ${dotSize}px, transparent ${dotSize}px)`;
      workflowGraph.style.backgroundSize = `${spacing}px ${spacing}px`;
      workflowGraph.style.backgroundPosition = `${state.panX}px ${state.panY}px`;
    }
  }

  function handleNodeDrag(startEvent, draggedStage) {
    if (draggedStage.id === 'From Any') return;
    const stageElement = document.getElementById(`stage-${draggedStage.id}`);
    state.isDraggingStage = true;
    const dragStart = { x: startEvent.clientX, y: startEvent.clientY, stageX: draggedStage.position.x, stageY: draggedStage.position.y };
    stageElement.classList.add('dragging');

    const onMouseMove = moveEvent => {
      const newX = dragStart.stageX + (moveEvent.clientX - dragStart.x) / state.scale;
      const newY = dragStart.stageY + (moveEvent.clientY - dragStart.y) / state.scale;
      const stageToUpdate = state.stages.find(s => s.id === draggedStage.id);
      if (stageToUpdate) {
        stageToUpdate.position.x = newX;
        stageToUpdate.position.y = newY;
      }
      renderGraph(document.querySelector('#workflow-graph'));
    };

    const onMouseUp = () => {
      document.removeEventListener('mousemove', onMouseMove);
      document.removeEventListener('mouseup', onMouseUp);
      stageElement.classList.remove('dragging');
      state.isDraggingStage = false;
    };
    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
  }

  async function init(modal) {
    const container = modal.querySelector('#workflow-graph');

    if (!container || container.dataset.initialized) {
      return;
    }
    container.dataset.initialized = 'true';

    const workflowContainer = container.querySelector('#workflow-container');

    const workflowId = parseInt(workflowContainer.dataset.workflowId, 10);

    if (!workflowId) return showMessageInModal('COM_WORKFLOW_GRAPH_ERROR_INVALID_ID', 'error');

    const graph = modal.querySelector('#graph');
    const svg = modal.querySelector('#connections');

    // Vue Flow style arrowhead markers
    svg.innerHTML = `<defs>
    <marker id="arrowhead" viewBox="-10 -10 20 20" refX="0" refY="0" 
      markerWidth="10" markerHeight="10" markerUnits="strokeWidth" orient="auto-start-reverse">
      <polyline points="-5,-4 0,0 -5,4" stroke="#2071c6" stroke-width="1" 
        stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    </marker>
    <marker id="arrowhead-up" viewBox="-10 -10 20 20" refX="0" refY="0" 
      markerWidth="10" markerHeight="10" markerUnits="strokeWidth" orient="auto-start-reverse">
      <polyline points="-4,-5 0,0 4,-5" stroke="#2071c6" stroke-width="1" 
        stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    </marker>
    <marker id="arrowhead-down" viewBox="-10 -10 20 20" refX="0" refY="0" 
      markerWidth="10" markerHeight="10" markerUnits="strokeWidth" orient="auto-start-reverse">
      <polyline points="-4,5 0,0 4,5" stroke="#2071c6" stroke-width="1" 
        stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    </marker>
    <marker id="arrowhead-left" viewBox="-10 -10 20 20" refX="0" refY="0" 
      markerWidth="10" markerHeight="10" markerUnits="strokeWidth" orient="auto-start-reverse">
      <polyline points="-5,-4 0,0 -5,4" stroke="#2071c6" stroke-width="1" 
        stroke-linecap="round" stroke-linejoin="round" fill="none"/>
    </marker>
  </defs>`;

    try {
      const workflowData = await makeRequest(`&task=graph.getWorkflow&workflow_id=${workflowId}&format=json`);
      if (!workflowData) return;
      const stagesData = await makeRequest(`&task=graph.getStages&workflow_id=${workflowId}&format=json`);
      if (!stagesData) return;
      const transitionsData = await makeRequest(`&task=graph.getTransitions&workflow_id=${workflowId}&format=json`);
      if (!transitionsData) return;

      state.workflow = workflowData?.data || {};
      let stages = stagesData?.data || [];
      let transitions = transitionsData?.data || [];

      if (!stages.length) {
        showMessageInModal('COM_WORKFLOW_GRAPH_ERROR_STAGES_NOT_FOUND', 'error');
        return;
      }

      const hasStart = transitions.some(tr => tr.from_stage_id === -1);
      // Only add 'From Any' once
      if (hasStart && !stages.some(s => s.id === 'From Any')) {
        stages.unshift({ id: 'From Any', title: 'From Any', position: null });
      }

      state.stages = stages.map(s => ({ ...s, position: s.position || { x: NaN, y: NaN } }));
      state.transitions = transitions;
      state.stages = calculateAutoLayout(state.stages, state.transitions);

      modal.querySelector('#workflow-main-title').textContent = state.workflow.title || translate('COM_WORKFLOW_GRAPH_WORKFLOW');
      const statusBadge = modal.querySelector('.badge[role="status"]');
      if (statusBadge) {
        const isPublished = state.workflow.published == 1;
        statusBadge.className = `badge ${isPublished ? 'bg-success' : 'bg-warning'}`;
        statusBadge.textContent = isPublished ? translate('COM_WORKFLOW_GRAPH_ENABLED') : translate('COM_WORKFLOW_GRAPH_DISABLED');
      }
      const stageCount = modal.querySelector('#workflow-stage-count');
      if (stageCount) {
        const realStagesCount = state.stages.filter(s => s.id !== 'From Any').length;
        stageCount.textContent = `${realStagesCount} ${realStagesCount === 1 ? translate('COM_WORKFLOW_GRAPH_STAGE') : translate('COM_WORKFLOW_GRAPH_STAGES')}`;
      }
      const transitionCount = modal.querySelector('#workflow-transition-count');
      if (transitionCount) {
        transitionCount.textContent = `${state.transitions.length} ${state.transitions.length === 1 ? translate('COM_WORKFLOW_GRAPH_TRANSITION') : translate('COM_WORKFLOW_GRAPH_TRANSITIONS')}`;
      }

      renderGraph(modal);
      setTimeout(() => fitToScreen(modal), 150);

    } catch (error) {
      showMessageInModal(error.message, 'error');
      return;
    }

    let isPanning = false, panStart = {};
    container.addEventListener("mousedown", e => {
      if (e.target.closest('.stage') || e.target.closest('.zoom-controls') || e.button !== 0) return;
      isPanning = true;
      panStart = { x: e.clientX - state.panX, y: e.clientY - state.panY };
      graph.classList.add('dragging');
    });

    document.addEventListener("mousemove", e => {
      if (!isPanning) return;
      state.panX = e.clientX - panStart.x;
      state.panY = e.clientY - panStart.y;
      renderGraph(modal);
    });

    const stopPanning = () => { isPanning = false; graph.classList.remove('dragging'); };
    document.addEventListener("mouseup", stopPanning);
    container.addEventListener("mouseleave", stopPanning);

    container.addEventListener("wheel", e => {
      e.preventDefault();
      const rect = container.getBoundingClientRect();
      const mouseX = e.clientX - rect.left;
      const mouseY = e.clientY - rect.top;
      const oldScale = state.scale;
      const zoomDirection = e.deltaY < 0 ? 1 : -1;
      state.scale = Math.max(MIN_ZOOM, Math.min(state.scale * (1 + zoomDirection * ZOOM_SENSITIVITY), MAX_ZOOM));
      const factor = state.scale / oldScale;
      state.panX = mouseX - (mouseX - state.panX) * factor;
      state.panY = mouseY - (mouseY - state.panY) * factor;
      renderGraph(modal);
    });

    const zoomControls = container.querySelector('.zoom-controls');
    zoomControls.querySelector('.zoom-in').addEventListener('click', () => applyZoom(1.2, modal));
    zoomControls.querySelector('.zoom-out').addEventListener('click', () => applyZoom(1 / 1.2, modal));
    zoomControls.querySelector('.fit-screen').addEventListener('click', () => fitToScreen(modal));

    function applyZoom(factor, modalContext) {
      const rect = modalContext.querySelector('#workflow-graph').getBoundingClientRect();
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;
      const oldScale = state.scale;
      state.scale = Math.max(MIN_ZOOM, Math.min(state.scale * factor, MAX_ZOOM));
      const scaleRatio = state.scale / oldScale;
      state.panX = centerX - (centerX - state.panX) * scaleRatio;
      state.panY = centerY - (centerY - state.panY) * scaleRatio;
      renderGraph(modalContext);
    }

    function getBoundingBox() {
      if (!state.stages.length) return null;
      let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
      state.stages.forEach(stage => {
        if (stage.position) {
          minX = Math.min(minX, stage.position.x);
          minY = Math.min(minY, stage.position.y);
          maxX = Math.max(maxX, stage.position.x + STAGE_WIDTH);
          maxY = Math.max(maxY, stage.position.y + STAGE_HEIGHT);
        }
      });
      if (minX === Infinity) return null;
      return { minX, minY, maxX, maxY, width: maxX - minX, height: maxY - minY };
    }

    function fitToScreen(modalContext) {
      const bounds = getBoundingBox();
      if (!bounds) return;
      const containerRect = modalContext.querySelector('#workflow-graph').getBoundingClientRect();
      const padding = 50;
      const scaleX = (containerRect.width - padding) / bounds.width;
      const scaleY = (containerRect.height - padding) / bounds.height;
      state.scale = Math.max(MIN_ZOOM, Math.min(scaleX, scaleY, MAX_ZOOM));
      state.panX = (containerRect.width - bounds.width * state.scale) / 2 - bounds.minX * state.scale;
      state.panY = (containerRect.height - bounds.height * state.scale) / 2 - bounds.minY * state.scale;
      renderGraph(modalContext);
    }
  }

  // Listen for dialog open events
  document.addEventListener('joomla-dialog:open', (event) => {
    const dialog = event.target;
    const workflowContainer = dialog.querySelector('#workflow-container');

    if (workflowContainer) {
      init(dialog);
    }
  });

})();