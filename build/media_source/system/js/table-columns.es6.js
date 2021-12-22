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

  const detailElement = document.createElement('details');
  const summary = document.createElement('summary');

  // @todo Needs to be translateable
  summary.innerText = 'Table options';
  detailElement.appendChild(summary);

  const ul = document.createElement('ul');
  headers.forEach((el, index) => {
    // Remove the first column as we don't want to hide the row select checkbox
    if (index === 0) return;

    el.classList.remove('d-none', 'd-md-table-cell', 'd-lg-table-cell', 'd-xl-table-cell');

    const li = document.createElement('li');
    const label = document.createElement('label');
    const input = document.createElement('input');
    input.type = 'checkbox';

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

    label.appendChild(input);
    li.appendChild(label);
    ul.appendChild(li);
  });
  detailElement.appendChild(ul);

  rows.forEach((col) => {
    [].slice.call(col.children)
      .forEach((cc, index) => {
        if (cc.nodeName !== 'TH') {
          cc.classList.remove('d-none', 'd-md-table-cell', 'd-lg-table-cell', 'd-xl-table-cell');
          if (storage.get(index) === true) {
            toggleHidden(index);
          }
        } else {
          // remove the checkbox for this column as its the "main link" of an item.
          // BRIAN - I assume this is supposed to remove the li for the item where the children nodeName === 'TH'
          // BRIAN - but it fails - not sure why
          const lis = [].slice.call(document.querySelector('details ul').children);
          lis[index].remove();
        }
      });
  });

  table.insertAdjacentElement('afterend', detailElement);
}
