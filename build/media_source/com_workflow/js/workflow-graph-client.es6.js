/**
 * @copyright (C) 2026 Open Source Matters
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
  const CORNER_RADIUS = 10;

  // This central state object holds all data needed for rendering.
  const state = {
    workflow: null,
    stages: [],
    transitions: [],
    scale: 1,
    panX: 0,
    panY: 0,
    isDraggingStage: false,
    highlightedEdge: null
  };

  // --- Translation ---
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
        if (Number.isNaN(val)) val = 0;
      }
      i += 1;
      return val;
    });
  };

  // Get full url for marker to avoid issues with base tags
  const getMarkerUrl = (id) => {
      const location = window.location.href.split('#')[0];
      return `url(${location}#${id})`;
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
      const baseUri = `${paths ? `${paths.baseFull}index.php` : window.location.pathname}`;
      const uri = `${baseUri}?option=com_workflow&extension=com_content&layout=modal&view=graph${url}`;
      const response = await fetch(uri, { credentials: 'same-origin' });
      if (!response.ok) {
        let message = 'COM_WORKFLOW_GRAPH_ERROR_UNKNOWN';
        if (response.status === 401) message = 'COM_WORKFLOW_GRAPH_ERROR_NOT_AUTHENTICATED';
        else if (response.status >= 403) message = 'COM_WORKFLOW_GRAPH_ERROR_NO_PERMISSION';
        else if (response.status != 200) message = sprintf('COM_WORKFLOW_GRAPH_ERROR_REQUEST_FAILED', response.status);
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

  // --- Initial Layout Creation ---
  function calculateAutoLayout(stages) {
    const withNoPosition = stages.filter(stage => !stage.position || isNaN(stage.position.x) || isNaN(stage.position.y));
    if (withNoPosition.length === 0) return stages;

    const fromAnyStage = stages.find(s => s.id === 'from_any');
    const transitionStages = stages.filter(s => s.id !== 'from_any');

    const gapX = 400;
    const gapY = 300;
    const paddingX = 100;
    const paddingY = 100;
    const columns = Math.min(4, Math.ceil(Math.sqrt(transitionStages.length) + 1));

    transitionStages.forEach((stage, index) => {
      if (withNoPosition.some(s => s.id === stage.id)) {
        const col = index % columns;
        const row = Math.floor(index / columns);
        stage.position = {
          x: col * gapX + paddingX,
          y: row * gapY + paddingY
        };
      }
    });

    if (fromAnyStage && withNoPosition.some(s => s.id === fromAnyStage.id)) {
      if (!fromAnyStage.position || isNaN(fromAnyStage.position.x) || isNaN(fromAnyStage.position.y)) {
        fromAnyStage.position = { x: 600, y: -200 };
      }
    }
    return stages;
  }

  function buildPathFromPoints(points) {
    let path = `M ${points[0].x} ${points[0].y  - 50}`;
    for (let i = 1; i < points.length - 1; i++) {
        const prev = points[i-1];
        const curr = points[i];
        const next = points[i+1];

        const dx1 = prev.x - curr.x;
        const dy1 = prev.y - curr.y;
        const len1 = Math.sqrt(dx1*dx1 + dy1*dy1);

        const dx2 = next.x - curr.x;
        const dy2 = next.y - curr.y;
        const len2 = Math.sqrt(dx2*dx2 + dy2*dy2);

        if (len1 < 1 || len2 < 1) {
             path += ` L ${curr.x} ${curr.y}`;
             continue;
        }

        const r = Math.min(CORNER_RADIUS, len1/2, len2/2);

        const startX = curr.x + (dx1 / len1) * r;
        const startY = curr.y + (dy1 / len1) * r;

        const endX = curr.x + (dx2 / len2) * r;
        const endY = curr.y + (dy2 / len2) * r;

        path += ` L ${startX} ${startY}`;
        path += ` Q ${curr.x} ${curr.y} ${endX} ${endY}`;
    }
    path += ` L ${points[points.length-1].x} ${points[points.length-1].y}`;
    return path;
  }

  function getVerticalStepPath(sourceX, sourceY, targetX, targetY, midY) {
    const points = [
      { x: sourceX, y: sourceY },
      { x: sourceX, y: midY },
      { x: targetX, y: midY },
      { x: targetX, y: targetY }
    ];
    return [buildPathFromPoints(points), (sourceX + targetX) / 2, midY];
  }

  function getHorizontalStepPath(sourceX, sourceY, targetX, targetY, midX) {
    const startStubY = sourceY + 30;
    const endStubY = targetY - 30;

    const points = [
      { x: sourceX, y: sourceY },
      { x: sourceX, y: startStubY },
      { x: midX, y: startStubY },
      { x: midX, y: endStubY },
      { x: targetX, y: endStubY },
      { x: targetX, y: targetY }
    ];
    return [buildPathFromPoints(points), midX, (startStubY + endStubY) / 2];
  }

  function generateEdges(transitions, stages) {
    const stageMap = new Map(stages.map(s => [s.id, s]));
    const edgeGroups = {};

    // Undirected Grouping to prevent overlaps on bi-directional edges
    transitions.forEach(tr => {
      const fromId = tr.from_stage_id === -1 ? 'from_any' : tr.from_stage_id;
      const toId = tr.to_stage_id;
      const s1 = String(fromId);
      const s2 = String(toId);
      const key = s1 < s2 ? `${s1}|${s2}` : `${s2}|${s1}`;

      if (!edgeGroups[key]) edgeGroups[key] = [];
      edgeGroups[key].push(tr);
    });

    Object.values(edgeGroups).forEach(group => group.sort((a, b) => a.id - b.id));

    return transitions.flatMap(tr => {
      const fromId = tr.from_stage_id === -1 ? 'from_any' : tr.from_stage_id;
      const toId = tr.to_stage_id;
      const fromStage = stageMap.get(fromId);
      const toStage = stageMap.get(toId);

      if (!(fromStage != null && fromStage.position) || !(toStage != null && toStage.position)) return [];

      const sourceX = fromStage.position.x + STAGE_WIDTH / 2;
      const sourceY = fromStage.position.y + STAGE_HEIGHT;
      const targetX = toStage.position.x + STAGE_WIDTH / 2;
      const targetY = toStage.position.y;

      const s1 = String(fromId);
      const s2 = String(toId);
      const groupKey = s1 < s2 ? `${s1}|${s2}` : `${s2}|${s1}`;
      const group = edgeGroups[groupKey] || [tr];
      const transitionIndex = group.findIndex(t => t.id === tr.id);

      let offsetIndex = transitionIndex - (group.length - 1) / 2;
      const bundleSpacing = 40;

      let pathData, labelX, labelY;

      // Obstruction Check
      let isVerticalObstructed = false;
      const distX = Math.abs(sourceX - targetX);
      const isVerticallyAligned = distX < STAGE_WIDTH;

      if (isVerticallyAligned && targetY > sourceY) {
         isVerticalObstructed = stages.some(stage => {
            if (stage.id === fromId || stage.id === toId) return false;
            const sTop = stage.position.y;
            const sBottom = stage.position.y + STAGE_HEIGHT;
            const isBetweenY = (sTop > sourceY && sBottom < targetY);
            const sLeft = stage.position.x;
            const sRight = stage.position.x + STAGE_WIDTH;
            const pathX = sourceX;
            const isBlockingX = (pathX > sLeft - 20 && pathX < sRight + 20);
            return isBetweenY && isBlockingX;
         });
      }

      const isStacked = (targetY > (sourceY + 50)) && !isVerticalObstructed && distX > 40;

      if (isStacked) {
          let midY = (sourceY + targetY) / 2;
          midY += offsetIndex * bundleSpacing;
          [pathData, labelX, labelY] = getVerticalStepPath(sourceX, sourceY, targetX, targetY, midY);
          labelX += offsetIndex * 60; // Stagger X
      } else {
          let midX = (sourceX + targetX) / 2;
          if (distX < STAGE_WIDTH || isVerticalObstructed) {
             midX = Math.max(sourceX, targetX) + STAGE_WIDTH / 2 + 60;
          }
          midX += offsetIndex * bundleSpacing;
          [pathData, labelX, labelY] = getHorizontalStepPath(sourceX, sourceY, targetX, targetY, midX);
          labelY += offsetIndex * 35; // Stagger Y
      }

      return {
        id: `transition-${tr.id}`,
        pathData,
        label: tr.title,
        labelPosition: { x: labelX, y: labelY },
        fromId,
        toId
      };
    }).filter(Boolean);
  }

  function renderGraph(modal) {
    const graph = modal.querySelector('#graph');
    const stageContainer = modal.querySelector('#stages');
    const svg = modal.querySelector('#connections');
    if (!graph || !stageContainer || !svg) return;

    // Render Stages
    stageContainer.querySelectorAll('[id^="stage-"]').forEach(el => el.remove());
    state.stages.forEach(stage => {
      let stageEl = document.createElement('div');
      stageEl.id = `stage-${stage.id}`;
      stageEl.addEventListener('mousedown', e => { if (e.button === 0) handleNodeDrag(e, stage); });
      const isVirtual = stage.id === 'from_any';
      stageEl.className = `stage ${stage.default ? 'default' : ''} ${isVirtual ? 'virtual' : ''}`;
      stageEl.style.left = `${stage.position.x}px`;
      stageEl.style.top = `${stage.position.y}px`;

      let newHTML = isVirtual
        ? `<div class="stage-title text-truncate">${stage.title}</div>
           <div class="d-flex justify-content-between align-items-center mt-2"><div class="badge bg-info rounded-pill p-1"></div></div>`
        : `<div class="stage-title text-truncate" title="${stage.title}">${stage.title}</div>
           <div class="d-flex align-items-center gap-2">
             ${stage.description ? `<div class="stage-description text-truncate small">${stage.description}</div>` : ''}
           </div>
           <div class="d-flex justify-content-between align-items-center mt-2">
             ${typeof stage.published !== 'undefined' ? `<div class="badge ${stage.published == '1' ? 'bg-success' : 'bg-danger'} rounded-pill p-1">${stage.published == '1' ? translate('COM_WORKFLOW_GRAPH_ENABLED') : translate('COM_WORKFLOW_GRAPH_DISABLED')}</div>` : ''}
             ${stage.default ? `<div class="badge bg-warning rounded-pill p-1">${translate('COM_WORKFLOW_GRAPH_DEFAULT')}</div>` : ''}
           </div>`;

      stageEl.innerHTML = newHTML;
      stageContainer.appendChild(stageEl);
    });

    // --- Setting up path SVG layers ---
    let pathsLayer = svg.querySelector('g.layers-paths');
    let labelsLayer = svg.querySelector('g.layers-labels');

    if (!pathsLayer) {
        pathsLayer = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        pathsLayer.classList.add('layers-paths');
        svg.appendChild(pathsLayer);
    }
    if (!labelsLayer) {
        labelsLayer = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        labelsLayer.classList.add('layers-labels');
        svg.appendChild(labelsLayer);
    } else {
        svg.appendChild(labelsLayer); // Ensure it is last (top)
    }

    const edges = generateEdges(state.transitions, state.stages);

    // Cleanup orphans
    pathsLayer.querySelectorAll('path[data-edge-id]').forEach(el => {
         if (!edges.find(e => e.id === el.dataset.edgeId)) el.remove();
    });
    labelsLayer.querySelectorAll('foreignObject[data-edge-id]').forEach(el => {
         if (!edges.find(e => e.id === el.dataset.edgeId)) el.remove();
    });

    edges.forEach(edge => {
      let path = pathsLayer.querySelector(`path[data-edge-id="${edge.id}"]`);
      if (!path) {
        path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        path.dataset.edgeId = edge.id;
        path.setAttribute('class', 'transition-path');
        pathsLayer.appendChild(path);
      }
      path.setAttribute('d', edge.pathData);
      path.classList.toggle('highlighted', state.highlightedEdge === edge.id);
      path.setAttribute('marker-end', getMarkerUrl('arrowhead'));

      let foreignObject = labelsLayer.querySelector(`foreignObject[data-edge-id="${edge.id}"]`);
      let labelDiv;

      if (!foreignObject) {
        foreignObject = document.createElementNS('http://www.w3.org/2000/svg', 'foreignObject');
        foreignObject.dataset.edgeId = edge.id;
        foreignObject.style.overflow = 'visible';

        labelDiv = document.createElement('div');
        labelDiv.className = 'transition-label-content';
        labelDiv.addEventListener('click', (e) => {
          e.stopPropagation();
          state.highlightedEdge = state.highlightedEdge === edge.id ? null : edge.id;
          renderGraph(modal);
        });
        foreignObject.appendChild(labelDiv);
        labelsLayer.appendChild(foreignObject);
      } else {
        labelDiv = foreignObject.querySelector('div');
      }

      labelDiv.textContent = edge.label;
      labelDiv.classList.toggle('highlighted', state.highlightedEdge === edge.id);
      graph.style.transform = `translate(${state.panX}px, ${state.panY}px) scale(${state.scale})`;

      requestAnimationFrame(() => {
        labelDiv.style.width = 'max-content';
        const rect = labelDiv.getBoundingClientRect();
        if (rect.width === 0 && rect.height === 0) return;
        const measuredWidth = rect.width / state.scale;
        const measuredHeight = (rect.height / state.scale) || 24;

        foreignObject.setAttribute('width', measuredWidth + 4);
        foreignObject.setAttribute('height', measuredHeight + 4);

        foreignObject.setAttribute('x', edge.labelPosition.x - (measuredWidth / 2));
        foreignObject.setAttribute('y', edge.labelPosition.y - (measuredHeight / 2));
      });
    });


    // Grid Background
    const workflowGraph = modal.querySelector('#workflow-graph');
    if (workflowGraph) {
      const dotSize = Math.max(0.3, Math.min(1, state.scale));
      const spacing = 20 * state.scale;
      workflowGraph.style.backgroundImage = `
        radial-gradient(circle,
          color-mix(in srgb, var(--body-color) 80%, transparent) 0px,
          color-mix(in srgb, var(--body-color) 60%, transparent) ${dotSize}px,
          transparent ${dotSize * 2}px
        )
      `;
      workflowGraph.style.backgroundSize = `${spacing}px ${spacing}px`;
      workflowGraph.style.backgroundPosition = `${state.panX}px ${state.panY}px`;
    }
  }

  function handleNodeDrag(startEvent, draggedStage) {
    if (draggedStage.id === 'from_any') return;
    const stageElement = document.getElementById(`stage-${draggedStage.id}`);
    state.isDraggingStage = true;
    const dragStart = {
      x: startEvent.clientX,
      y: startEvent.clientY,
      stageX: draggedStage.position.x,
      stageY: draggedStage.position.y
    };
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
    const graph = modal.querySelector('#graph');
    if (!container || container.dataset.initialized) return;
    container.dataset.initialized = 'true';

    const workflowContainer = container.querySelector('#workflow-container');
    const workflowId = parseInt(workflowContainer.dataset.workflowId, 10);
    if (!workflowId) return showMessageInModal('COM_WORKFLOW_GRAPH_ERROR_INVALID_ID', 'error');

    // Standard Arrowhead definition (points right, orient=auto handles the rest)
    modal.querySelector('#connections').innerHTML = `<defs>
      <marker id="arrowhead" viewBox="0 0 10 10" refX="10" refY="5"
        markerWidth="6" markerHeight="6" orient="auto">
        <path d="M 0 0 L 10 5 L 0 10 z" fill="#2071c6" />
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
      state.transitions = transitionsData?.data || [];

      if (!stages.length) return showMessageInModal('COM_WORKFLOW_GRAPH_ERROR_STAGES_NOT_FOUND', 'error');

      if (state.transitions.some(tr => tr.from_stage_id === -1) && !stages.some(s => s.id === 'from_any')) {
        stages.unshift({ id: 'from_any', title: translate('COM_WORKFLOW_GRAPH_FROM_ANY'), position: null });
      }
      state.stages = stages.map(s => ({ ...s, position: s.position || { x: NaN, y: NaN } }));
      state.stages = calculateAutoLayout(state.stages);

      // Update UI Counts
      modal.querySelector('.joomla-dialog-header h3').textContent = state.workflow.title || translate('COM_WORKFLOW_GRAPH_WORKFLOW');
      const statusBadge = modal.querySelector('#workflow-status-badge');
      if (statusBadge) {
        statusBadge.textContent = state.workflow.published == '1' ? translate('COM_WORKFLOW_GRAPH_ENABLED') : translate('COM_WORKFLOW_GRAPH_DISABLED');
        statusBadge.classList.add(state.workflow.published == '1' ? 'bg-success' : 'bg-warning');
      }
      const realStagesCount = state.stages.filter(s => s.id !== 'from_any').length;
      const stageCount = modal.querySelector('#workflow-stage-count');
      if (stageCount) stageCount.textContent = `${realStagesCount} ${realStagesCount === 1 ? translate('COM_WORKFLOW_GRAPH_STAGE') : translate('COM_WORKFLOW_GRAPH_STAGES')}`;

      const transitionCount = modal.querySelector('#workflow-transition-count');
      if (transitionCount) transitionCount.textContent = `${state.transitions.length} ${state.transitions.length === 1 ? translate('COM_WORKFLOW_GRAPH_TRANSITION') : translate('COM_WORKFLOW_GRAPH_TRANSITIONS')}`;
      renderGraph(modal);
      setTimeout(() => fitToScreen(modal), 150);
    } catch (error) {
      showMessageInModal(error.message, 'error');
      return;
    }

    // Zoom & Pan Logic
    let isPanning = false, panStart = {};
    container.addEventListener("mousedown", e => {
      if (e.target.closest('.stage') || e.target.closest('.zoom-controls') || e.button !== 0) return;
      isPanning = true;
      panStart = { x: e.clientX - state.panX, y: e.clientY - state.panY };
      if (graph) graph.classList.add('dragging');
    });
    document.addEventListener("mousemove", e => {
      if (!isPanning) return;
      state.panX = e.clientX - panStart.x;
      state.panY = e.clientY - panStart.y;
      renderGraph(modal);
    });
    const stopPanning = () => {
      isPanning = false;
      if (graph) graph.classList.remove('dragging');
    };
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
    function fitToScreen(modalContext) {
      if (!state.stages.length) return;
      let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
      state.stages.forEach(s => {
        if (s.position) {
          minX = Math.min(minX, s.position.x);
          minY = Math.min(minY, s.position.y);
          maxX = Math.max(maxX, s.position.x + STAGE_WIDTH);
          maxY = Math.max(maxY, s.position.y + STAGE_HEIGHT);
        }
      });
      if (minX === Infinity) return;
      const containerRect = modalContext.querySelector('#workflow-graph').getBoundingClientRect();
      const padding = 50;
      const w = maxX - minX;
      const h = maxY - minY;
      state.scale = Math.max(MIN_ZOOM, Math.min((containerRect.width - padding) / w, (containerRect.height - padding) / h, MAX_ZOOM));
      state.panX = (containerRect.width - w * state.scale) / 2 - minX * state.scale;
      state.panY = (containerRect.height - h * state.scale) / 2 - minY * state.scale;
      renderGraph(modalContext);
    }

    // --- Keyboard Shortcuts ---
  document.addEventListener('keydown', (e) => {
    if (['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) return;

    const PAN_STEP = 30; // Pixels to move
    const ZOOM_STEP = 1.1; // Multiplier

    switch (e.code) {
      /* ---------- Zoom ---------- */
      case 'Equal':   // + / =
        applyZoom(ZOOM_STEP, modal);
        break;

      case 'Minus':   // - / _
        applyZoom(1 / ZOOM_STEP, modal);
        break;

      /* ---------- Panning ---------- */
      case 'ArrowLeft':
      case 'KeyA':
        state.panX += PAN_STEP;
        renderGraph(modal);
        break;

      case 'ArrowRight':
      case 'KeyD':
        state.panX -= PAN_STEP;
        renderGraph(modal);
        break;

      case 'ArrowUp':
      case 'KeyW':
        state.panY += PAN_STEP;
        renderGraph(modal);
        break;

      case 'ArrowDown':
      case 'KeyS':
        state.panY -= PAN_STEP;
        renderGraph(modal);
        break;

      /* ---------- Reset / Fit ---------- */
      case 'Digit0':
      case 'KeyF':
        fitToScreen(modal);
        break;

      default:
        break;
    }
  });
  }

  document.addEventListener('joomla-dialog:open', event => {
    const dialog = event.target;
    if (dialog.querySelector('#workflow-container')) init(dialog);
  });
})();
