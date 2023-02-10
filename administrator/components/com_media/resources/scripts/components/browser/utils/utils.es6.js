import * as types from '../../../store/mutation-types.es6';

/**
 * Handle the click event
 * @param event
 * @param ctx  the context
 */
export default function onItemClick(event, ctx) {
  if (ctx.item.path && ctx.item.type === 'file') {
    window.parent.document.dispatchEvent(
      new CustomEvent('onMediaFileSelected', {
        bubbles: true,
        cancelable: false,
        detail: {
          path: ctx.item.path,
          thumb: ctx.item.thumb,
          fileType: ctx.item.mime_type ? ctx.item.mime_type : false,
          extension: ctx.item.extension ? ctx.item.extension : false,
          width: ctx.item.width ? ctx.item.width : 0,
          height: ctx.item.height ? ctx.item.height : 0,
        },
      }),
    );
  }

  if (ctx.item.type === 'dir') {
    window.parent.document.dispatchEvent(
      new CustomEvent('onMediaFileSelected', {
        bubbles: true,
        cancelable: false,
        detail: {},
      }),
    );
  }

  // Handle clicks when the item was not selected
  if (!ctx.isSelected()) {
    // Handle clicks when shift key was pressed
    if (event.shiftKey || event.keyCode === 13) {
      const currentIndex = ctx.localItems.indexOf(ctx.$store.state.selectedItems[0]);
      const endindex = ctx.localItems.indexOf(ctx.item);
      // Handle selections from up to down
      if (currentIndex < endindex) {
        ctx.localItems.slice(currentIndex, endindex + 1)
          .forEach((element) => ctx.$store.commit(types.SELECT_BROWSER_ITEM, element));
      // Handle selections from down to up
      } else {
        ctx.localItems.slice(endindex, currentIndex)
          .forEach((element) => ctx.$store.commit(types.SELECT_BROWSER_ITEM, element));
      }
      // Handle clicks when ctrl key was pressed
    } else if (event[/Mac|Mac OS|MacIntel/gi.test(window.navigator.userAgent) ? 'metaKey' : 'ctrlKey'] || event.keyCode === 17) {
      ctx.$store.commit(types.SELECT_BROWSER_ITEM, ctx.item);
    } else {
      ctx.$store.commit(types.UNSELECT_ALL_BROWSER_ITEMS);
      ctx.$store.commit(types.SELECT_BROWSER_ITEM, ctx.item);
    }
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
