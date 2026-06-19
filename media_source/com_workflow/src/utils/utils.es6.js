/**
 * Utility function to compute color for a stage based on its ID.
 * Uses a hue offset to ensure color uniqueness.
 * @param {Object} stage - Stage object with an `id` field.
 * @returns {string} HSL color string.
 */
export function getColorForStage(stage) {
  return stage?.color || "rgb(var(--primary-rgb))";
}

/**
 * Utility function to compute color for a transition based on its ID.
 * Uses a different hue offset than stages.
 * @param {Object} transition - Transition object with an `id` field.
 * @returns {string} HSL color string.
 */
export function getColorForTransition(transition) {
  const hue = (parseInt(transition?.id, 10) * 199) % 360;
  return `hsl(${hue}, 70%, 60%)`;
}

/**
 * Utility function to determine edge color for a transition.
 * @param {Object} transition - Transition object.
 * @param {boolean} isSelected - Whether the edge is currently selected.
 * @returns {string} Hex or HSL color.
 */
export function getEdgeColor(transition, isSelected) {
  if (isSelected) return getColorForTransition(transition); // Blue for selected
  if (transition?.published) return '#3B82F6';
  return (transition.from_stage_id === -1 || transition.to_stage_id === -1) ? '#F97316' : '#10B981';
}

/**
 * Utility function to debounce a function call by delay in milliseconds.
 * Useful for rate-limiting input or UI updates.
 * @param {Function} func - Function to debounce.
 * @param {number} delay - Delay in milliseconds.
 * @returns {Function} Debounced function.
 */
export function debounce(func, delay) {
  let timer;
  return function debounced(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => func.apply(this, args), delay);
  };
}

/**
 * Utility function to check if the document is in right-to-left (RTL) mode.
 * @returns {boolean} True if RTL, false otherwise.
 */
export function isRTL() {
  return document.dir === 'rtl';
}
