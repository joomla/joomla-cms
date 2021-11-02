class TreeView extends HTMLElement {
  // constructor() {
  //   super();
  // }

  connectedCallback() {
    try {
      this.data = JSON.parse(this.getAttribute('json'));
    } catch {
      throw new Error('JSON parsing problem.');
    }

    if (!this.data) return;

    this.data.forEach((data) => {
      const liParent = document.createElement('li');
      if (!data.dir) liParent.innerHTML = `<span class="fa fa-file"></span>${child.name || child.file}`;
      liParent.dataset.path = data.path;
      this.appendChild(liParent);
      if (data.dir) {
        liParent.innerHTML = `<a href="#" style="cursor: pointer;"><span class="fa fa-folder"></span>${data.name || data.file}</a>`;
        this.childs(liParent, data);
        this.hide();
      }
    });
  }

  childs(liParent, data) {
    // Create a new unordered list for children
    const childList = document.createElement('ul');
    data.dir.forEach((child) => {
      const liChild = document.createElement('li');
      if (!child.dir) liChild.innerHTML = `<span class="fa fa-file"></span>${child.name || child.file}`;
      liChild.dataset.path = child.path;
      childList.appendChild(liChild);
      if (child.dir) {
        liChild.innerHTML = `<a href="#" style="cursor: pointer; text-decoration: none;"><span class="fa fa-folder"></span>${child.name || child.file}</a>`;
        this.childs(liChild, child);
      }
    });
    liParent.appendChild(childList);
  }

  // Hide childs function
  hide() {
    const ulChildren = Array.from(this.querySelectorAll('ul'));
    const liChildren = Array.from(this.querySelectorAll('li'));
    ulChildren.forEach((ul) => {
      ul.style.display = 'none';
      ul.previousElementSibling.children[0].classList.remove('fa-folder-open');
      ul.previousElementSibling.children[0].classList.add('fa-folder');
    });
    liChildren.forEach((li) => {
      if (li.querySelector('ul') !== null) {
        li.querySelector('a').onclick = (event) => {
          const a = event.target;
          const next = a.nextElementSibling;
          if (next.style.display === '') {
            a.children[0].classList.remove('fa-folder-open');
            a.children[0].classList.add('fa-folder');
            next.style.display = 'none';
          } else {
            a.children[0].classList.remove('fa-folder');
            a.children[0].classList.add('fa-folder-open');
            next.style.display = '';
          }
        };
      }
    });
  }
}

customElements.define('tree-view', TreeView);

// , { extends: 'ul' }
