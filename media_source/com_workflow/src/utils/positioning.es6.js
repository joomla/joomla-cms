import { getColorForStage } from './utils.es6.js';

/**
 * Calculate and return positioned stage nodes in a grid layout.
 * @param {Array<Object>} stages - Array of stage objects.
 * @param {Object} options - Grid layout options (gapX, gapY, paddingX, paddingY).
 * @returns {Array<Object>} Array of positioned node configs.
 */
export function generatePositionedNodes(stages, options = {}) {
  const {
    gapX = 400,
    gapY = 300,
    paddingX = 100,
    paddingY = 100,
  } = options;

  const columns = Math.min(4, Math.ceil(Math.sqrt(stages?.length || 0)) + 1);

  return stages.map((stage, index) => {
    const col = index % columns;
    const row = Math.floor(index / columns);

    const position = stage?.position || {
      x: col * gapX + paddingX,
      y: row * gapY + paddingY,
    };

    return {
      id: String(stage.id),
      type: 'stage',
      position,
      data: {
        stage: {
          ...stage,
          color: getColorForStage(stage),
        },
        isSelected: false,
        onSelect: () => {},
        onEdit: () => {},
        onDelete: () => {},
      },
      draggable: true,
    };
  });
}

/**
 * Create special static nodes like "from_any" node.
 * @param {String} id - The ID of the special node
 * @param {Object} position - The position of the special node
 * @param {String} color - The color of the special node
 * @param {String} label - The label of the special node
 * @param {Function} onSelect - The function to call when the special node is selected
 * @param {Boolean} draggable - Whether the special node is draggable
 */
export function createSpecialNode(id, position, color, label, onSelect = () => {}, draggable = false) {
  return {
    id,
    type: 'stage',
    position,
    data: {
      stage: {
        id,
        title: label,
        published: true,
        color,
      },
      isSpecial: true,
      onSelect,
    },
    draggable,
  };
}
