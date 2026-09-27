import { DragDropManager, Accessibility } from '@dnd-kit/dom';
import { Sortable, isSortable } from '@dnd-kit/dom/sortable';

// @TODO make it a module and import it
class DND {
  constructor(options) {
    this.container = options.container;
    this.saveOrderingUrl = options.saveOrderingUrl;
    this.direction = options.direction || 'asc';
    this.isNested = options.isNested || false;
    this.itemSelector = options.itemSelector || 'tr';
    this.handleSelector = options.handleSelector || '.sortable-handler';
    this.getOrderData = options.getOrderData;
    this.postDropCallback = options.postDropCallback || async function () {};

    this.onDragStart = this.onDragStart.bind(this);
    this.onDragEnd = this.onDragEnd.bind(this);

    if (!this.container) throw new Error(`Container not found for selector: ${this.containerSelector}`);

    this.manager = new DragDropManager({
      plugins: (defaults) => [
        ...defaults,
        Accessibility.configure({
          screenReaderInstructions: {
            draggable: Joomla.Text._('JGLOBAL_DRAGANDDROP_INSTRUCTIONS'),
          },
          announcements: {
            dragstart: ({ operation: { source } }) => {
              if (!source) return;

              const { label, position, total } = this.getDragInfo(source);

              return Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGEND_STARTED')
                .replace('{{source}}', label)
                .replace('{{position}}', position)
                .replace('{{total}}', total);
            },
            dragover: ({ operation: { source, target } }) => {
              if (!source) return;

              // dnd-kit fires a second dragover when the element is positioned
              // over itself; skip it to avoid a duplicate announcement.
              if (target && target.id === source.id) {
                return undefined;
              }

              if (!target) {
                return Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGOVER_NO_ELEMENT')
                  .replace('{{source}}', this.getLabel(source.element));
              }

              const { label, position, total } = this.getDragInfo(target);

              return Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGOVER_ELEMENT')
                .replace('{{source}}', this.getLabel(source.element))
                .replace('{{target}}', label)
                .replace('{{position}}', position)
                .replace('{{total}}', total);
            },
            dragend: ({ operation: { source, target }, canceled }) => {
              if (!source) return;

              // A cancelled drag usually has no target, so check it first so the
              // cancellation is announced instead of "dropped".
              if (canceled) {
                const { label, position, total } = this.getDragInfo(source);

                return Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGEND_CANCELED')
                  .replace('{{source}}', label)
                  .replace('{{position}}', position)
                  .replace('{{total}}', total);
              }

              if (!target) {
                return Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGEND_DROPPED_NO_ELEMENT')
                  .replace('{{source}}', this.getLabel(source.element));
              }

              const { label, position, total } = this.getDragInfo(target);

              return Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGEND_DROPPED')
                .replace('{{source}}', this.getLabel(source.element))
                .replace('{{target}}', label)
                .replace('{{position}}', position)
                .replace('{{total}}', total);
            },
          },
        }),
      ],
    });

    const elements = this.container.querySelectorAll(this.itemSelector);

    elements.forEach((element, index) => {
      const handle = element.querySelector(this.handleSelector);

      if (!handle) return;

      new Sortable({ id: element.id ? element.id : `row-${index}`, index, element, handle }, this.manager);

      element.dataset.dndDraggableId = element.id ? element.id : `row-${index}`;

      // Give the drag handle a meaningful accessible name and role description so
      // screen readers announce e.g. "Reorder Article title, sortable item" instead
      // of "draggable, button". dnd-kit keeps these attributes because they exist.
      handle.setAttribute(
        'aria-label',
        Joomla.Text._('JGLOBAL_DRAGANDDROP_HANDLE_LABEL').replace('{{title}}', this.getLabel(element)),
      );
      handle.setAttribute('aria-roledescription', Joomla.Text._('JGLOBAL_DRAGANDDROP_ROLEDESCRIPTION'));
    });

    this.manager.monitor.addEventListener('dragstart', this.onDragStart);
    this.manager.monitor.addEventListener('dragend', this.onDragEnd);
  }

