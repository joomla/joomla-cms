import { DragDropManager, Accessibility } from '@dnd-kit/dom';
import { Sortable, isSortable } from '@dnd-kit/dom/sortable';

const options = Joomla.getOptions('dnd-options');

if (!options) {
  throw new Error('DND options not found. Please ensure Joomla.getOptions("dnd-options") returns the necessary configuration.');
}

// @TODO make it a module and import it
export class DND {
  constructor(options) {
    this.containerSelector = options.containerSelector || 'table tbody';
    this.saveOrderingUrl = options.saveOrderingUrl || location.href;
    this.formSelector = options.formSelector || '#adminForm';
    this.direction = options.direction || 'asc';
    this.isNested = options.isNested || false;
    this.itemSelector = options.itemSelector || 'tr';
    this.handleSelector = options.handleSelector || '.sortable-handler';

    this.onDragEnd = this.onDragEnd.bind(this);

    this.init();
  }

  init() {
    this.container = document.querySelector(this.containerSelector);

    if (!this.container) {
      throw new Error(`Container not found for selector: ${this.containerSelector}`);
    }

    this.manager = new DragDropManager({
      plugins: (defaults) => [
        ...defaults,
        Accessibility.configure({
          announcements: {
            dragstart: ({operation: {source}}) => {
              if (!source) return;
              return Joomla.Text._('JGLOBAL_DRAGANDDROP_STARTED')
                .replace('{{source}}', source.id);
            },
            dragover: ({operation: {source, target}}) => {
              if (!source || !target) return;
              return Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGOVER')
                .replace('{{source}}', source.id)
                .replace('{{target}}', target.id);
            },
            dragend: ({operation: {source, target}, canceled}) => {
              if (!source) return;

              if (canceled) {
                return Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGEND_CANCELED')
                  .replace('{{source}}', source.id);
              }

              return Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGEND_DROPPED')
                .replace('{{source}}', source.id)
                .replace('{{target}}', target?.id ?? Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGEND_NO_ELEMENT'));
            },
          },
        }),
      ],
    });

    const rows = this.container.querySelectorAll(this.itemSelector);

    rows.forEach((row, index) => {
      const handle = row.querySelector(this.handleSelector);

      if (!handle) return;

      new Sortable({ id: `row-${index}`, index, element: row, handle }, this.manager);

      row.dataset.dndDraggableId = `row-${index}`;
    });

    this.manager.monitor.addEventListener('dragend', this.onDragEnd);
  }

  destroy() {
    this.manager.monitor.removeEventListener('dragend', this.onDragEnd);
    this.manager.destroy();
  }

  async onDragEnd(event) {
    const { source, target } = event.operation;

    if (!isSortable(source)) return;

    const { initialIndex, index } = source;

    // No valid target
    if (!target) {
      // @TODO A11Y: Announce error via screen reader and reverted position
      event.canceled = true;
      return;
    }

    // Item moved
    if (initialIndex !== index) {
      const saved = await this.saveOrder();

      if (!saved) {
        // @TODO A11Y: Announce error via screen reader and reverted position
        event.canceled = true;
      }
    }
  }

  async saveOrder() {
    if (!this.saveOrderingUrl) {
      throw new Error('Save ordering URL not provided');
    }

    const rows = this.container.querySelectorAll('[name="order[]"]');
    const ids = this.container.querySelectorAll('[name="cid[]"]');

    const orderedItems = [];

    rows.forEach((row, i) => {
      orderedItems.push({
        id: ids[i].value,
        order: i + 1,
      });
    });

    try {
      await Joomla.request({
        url: this.saveOrderingUrl,
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        data: JSON.stringify(orderedItems),
        perform: true,
        promise: true,
      })
      .then((response) => {
        if (response.status !== 200) {
          throw new Error(`Unexpected response status: ${response.status}`);
        }
      })
      .catch((error) => {
        return false;
      });

      return true;
    } catch {
      return false;
    }
  }
}

new DND(options);
