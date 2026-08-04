import { getEdgeColor } from './utils.es6.js';

/**
 * Generate styled edges based on transition data.
 * @param {Array<Object>} transitions - List of transitions.
 * @param {Object} options - Optional configuration - contains selected transition id.
 * @returns {Array<Object>} Styled edge definitions.
 */
export function generateStyledEdges(transitions, options = {}) {
  const {
    selectedId = null,
  } = options;

  // Group transitions by source-target pair
  const edgeGroups = {};
  transitions.forEach((transition) => {
    const sourceId = transition.from_stage_id === -1 ? 'from_any' : String(transition.from_stage_id);
    const targetId = String(transition.to_stage_id);
    const key = `${sourceId}__${targetId}`;
    if (!edgeGroups[key]) edgeGroups[key] = [];
    edgeGroups[key].push(transition);
  });

  // Assign offsetIndex for each edge in a group
  const edgeOffsetMap = new Map();
  Object.entries(edgeGroups).forEach(([key, group]) => {
    group.forEach((transition, idx) => {
      edgeOffsetMap.set(transition.id, idx - (group.length - 1) / 2);
    });
  });

  return transitions.map((transition) => {
    const sourceId = transition.from_stage_id === -1 ? 'from_any' : String(transition.from_stage_id);
    const targetId = String(transition.to_stage_id);

    const isSelected = transition.id === selectedId;
    const isBiDirectional = transitions.some(
      (t) => t.from_stage_id === transition.to_stage_id && t.to_stage_id === transition.from_stage_id,
    );

    // Offset index for multiple edges between same source-target
    let offsetIndex = edgeOffsetMap.get(transition.id) || 0;

    // If bidirectional, add a small extra offset to separate further
    if (isBiDirectional) {
      offsetIndex += transition.from_stage_id > transition.to_stage_id ? 1 : -1;
    }

    const edgeColor = getEdgeColor(transition, isSelected);
    const strokeWidth = isSelected ? 5 : 3;

    return {
      id: String(transition.id),
      source: sourceId,
      target: targetId,
      type: 'custom',
      animated: isSelected,
      style: {
        stroke: edgeColor,
        strokeWidth,
        strokeDasharray: transition.published ? undefined : '5,5',
        zIndex: isSelected ? 1000 : 1,
      },
      markerEnd: {
        type: 'arrow',
        width: 10,
        height: 10,
        color: edgeColor,
      },
      data: {
        ...transition,
        isSelected,
        isBiDirectional,
        offsetIndex,
        onEdit: () => {},
        onDelete: () => {},
      },
    };
  });
}