  /**
   * Destroys the DND instance and removes event listeners.
   *
   * @returns {void}
   */
  destroy() {
    this.manager.monitor.removeEventListener('dragstart', this.onDragStart);
    this.manager.monitor.removeEventListener('dragend', this.onDragEnd);
    this.manager.destroy();
  }

  /**
    * Reads an accessible label for a row from its title cell, falling back to the
    * dnd-kit internal id so screen reader announcements use the item title instead
    * of an identifier such as "row-0".
    *
    * @param {HTMLElement} element The dragged or targeted row element
    *
    * @returns {String} A human readable label
    */
  getLabel(element) {
    const cell
      = element?.querySelector('th[scope="row"] a')
        || element?.querySelector('th[scope="row"]')
        || element?.querySelector('td a');

    const label = cell ? cell.textContent.replace(/\s+/g, ' ').trim() : '';

    return label || element?.dataset.dndDraggableId || '';
  }

  /**
     * Resolves the position (1-indexed) and total count of a sortable within its
     * group so announcements can tell the user where the item is positioned.
     *
     * @param {Object} sortable A dnd-kit sortable item
     *
     * @returns {Object} The label, position and total of the item
     */
  getDragInfo(sortable) {
    const element = sortable?.element;

    const groupId = element?.dataset.draggableGroup;
    const selector = groupId ? `${this.itemSelector}[data-draggable-group="${groupId}"]` : this.itemSelector;

    const items = [...this.container.querySelectorAll(selector)].filter(
      (node) => !node.hasAttribute('data-dnd-placeholder'),
    );

    const position = items.indexOf(element) + 1;
    const total = items.length;

    return { label: this.getLabel(element), position, total };
  }

  /**
   * Handles the drag start event.
   *
   * @param {Object} event The drag start event
   *
   * @returns {void}
   */
  onDragStart(event) {
    const { source } = event.operation;

    if (!isSortable(source)) return;

    const { element } = source;
    const groupId = element.dataset.draggableGroup;
    const elementSelector = groupId ? `${this.itemSelector}[data-draggable-group="${groupId}"]` : this.itemSelector;
    const elements = [...this.container.querySelectorAll(elementSelector)];

    this.dragElementIndex = elements.indexOf(element);
  }

  /**
   * Handles the drag end event.
   *
   * @param {Object} event The drag end event
   *
   * @returns {Promise<void>}
   */
  async onDragEnd(event) {
    const { source, target } = event.operation;
    const { element } = source;

    if (!isSortable(source)) return;

    const { initialIndex, index } = source;

    // No valid target
    if (!target) {
      event.canceled = true;
      return;
    }

    // Item moved
    if (initialIndex !== index) {
      const saved = await this.saveOrder(element).catch(() => false);

      if (!saved) {
        event.canceled = true;
        return;
      }

      this.postDropCallback(element, this.container);
    }
  }

  /**
   * Creates the payload for the new order based on the moved element's new position and sends it to the server.
   *
   * @param {HTMLElement} element The moved element
   *
   * @returns {Promise<boolean>} A promise resolving to true if the order was saved successfully, false otherwise
   */
  async saveOrder(element) {
    if (!this.saveOrderingUrl) throw new Error('Save ordering URL not provided');
    if (typeof this.getOrderData !== 'function' || this.getOrderData.constructor.name !== 'AsyncFunction') {
      throw new Error('getOrderData function not provided');
    }

    const data = await this.getOrderData(element, this.itemSelector, this.container, this.dragElementIndex, this.direction);

    if (!data) {
      throw new Error('Failed to get new order data');
    }

    await Joomla.request({
      url: this.saveOrderingUrl,
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      data: JSON.stringify(data),
      perform: true,
      promise: true,
    })
      .then((response) => { if (response.status !== 200) throw new Error(`Unexpected response status: ${response.status}`); })
      .catch((error) => { return false; });

    return true;
  }
}

