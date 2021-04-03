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
      return obj[i] === 1;
    }

    return false;
  },
};

const table = document.querySelector('table');
const headers = [].slice.call(table.querySelector('thead tr').children);
const columns = [].slice.call(table.querySelectorAll('tbody tr'));

function toggleHidden(index) {
  if (headers[index].classList.contains('d-md-table-cell')) {
    headers[index].classList.remove('d-md-table-cell');
  }

  headers[index].classList.toggle('d-none');
  columns.forEach((col) => {
    if (col.children[index].classList.contains('d-md-table-cell')) {
      col.children[index].classList.remove('d-md-table-cell');
    }
    col.children[index].classList.toggle('d-none');
  });

  if (headers[index].classList.contains('d-none')) {
    storage.set(index, 1);
  } else {
    storage.set(index, 0);
  }
}

if (window.innerWidth > 1024) {
  headers.forEach((el) => {
    el.classList.remove('d-none', 'd-md-table-cell', 'd-lg-table-cell');
  });
  columns.forEach((col) => {
    [].slice.call(col.children)
      .forEach((cc, index) => {
        cc.classList.remove('d-none', 'd-md-table-cell', 'd-lg-table-cell');
        if (storage.get(index) === true) {
          toggleHidden(index);
        }
      });
  });

  const detailElement = document.createElement('details');
  const summary = document.createElement('summary');
  summary.innerText = 'Table options';
  detailElement.appendChild(summary);

  const ul = document.createElement('ul');
  headers.forEach((el, index) => {
    if (index === 0 /* checkbox */ || index === 1 /* ordering */ || index === headers.length - 2) {
      return;
    }
    const li = document.createElement('li');
    const label = document.createElement('label');
    const input = document.createElement('input');
    input.type = 'checkbox';

    if (storage.get(index) === 0) {
      input.setAttribute('checked', '');
    }

    input.addEventListener('input', () => toggleHidden(index));
    if (el.querySelector('span')) {
      label.innerText = el.querySelector('span').innerText;
    } else {
      label.innerText = el.innerText;
    }
    label.appendChild(input);
    li.appendChild(label);
    ul.appendChild(li);
  });
  detailElement.appendChild(ul);

  table.insertAdjacentElement('afterend', detailElement);
}
