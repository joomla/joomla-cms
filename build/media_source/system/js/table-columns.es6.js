if (window.innerWidth > 1024) {
  const storage = {
    pagens() {
      return `joomla-tableoptions-${document.querySelector('.page-title')
        .innerText
        .trim()
        .replace(/\s'\//, '-')
        .toLowerCase()}`;
    },
    getData() {
      const item = window.localStorage.getItem(this.pagens());

      if (item == null) {
        return {};
      }
      try {
        return JSON.parse(item);
      } catch (e) {
        return {};
      }
    },
    has() {
      return Object.prototype.hasOwnProperty.call(window.localStorage, this.pagens());
    },
    set(i, v) {
      const obj = this.getData();

      if (obj) {
        obj[i] = v;
        window.localStorage.setItem(this.pagens(), JSON.stringify(obj));
      }
    },
    get(i) {
      const obj = this.getData();

      if (obj) {
        return Object.prototype.hasOwnProperty.call(obj, i) && obj[i] === 1;
      }

      return false;
    },
  };

  const table = document.querySelector('table');
  if (!table) {
    throw new Error('A table is needed');
  }
  const headers = [].slice.call(table.querySelector('thead tr').children);
  if (!headers) {
    throw new Error('A thead element is needed');
  }
  const rows = [].slice.call(table.querySelectorAll('tbody tr'));
  if (!rows.length) {
    throw new Error('The table needs rows');
  }

  const toggleHidden = (index) => {
    headers[index].classList.toggle('d-none');

    rows.forEach((col) => {
      col.children[index].classList.toggle('d-none');
    });

    if (headers[index].classList.contains('d-none')) {
      storage.set(index, 1);
    } else {
      storage.set(index, 0);
    }
  };

  const button = document.createElement('button')
  // set up the button
  button.setAttribute('class', 'btn btn-info');
  button.setAttribute('type', 'button');
  button.setAttribute('data-bs-toggle', 'dropend');
  button.setAttribute('data-bs-display', 'static');
  button.setAttribute('aria-haspopup', 'true');
  button.setAttribute('aria-expanded', 'false');
  // the id below needs to be changed to something unique (same as pagens?)
  button.setAttribute('id', '12345');

  const ul = document.createElement('ul');
  ul.setAttribute('class', 'list-unstyled');
  ul.setAttribute('id', 'columnList');

  headers.forEach((el, index) => {
    // Remove the first column as we don't want to hide the row select checkbox
    if (index === 0) return;

    el.classList.remove('d-none', 'd-md-table-cell', 'd-lg-table-cell', 'd-xl-table-cell');

    const li = document.createElement('li');
    const label = document.createElement('label');
    const input = document.createElement('input');
    input.setAttribute('class', 'form-check-input me-1');
    input.type = 'checkbox';
    input.name = 'column';

    if (storage.get(index) === false) {
      input.setAttribute('checked', '');
    }

    input.addEventListener('input', () => toggleHidden(index));

    let s = '';
    if (el.querySelector('span.visually-hidden')) {
      s = el.querySelector('span.visually-hidden').innerText;
    } else if (el.querySelector('span')) {
      s = el.querySelector('span').innerText;
    } else {
      s = el.innerText;
    }

    if (s.includes(':')) {
      label.innerText = s.split(':', 2)[1].trim();
    } else {
      label.innerText = s;
    }

    label.insertAdjacentElement('afterbegin', input);
    li.appendChild(label);
    ul.appendChild(li);
  });
  table.insertAdjacentElement('afterend', ul);
  rows.forEach((col) => {
    [].slice.call(col.children)
      .forEach((cc, index) => {
        if (cc.nodeName !== 'TH') {
          cc.classList.remove('d-none', 'd-md-table-cell', 'd-lg-table-cell', 'd-xl-table-cell');
          if (storage.get(index) === true) {
            toggleHidden(index);
          }
        } else {
          // disable the checkbox for this column as its the "main link" of an item.
          const lis = [...document.querySelector('#columnList').children];
          const input = lis[index -1].querySelector('input');
          if (input) input.setAttribute('disabled', '');
        }
      });
  });
const columnCount = document.querySelectorAll("input[name='column']:checked");
// columnCount needs to be updated when you select a checkbox - this is static :()
// add 1 to the count for the checkbox column we excluded earlier
button.innerText = (columnCount.length + 1)  + '/' + headers.length + ' ' + Joomla.Text._('JGLOBAL_COLUMNS');
table.insertAdjacentElement('afterend', button);

}