/**
 * Generates the new order data for the moved element based on its new position in the container.
 *
 * @param {HTMLElement} element The moved element
 * @param {String} itemSelector The item selector
 * @param {HTMLElement} container The container element
 * @param {Number} dragElementIndex The index of the dragged element
 * @param {String} direction The direction of the drag
 *
 * @returns {Array} The new order data
 */
async function getOrderData(element, itemSelector, container, dragElementIndex, direction) {
  const groupId = element.dataset.draggableGroup;
  const rowSelector = groupId ? `${itemSelector}[data-draggable-group="${groupId}"]` : itemSelector;
  const orderSelector = groupId ? `[data-draggable-group="${groupId}"] [name="order[]"]` : '[name="order[]"]';

  // Filter out dnd-kit placeholders (they have data-dnd-placeholder attribute)
  const isNotPlaceholder = (node) => {
    const row = node.closest ? node.closest(itemSelector) : node;
    return !row || !row.hasAttribute('data-dnd-placeholder');
  };

  const rowElements = [...container.querySelectorAll(rowSelector)].filter(isNotPlaceholder);
  const rows = [...container.querySelectorAll(orderSelector)].filter(isNotPlaceholder);

  const dropIndex = rowElements.indexOf(element);
  const dragIndex = dragElementIndex;

  if (dragIndex !== undefined && dragIndex !== dropIndex) {
    // Element is moved down (to a higher index)
    if (dragIndex < dropIndex) {
      rows[dropIndex].value = rows[dropIndex - 1].value;

      for (let i = dragIndex; i < dropIndex; i += 1) {
        if (direction === 'asc') {
          rows[i].value = parseInt(rows[i].value, 10) - 1;
        } else {
          rows[i].value = parseInt(rows[i].value, 10) + 1;
        }
      }
    } else if (dragIndex > dropIndex) {
      // Element is moved up (to a lower index)
      rows[dropIndex].value = rows[dropIndex + 1].value;

      for (let i = dropIndex + 1; i <= dragIndex; i += 1) {
        if (direction === 'asc') {
          rows[i].value = parseInt(rows[i].value, 10) + 1;
        } else {
          rows[i].value = parseInt(rows[i].value, 10) - 1;
        }
      }
    }
  }

  const orderedItems = [];

  // Build the complete payload from all rows (not just the group)
  const allRows = [...container.querySelectorAll('[name="order[]"]')].filter(isNotPlaceholder);
  const allIds = [...container.querySelectorAll('[name="cid[]"]')].filter(isNotPlaceholder);

  allRows.forEach((row, i) => {
    orderedItems.push({
      id: allIds[i].value,
      order: parseInt(row.value, 10),
    });
  });

  return orderedItems;
}

/**
 * Rearranges the child nodes of the given element based on their data-parents attribute.
 *
 * @param {HTMLElement} element
 * @param {HTMLElement} container
 *
 * @returns {void}
 */
function postDropCallback(element, container) {
  if (!element.dataset.itemId) return;

  const parentId = element.dataset.itemId;
  const tagName = element.tagName.toLowerCase();
  const $children = container.querySelectorAll(
    `${tagName}[data-parents~="${parentId}"]`,
  );

  if ($children.length) {
    element.after(...$children);
  }
}

// Main DOM initialization
for (const draggable of document.querySelectorAll('.js-draggable')) {
  const options = {
    container: draggable,
    saveOrderingUrl: draggable.dataset.dndUrl,
    direction: draggable.dataset.dndDirection,
    isNested: draggable.dataset.dndNested === 'true',
    itemSelector: draggable.dataset.dndItemSelector || 'tr',
    getOrderData,
    postDropCallback,
  };

  new DND(options);
}
