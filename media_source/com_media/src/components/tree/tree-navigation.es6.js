/**
 * The id of the label of a tree item. Paths can contain characters that are not valid in an id.
 * @param path
 * @returns {string}
 */
export function treeLabelId(path) {
  return `media-tree-label-${encodeURIComponent(path).replace(/[%.]/g, '_')}`;
}

/**
 * Keyboard handling of a tree item (WAI-ARIA tree view pattern)
 * @param event
 * @param node  { hasChildren, expanded, expand(), collapse(), activate() }
 */
export function onTreeItemKeydown(event, node) {
  const treeItem = event.currentTarget;
  // Only the items whose parents are expanded can get the focus
  const items = [...treeItem.closest('[role="tree"]').querySelectorAll('[role="treeitem"]')]
    .filter((item) => item.getClientRects().length > 0);
  const index = items.indexOf(treeItem);
  let next;

  switch (event.key) {
    case 'Enter':
    case ' ':
      node.activate();
      break;
    case 'ArrowRight':
      if (node.hasChildren && !node.expanded) {
        node.expand();
      } else if (node.hasChildren) {
        next = treeItem.querySelector(':scope > [role="group"] > [role="treeitem"]');
      }
      break;
    case 'ArrowLeft':
      if (node.hasChildren && node.expanded) {
        node.collapse();
      } else {
        next = treeItem.parentElement.closest('[role="treeitem"]');
      }
      break;
    case 'ArrowDown':
      next = items[index + 1];
      break;
    case 'ArrowUp':
      next = items[index - 1];
      break;
    case 'Home':
      [next] = items;
      break;
    case 'End':
      next = items[items.length - 1];
      break;
    default:
      return;
  }

  event.preventDefault();

  if (next) {
    next.focus();
  }
}
