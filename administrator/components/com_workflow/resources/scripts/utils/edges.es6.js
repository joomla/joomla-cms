import { getEdgeColor } from './utils.es6.js';

/**
 * Generate styled edges based on transition data.
 * @param {Array<Object>} transitions - List of transitions.
 * @param {Object} options - Optional configuration.
 * @param {Number|String|null} options.selectedId - Currently selected transition id.
 * @returns {Array<Object>} Styled edge definitions.
 */
export function generateStyledEdges(transitions, options = {}) {
  const {
    selectedId = null,
  } = options;

  return transitions.map((transition) => {
    const sourceId = transition.from_stage_id === -1 ? 'from_any' : String(transition.from_stage_id);
    const targetId = String(transition.to_stage_id);

    const isSelected = transition.id === selectedId;
    const isBiDirectional = transitions.some(
      (t) => t.from_stage_id === transition.to_stage_id && t.to_stage_id === transition.from_stage_id,
    );

    let offsetIndex = 0;
    if (isBiDirectional) {
      offsetIndex = transition.from_stage_id > transition.to_stage_id ? 1 : -1;
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
