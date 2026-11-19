import * as types from '../../../store/mutation-types.es6';

/**
 * The detail of the onMediaFileSelected event for an item
 * @param item
 */
function selectedItemDetail(item) {
  if (item.type === 'dir') {
    return { type: item.type, name: item.name, path: item.path };
  }

  return {
    type: item.type,
    name: item.name,
    path: item.path,
    thumb: item.thumb,
    fileType: item.mime_type ? item.mime_type : false,
    extension: item.extension ? item.extension : false,
    width: item.width ? item.width : 0,
    height: item.height ? item.height : 0,
  };
}

function dispatchFileSelected(detail) {
  window.parent.document.dispatchEvent(
    new CustomEvent('onMediaFileSelected', { bubbles: true, cancelable: false, detail }),
  );
}

/**
 * Select or unselect one item with its checkbox, keeping the other selected items
 * @param item
 * @param store
 */
export function toggleItemSelection(item, store) {
  const wasSelected = store.state.selectedItems.some((selected) => selected.path === item.path);

  store.dispatch('toggleBrowserItemSelect', item);
  dispatchFileSelected(wasSelected ? {} : selectedItemDetail(item));
}

/**
 * Click handler of the button that opens an item. A click from the keyboard or from
 * assistive technology has detail 0 and opens the item. A mouse click bubbles to the
 * item and selects it, as before; a double click opens it.
 * @param event
 * @param open
 */
export function openOnNonPointerClick(event, open) {
  if (event.detail === 0) {
    event.stopPropagation();
    open();
  }
}

/**
 * Handle the click event
 * @param event
 * @param ctx  the context
 */
export default function onItemClick(event, ctx) {
  if (ctx.item.type === 'dir' || (ctx.item.path && ctx.item.type === 'file')) {
    dispatchFileSelected(selectedItemDetail(ctx.item));
  }

  // Handle clicks when the item was not selected
  if (!ctx.isSelected()) {
    // Handle clicks when ctrl key was pressed
    if (event[/Mac|Mac OS|MacIntel/gi.test(window.navigator.userAgent) ? 'metaKey' : 'ctrlKey'] || event.keyCode === 17) {
      ctx.$store.commit(types.SELECT_BROWSER_ITEM, ctx.item);

      return;
    }

    // Unselect when shift key is not pressed
    if (!event.shiftKey && event.keyCode !== 13) {
      ctx.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
      ctx.$store.commit(types.SELECT_BROWSER_ITEM, ctx.item);

      return;
    }

    const currentIndex = ctx.localItems.indexOf(ctx.$store.state.selectedItems[0]);
    const endIndex = ctx.localItems.indexOf(ctx.item);
    // Handle selections from up to down
    if (currentIndex < endIndex) {
      ctx.localItems.slice(currentIndex, endIndex + 1).forEach((element) => ctx.$store.commit(types.SELECT_BROWSER_ITEM, element));

      return;
    }

    // Handle selections from down to up
    ctx.localItems.slice(endIndex, currentIndex).forEach((element) => ctx.$store.commit(types.SELECT_BROWSER_ITEM, element));

    return;
  }

  ctx.$store.dispatch('toggleBrowserItemSelect', ctx.item);
  window.parent.document.dispatchEvent(
    new CustomEvent('onMediaFileSelected', {
      bubbles: true,
      cancelable: false,
      detail: {},
    }),
  );

  // If more than one item was selected and the user clicks again on the selected item,
  // he most probably wants to unselect all other items.
  if (ctx.$store.state.selectedItems.length > 1) {
    ctx.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
    ctx.$store.commit(types.SELECT_BROWSER_ITEM, ctx.item);
  }
}
