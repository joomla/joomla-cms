/**
 * @copyright (C) 2025 Open Source Matters
 * @license  GNU GPL v2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};
(() => {
  async function makeRequest(url) {
    try {
      const paths = Joomla.getOptions('system.paths');
      const uri = `${paths ? `${paths.rootFull}administrator/index.php` : window.location.pathname
        }?option=com_workflow&extension=com_content&layout=modal&view=graph${url}`;

      const response = await fetch(uri, { credentials: 'same-origin' });

      if (!response.ok) {
        // Normalize message based on status
        let message = 'An unexpected error occurred.';
        if (response.status === 401) {
          message = 'Not authenticated.';
        } else if (response.status === 403 || response.status === 404) {
          message = 'You do not have permission to access the workflows.';
        } else {
          message = `Request failed with status ${response.status}`;
        }
        throw new Error(message);
      }

      return await response.json();

    } catch (err) {
      showErrorInModal(err.message);
      throw err;
    }
  }

  function showErrorInModal(errorMessage) {
    const container = document.getElementById("workflow-container");
    const stageContainer = document.getElementById("stages");

    if (container) {
      // Clear the main container and show error
      container.innerHTML = `
        <div class="alert alert-danger" role="alert">
          <h4 class="alert-heading">Error Loading Workflow</h4>
          <p class="mb-0">${errorMessage}</p>
        </div>
      `;
    } else if (stageContainer) {
      // Fallback: show in stages container
      stageContainer.innerHTML = `
        <div class="alert alert-danger" role="alert">
          <h4 class="alert-heading">Error Loading Workflow</h4>
          <p class="mb-0">${errorMessage}</p>
        </div>
      `;
    }
  }


  async function getWorkflow(id) {
    return makeRequest(`&task=graph.getWorkflow&workflow_id=${id}&format=json`);
  }
  async function getStages(workflowId) {
    return makeRequest(`&task=graph.getStages&workflow_id=${workflowId}&format=json`);
  }
  async function getTransitions(workflowId) {
    return makeRequest(`&task=graph.getTransitions&workflow_id=${workflowId}&format=json`);
  }
  function filterWorkflow(stages, transitions) {
    let filteredTransitions = transitions.filter(tr => {
      return tr.permissions == null ? void 0 : tr.permissions.run_transition;
    });
    const connectedStageIds = new Set();
    filteredTransitions.forEach(tr => {
      if (tr.from_stage_id !== -1) connectedStageIds.add(tr.from_stage_id);
      connectedStageIds.add(tr.to_stage_id);
    });
    let filteredStages = stages.filter(st => {
      const editable = (st.permissions == null ? void 0 : st.permissions.edit) || (st.permissions == null ? void 0 : st.permissions.delete);
      const connected = connectedStageIds.has(st.id);
      return editable || connected;
    });
    const validStageIds = new Set(filteredStages.map(st => st.id));
    filteredTransitions = filteredTransitions.filter(tr => (tr.from_stage_id === -1 || validStageIds.has(tr.from_stage_id)) && validStageIds.has(tr.to_stage_id));
    return {
      stages: filteredStages,
      transitions: filteredTransitions
    };
  }
  function calculateAutoLayout(stages, transitions) {
    const needsPosition = stages.filter(stage => !stage.position || isNaN(stage.position.x) || isNaN(stage.position.y));
    if (needsPosition.length === 0) return stages;
    const fromAnyStage = stages.find(s => s.id === 'From Any');
    if (fromAnyStage && (!fromAnyStage.position || isNaN(fromAnyStage.position.x) || isNaN(fromAnyStage.position.y))) {
      fromAnyStage.position = {
        x: 600,
        y: -200
      };
    }
    const outgoing = new Map(stages.map(s => [s.id, []]));
    const inDegree = new Map(stages.map(s => [s.id, 0]));
    transitions.forEach(tr => {
      const fromId = tr.from_stage_id === -1 ? 'From Any' : tr.from_stage_id;
      const toId = tr.to_stage_id;
      if (outgoing.has(fromId) && inDegree.has(toId)) {
        outgoing.get(fromId).push(toId);
        inDegree.set(toId, inDegree.get(toId) + 1);
      }
    });
    const levels = [];
    let queue = stages.filter(s => inDegree.get(s.id) === 0 && s.id !== 'From Any');
    while (queue.length > 0) {
      levels.push(queue);
      const nextQueue = [];
      for (const stage of queue) {
        for (const targetId of outgoing.get(stage.id) || []) {
          inDegree.set(targetId, inDegree.get(targetId) - 1);
          if (inDegree.get(targetId) === 0 && targetId !== 'From Any') {
            nextQueue.push(stages.find(s => s.id === targetId));
          }
        }
      }
      queue = nextQueue;
    }
    const levelSpacing = 300;
    const stageSpacing = 120;
    levels.forEach((levelStages, level) => {
      const levelHeight = (levelStages.length - 1) * stageSpacing;
      const startY = -levelHeight / 2;
      levelStages.forEach((stage, index) => {
        if (needsPosition.some(s => s.id === stage.id)) {
          stage.position = {
            x: level * levelSpacing + 50,
            y: startY + index * stageSpacing + 300
          };
        }
      });
    });
    return stages;
  }
  function generateNodes(stages, transitions) {
    // Ensure every stage has a position object
    stages.forEach(stage => {
      if (!stage.position || isNaN(stage.position.x) || isNaN(stage.position.y)) {
        stage.position = { x: 0, y: 0 };
      } else {
        stage.position.x = parseFloat(stage.position.x);
        stage.position.y = parseFloat(stage.position.y);
      }
    });
    const hasStart = transitions.some(tr => tr.from_stage_id === -1);
    if (hasStart && !stages.find(s => s.id === 'From Any')) stages.unshift({
      id: 'From Any',
      title: 'From Any',
      position: { x: 600, y: -200 }
    });
    const positionedStages = calculateAutoLayout(stages, transitions);
    return positionedStages.map(stage => {
      const isVirtual = stage.id === 'From Any';
      return {
        id: `stage-${stage.id}`,
        position: stage.position,
        data: stage,
        className: `stage ${stage.default ? 'default' : ''} ${isVirtual ? 'virtual' : ''}`,
        innerHTML: `
            <div class="stage-title text-truncate" style="max-width: 180px;" title="${stage.title}">${stage.title}</div>
            ${stage.description ? `<div class="stage-description text-truncate small text-white" style="max-width: 180px;" title="${stage.description}">${stage.description}</div>` : ''}
            <div style="display: flex; gap: 4px; align-items: center; margin-top: 2px;">
          ${stage.default ? '<div class="badge bg-warning bg-opacity-10 rounded-pill p-1">DEFAULT</div>' : ''}
          ${typeof stage.published !== 'undefined' ? `<div class="badge ${stage.published == 1 ? 'bg-success' : 'bg-warning'} rounded-pill p-1">${stage.published == 1 ? 'ENABLED' : 'DISABLED'}</div>` : ''}
            </div>
          `
      };
    });
  }

  /**
   * Generates edge objects with robust pathing and centered labels.
   */
  function generateEdges(transitions, stages) {
    const STAGE_WIDTH = 200;
    const STAGE_HEIGHT = 100;
    const getConnectionPoint = (fromStage, toStage, isSource) => {
      const node = isSource ? fromStage : toStage;
      const center = {
        x: node.position.x + STAGE_WIDTH / 2,
        y: node.position.y + STAGE_HEIGHT / 2
      };
      const otherCenter = {
        x: (isSource ? toStage : fromStage).position.x + STAGE_WIDTH / 2,
        y: (isSource ? toStage : fromStage).position.y + STAGE_HEIGHT / 2
      };
      const dx = otherCenter.x - center.x;
      const dy = otherCenter.y - center.y;
      if (Math.abs(dx) > Math.abs(dy)) {
        return {
          x: dx > 0 ? node.position.x + STAGE_WIDTH : node.position.x,
          y: center.y
        };
      }
      return {
        x: center.x,
        y: dy > 0 ? node.position.y + STAGE_HEIGHT : node.position.y
      };
    };
    return transitions.map(tr => {
      const fromStage = stages.find(s => s.id === (tr.from_stage_id === -1 ? 'From Any' : tr.from_stage_id));
      const toStage = stages.find(s => s.id === tr.to_stage_id);
      if (!(fromStage != null && fromStage.position) || !(toStage != null && toStage.position)) return null;
      const sourcePoint = getConnectionPoint(fromStage, toStage, true);
      const targetPoint = getConnectionPoint(fromStage, toStage, false);
      const toCenter = {
        x: toStage.position.x + STAGE_WIDTH / 2,
        y: toStage.position.y + STAGE_HEIGHT / 2
      };
      let pathData;
      let labelPosition;

      // Determine if the entry to the target node is vertical (top/bottom) or horizontal (left/right)
      const isVerticalEntry = Math.abs(targetPoint.x - toCenter.x) < 1;
      if (isVerticalEntry) {
        // Entry is top/bottom. Path must end with a vertical line for the arrow.
        const midY = (sourcePoint.y + targetPoint.y) / 2;
        pathData = `M ${sourcePoint.x},${sourcePoint.y} L ${sourcePoint.x},${midY} L ${targetPoint.x},${midY} L ${targetPoint.x},${targetPoint.y}`;
        labelPosition = {
          x: (sourcePoint.x + targetPoint.x) / 2,
          y: midY
        };
      } else {
        // Entry is left/right. Path must end with a horizontal line.
        const midX = (sourcePoint.x + targetPoint.x) / 2;
        pathData = `M ${sourcePoint.x},${sourcePoint.y} L ${midX},${sourcePoint.y} L ${midX},${targetPoint.y} L ${targetPoint.x},${targetPoint.y}`;
        labelPosition = {
          x: midX,
          y: (sourcePoint.y + targetPoint.y) / 2
        };
      }
      return {
        id: `transition-${tr.id}`,
        pathData,
        label: tr.title || 'Transition',
        labelPosition
      };
    }).filter(Boolean);
  }
  function renderNodes(nodes, container, onDrag) {
    container.innerHTML = '';
    nodes.forEach(node => {
      const div = document.createElement('div');
      div.id = node.id;
      div.className = node.className;
      div.innerHTML = node.innerHTML;
      div.style.left = `${node.position.x}px`;
      div.style.top = `${node.position.y}px`;
      div.addEventListener('mousedown', e => {
        if (e.button !== 0) return;
        e.stopPropagation();
        onDrag(e, node.data);
      });
      container.appendChild(div);
    });
  }
  function highlightTransition(edgeId) {
    // Reset all first
    document.querySelectorAll('.transition-path').forEach(p => {
      p.classList.remove('highlighted');
    });
    document.querySelectorAll('.transition-label-content').forEach(l => {
      l.classList.remove('highlighted');
    });

    // Highlight selected
    const path = document.querySelector(`.transition-path[data-edge-id="${edgeId}"]`);
    const label = document.querySelector(`.transition-label-content[data-edge-id="${edgeId}"]`);
    if (path) path.classList.add('highlighted');
    if (label) label.classList.add('highlighted');
  }
  function renderEdges(edges, svg) {
    svg.querySelectorAll('path, foreignObject').forEach(el => el.remove());
    edges.forEach(edge => {
      const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      path.setAttribute('d', edge.pathData);
      path.setAttribute('class', 'transition-path');
      path.setAttribute('data-edge-id', edge.id); // track edge
      path.setAttribute('marker-end', 'url(#arrowhead)');
      const foreignObject = document.createElementNS('http://www.w3.org/2000/svg', 'foreignObject');
      foreignObject.setAttribute('class', 'transition-label');
      foreignObject.setAttribute('width', '120');
      foreignObject.setAttribute('height', '24');
      foreignObject.setAttribute('x', edge.labelPosition.x - 60);
      foreignObject.setAttribute('y', edge.labelPosition.y - 12);
      const labelDiv = document.createElement('div');
      labelDiv.className = 'transition-label-content';
      labelDiv.textContent = edge.label;
      labelDiv.dataset.edgeId = edge.id;
      labelDiv.addEventListener('click', e => {
        e.stopPropagation();
        highlightTransition(edge.id);
      });
      foreignObject.appendChild(labelDiv);
      svg.appendChild(path);
      svg.appendChild(foreignObject);
    });
  }
  function initWorkflowGraph() {
    const container = document.getElementById("workflow-container");
    const containerTitle = document.getElementById("workflow-main-title");
    const graph = document.getElementById("graph");
    const stageContainer = document.getElementById("stages");
    const svg = document.getElementById("connections");
    const statusBadge = document.querySelector(".badge[role='status']");
    if (!container || !graph || !stageContainer || !svg) {
      console.warn("[Workflow Graph] Missing required DOM elements.");
      return;
    }

    // Check if already initialized
    if (container.hasAttribute('data-workflow-initialized')) {
      console.log('[Workflow Graph] Already initialized, skipping...');
      return;
    }

    // Mark as initialized
    container.setAttribute('data-workflow-initialized', 'true');
    const workflowId = parseInt(container.dataset.workflowId, 10);
    if (!workflowId) {
      console.warn("[Workflow Graph] Invalid workflow ID.");
      return;
    }


    // Pan & Zoom state
    svg.innerHTML = `
      <defs>
      <marker 
        id="arrowhead" 
        markerWidth="8" 
        markerHeight="8" 
        refX="7" 
        refY="4" 
        orient="auto" 
        markerUnits="strokeWidth">
        <polygon points="0 0, 8 4, 0 8" class="arrow-marker" />
      </marker>
      </defs>`;
    let state = {
      stages: [],
      transitions: [],
      scale: 1,
      panX: 0,
      panY: 0,
      isDraggingStage: false
    };

    // Stage dimensions
    const STAGE_WIDTH = 200;
    const STAGE_HEIGHT = 80;
    function updateTransform() {
      graph.style.transform = `translate(${state.panX}px, ${state.panY}px) scale(${state.scale})`;
    }
    const handleNodeDrag = (startEvent, draggedStage) => {
      state.isDraggingStage = true;
      const stageElement = document.getElementById(`stage-${draggedStage.id}`);
      const dragStart = {
        x: startEvent.clientX,
        y: startEvent.clientY,
        stageX: draggedStage.position.x,
        stageY: draggedStage.position.y
      };
      stageElement.classList.add('dragging');
      const onMouseMove = moveEvent => {
        draggedStage.position.x = dragStart.stageX + (moveEvent.clientX - dragStart.x) / state.scale;
        draggedStage.position.y = dragStart.stageY + (moveEvent.clientY - dragStart.y) / state.scale;
        stageElement.style.left = `${draggedStage.position.x}px`;
        stageElement.style.top = `${draggedStage.position.y}px`;
        const edges = generateEdges(state.transitions, state.stages);
        renderEdges(edges, svg);
      };
      const onMouseUp = () => {
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('mouseup', onMouseUp);
        stageElement.classList.remove('dragging');
        setTimeout(() => {
          state.isDraggingStage = false;
        }, 0);
      };
      document.addEventListener('mousemove', onMouseMove);
      document.addEventListener('mouseup', onMouseUp);
    };
    function getBoundingBox() {
      if (!state.stages.length) return null;
      let minX = Infinity,
        minY = Infinity,
        maxX = -Infinity,
        maxY = -Infinity;
      state.stages.forEach(stage => {
        if (stage.position) {
          minX = Math.min(minX, stage.position.x);
          minY = Math.min(minY, stage.position.y);
          maxX = Math.max(maxX, stage.position.x + STAGE_WIDTH);
          maxY = Math.max(maxY, stage.position.y + STAGE_HEIGHT);
        }
      });
      if (minX === Infinity) return null;
      return {
        minX,
        minY,
        maxX,
        maxY,
        width: maxX - minX,
        height: maxY - minY
      };
    }
    function fitToScreen() {
      const bounds = getBoundingBox();
      if (!bounds) return;
      const containerRect = container.getBoundingClientRect();
      const padding = 100;
      const availableWidth = containerRect.width - padding;
      const availableHeight = containerRect.height - padding;

      // Calculate scale to fit content
      const scaleX = availableWidth / bounds.width;
      const scaleY = availableHeight / bounds.height;
      state.scale = Math.min(scaleX, scaleY, 1.5); // Allow some zoom in
      state.scale = Math.max(state.scale, 0.1); // Minimum scale

      // Center the content
      state.panX = (containerRect.width - bounds.width * state.scale) / 2 - bounds.minX * state.scale;
      state.panY = (containerRect.height - bounds.height * state.scale) / 2 - bounds.minY * state.scale;
      updateTransform();
    }
    Promise.all([getWorkflow(workflowId), getStages(workflowId), getTransitions(workflowId)]).then(([workflowData, stagesData, transitionsData]) => {
      const workflow = (workflowData == null ? void 0 : workflowData.data) || {};
      let stages = (stagesData == null ? void 0 : stagesData.data) || [];
      let transitions = (transitionsData == null ? void 0 : transitionsData.data) || [];
      ({
        stages,
        transitions
      } = filterWorkflow(stages, transitions));
      console.log('stages:', stages);
      console.log('transitions:', transitions);
      state.stages = stages;
      state.transitions = transitions;
      if (!state.stages.length) {
        showErrorInModal('No stages found.');
        return;
      }

      // Update header info
      if (containerTitle) {
        containerTitle.innerHTML = workflow.title || 'Workflow';
      }
      if (statusBadge) {
        const isPublished = workflow.published || workflow.state === 1;
        statusBadge.className = `badge ${isPublished ? 'bg-success' : 'bg-warning'}`;
        statusBadge.textContent = isPublished ? 'Enabled' : 'Disabled';
      }
      const stageCountSpan = document.querySelectorAll('dd span')[1];
      if (stageCountSpan) {
        stageCountSpan.textContent = `${state.stages.length} ${state.stages.length === 1 ? 'Stage' : 'Stages'}`;
      }
      const transitionCountSpan = document.querySelectorAll('dd span')[2];
      if (transitionCountSpan) {
        transitionCountSpan.textContent = `${state.transitions.length} ${state.transitions.length === 1 ? 'Transition' : 'Transitions'}`;
      }
      const nodes = generateNodes(state.stages, state.transitions);
      renderNodes(nodes, stageContainer, handleNodeDrag);
      const edges = generateEdges(state.transitions, state.stages);
      renderEdges(edges, svg);

      // Add zoom controls
      const zoomControls = container.querySelector('.zoom-controls');
      const zoomInBtn = zoomControls.querySelector('.zoom-in');
      const zoomOutBtn = zoomControls.querySelector('.zoom-out');
      const fitBtn = zoomControls.querySelector('.fit-screen');
      zoomInBtn.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        const rect = container.getBoundingClientRect();
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const oldScale = state.scale;
        state.scale = Math.min(state.scale * 1.2, 3);

        // Zoom towards center
        const factor = state.scale / oldScale;
        state.panX = centerX - (centerX - state.panX) * factor;
        state.panY = centerY - (centerY - state.panY) * factor;
        updateTransform();
      });
      zoomOutBtn.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        const rect = container.getBoundingClientRect();
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        const oldScale = state.scale;
        state.scale = Math.max(state.scale / 1.2, 0.1);

        // Zoom from center
        const factor = state.scale / oldScale;
        state.panX = centerX - (centerX - state.panX) * factor;
        state.panY = centerY - (centerY - state.panY) * factor;
        updateTransform();
      });
      fitBtn.addEventListener('click', e => {
        e.preventDefault();
        e.stopPropagation();
        fitToScreen();
      });

      // Pan functionality - improved to not conflict with stage dragging
      let isPanning = false,
        panStart = {};
      container.addEventListener("mousedown", e => {
        if (e.target.closest('.stage') || e.target.closest('.zoom-controls') || state.isDraggingStage) return;
        isPanning = true;
        panStart = {
          x: e.clientX - state.panX,
          y: e.clientY - state.panY
        };
        container.style.cursor = 'grabbing';
        e.preventDefault();
      });
      container.addEventListener("mousemove", e => {
        if (!isPanning || state.isDraggingStage) return;
        state.panX = e.clientX - panStart.x;
        state.panY = e.clientY - panStart.y;
        updateTransform();
      });
      container.addEventListener("mouseup", () => {
        isPanning = false;
        container.style.cursor = 'default';
      });
      container.addEventListener("mouseleave", () => {
        isPanning = false;
        container.style.cursor = 'default';
      });

      // Zoom with wheel - fixed to zoom towards mouse position
      container.addEventListener("wheel", e => {
        e.preventDefault();
        const rect = container.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        const mouseY = e.clientY - rect.top;
        const oldScale = state.scale;
        const zoomFactor = 0.1;
        if (e.deltaY < 0) {
          state.scale = Math.min(state.scale * (1 + zoomFactor), 3);
        } else {
          state.scale = Math.max(state.scale * (1 - zoomFactor), 0.1);
        }

        // Zoom towards mouse position
        const factor = state.scale / oldScale;
        state.panX = mouseX - (mouseX - state.panX) * factor;
        state.panY = mouseY - (mouseY - state.panY) * factor;
        updateTransform();
      });

      // Keyboard shortcuts
      const handleKeyDown = e => {
        if (document.activeElement.tagName === 'INPUT' || document.activeElement.tagName === 'TEXTAREA' || document.activeElement.isContentEditable) return;
        switch (e.key.toLowerCase()) {
          case '+':
          case '=':
            e.preventDefault();
            zoomControls.querySelector('.zoom-in').click();
            break;
          case '-':
            e.preventDefault();
            zoomControls.querySelector('.zoom-out').click();
            break;
          case 'f':
            e.preventDefault();
            fitToScreen();
            break;
        }
      };
      document.addEventListener('keydown', handleKeyDown);

      // Auto-fit initially with proper delay
      setTimeout(() => {
        requestAnimationFrame(() => {
          fitToScreen();
        });
      }, 100); // Reduced delay for faster fitting
    }).catch(error => {
      console.error('[Workflow Graph] Failed to initialize:', error);
      showErrorInModal(`Failed to load workflow data: ${error.message}`);
    });
  }

  // Observer for dynamic content
  const observer = new MutationObserver(mutationsList => {
    for (const mutation of mutationsList) {
      if (mutation.type === 'childList') {
        mutation.addedNodes.forEach(node => {
          if (node.nodeType === Node.ELEMENT_NODE) {
            const workflowContainer = node.querySelector ? node.querySelector('#workflow-container') : node.id === 'workflow-container' ? node : null;
            if (workflowContainer && !workflowContainer.hasAttribute('data-workflow-initialized')) {
              setTimeout(initWorkflowGraph, 100);
            }
          }
        });
      }
    }
  });
  function init() {
    const existingContainer = document.getElementById('workflow-container');
    if (existingContainer && !existingContainer.hasAttribute('data-workflow-initialized')) {
      setTimeout(initWorkflowGraph, 100);
    }
    observer.observe(document.body, {
      childList: true,
      subtree: true
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
