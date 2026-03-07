import {DragDropManager, Accessibility} from '@dnd-kit/dom';
import { Sortable, isSortable } from '@dnd-kit/dom/sortable';

// let containerSelector = 'table tbody';
// let saveOrderingUrl = location.href;
// let formSelector = '#adminForm';
// let direction = 'asc';
// let isNested = false;
let itemSelector = 'tr';
let handleSelector = '.sortable-handler';

const { containerSelector, saveOrderingUrl, formSelector, direction, isNested } = Joomla.getOptions('dnd-options');

const container = document.querySelector(`${containerSelector}`);

if (typeof container !== 'object' || container === null) {
  throw new Error(`Container not found for selector: ${containerSelector}`);
}

// DND Manager
const manager = new DragDropManager({
  plugins: (defaults) => [
    ...defaults,
    Accessibility.configure({
      announcements: {
        dragstart({operation: {source}}) {
          if (!source) return;
          return Joomla.Text._('JGLOBAL_DRAGANDDROP_STARTED').replace('{{source}}', source.id);
        },
        dragover({operation: {source, target}}) {
          if (!source || !target) return;
          return Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGOVER').replace('{{source}}', source.id).replace('{{target}}', target.id);
        },
        dragend({operation: {source, target}, canceled}) {
          if (!source) return;
          if (canceled) return Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGEND_CANCELED').replace('{{source}}', source.id);
          return Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGEND_DROPPED').replace('{{source}}', source.id).replace('{{target}}', target?.id ?? Joomla.Text._('JGLOBAL_DRAGANDDROP_DRAGEND_NO_ELEMENT'));
        },
      },
    }),
  ],
});

// Create draggables
let rows = container.querySelectorAll('tr');

rows.forEach((row, index) => {
  const handle = row.querySelector('.sortable-handler');

  if (!handle) {
    console.warn(`Handle not found for row ${index}, skipping draggable initialization.`);
    return;
  }

  new Sortable(
    {
      id: `row-${index}`,
      index,
      element: row,
      handle: handle,
    },
    manager,
  );

  row.dataset.dndDraggableId = `row-${index}`;
});

// Drag end
manager.monitor.addEventListener('dragend', async (event) => {
  if (event.canceled) return;

  const {source} = event.operation;

  if (isSortable(source)) {
    const { initialIndex, index } = source;

    console.log('dragend', source.element, { initialIndex, index });
    if (initialIndex !== index) {
      // Reorder your data: move the item from initialIndex to index
      const newItems = [...rows];
      const [removed] = newItems.splice(initialIndex, 1);
      newItems.splice(index, 0, removed);
      rows = newItems;

      const saveTheOrder = await saveOrder();
      if (!saveTheOrder) {
        console.error('Failed to save the order');
        return;
      }
    }
  }
});

// Joomla order save
async function saveOrder() {
  if (!saveOrderingUrl) {
    throw new Error('Save ordering URL not provided in options');
  }

  const form = document.querySelector(formSelector);

  if (!form) {
    throw new Error(`Form not found for selector: ${formSelector}`);
  }
  const rows = container.querySelectorAll('[name="order[]"]');
  const inputRows = container.querySelectorAll('[name="cid[]"]');

  const result = [];

  rows.forEach((row, i) => {
    result.push(`order[]=${encodeURIComponent(i + 1)}`);
    result.push(`cid[]=${encodeURIComponent(inputRows[i].value)}`);
  });

  const formData = new FormData(form);
  formData.delete('task');
  formData.delete('order[]');
  const task = document.querySelector('[name="task"]');

  if (task) task.setAttribute('name', 'tmp_task');

  // console.log(`${new URLSearchParams(formData).toString()}&${result.join('&')}`);
  try {
    await Joomla.request({
      url: saveOrderingUrl,
      method: 'POST',
      data: `${new URLSearchParams(formData).toString()}&${result.join('&')}`,
      perform: true,
      promise: true,
    });
  } catch (error) {
    console.error('Error saving order:', error);
    if (task) task.setAttribute('name', 'task');
    return false;
  }

  if (task) task.setAttribute('name', 'task');
  return true;
}
