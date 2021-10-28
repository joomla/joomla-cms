class TreeView extends HTMLElement {
  constructor() {
    super();
    this.data = JSON.parse(this.getAttribute('json'));
  }

  connectedCallback() {
    this.data.forEach(data => {
      const liParent = document.createElement(`li`);
      liParent.innerText= data.name || data.file;
      this.appendChild(liParent);
      if (data.dir !== undefined) {
        this.childs(liParent, data);
        this.hide();
      }
    });
  }

  childs(liParent, data) {
    // Create a new unordered list for children
    const childList = document.createElement(`ul`);
    data.dir.forEach(child => {
      const liChild = document.createElement(`li`);
      liChild.innerText = child.value;
      childList.appendChild(liChild);
      if (child.dir !== undefined) {
        this.childs(liChild, child);
      }
    });
    liParent.appendChild(childList);
  }

  // Hide childs function
  hide() {
    var ulChildren = Array.from(this.querySelectorAll(`ul`));
    var liChildren = Array.from(this.querySelectorAll(`li`));
    ulChildren.forEach(ul => {
      ul.style.display = `none`;
    });
    liChildren.forEach(li => {
      var childrenText = li.childNodes[0];
      if (li.querySelector(`ul`) != null) {
        const span = document.createElement(`span`);
        span.textContent = childrenText.textContent;
        span.style.cursor = `pointer`;
        childrenText.parentNode.insertBefore(span, childrenText);
        childrenText.parentNode.removeChild(childrenText);
        span.onclick = (event) => {
          var next = event.target.nextElementSibling;
          if (next.style.display == ``) {
            next.style.display = `none`;
          }
          else {
            next.style.display = ``;
          }
        }
      }
    });
  }
}

customElements.define('tree-view', TreeView, { extends: 'ul' });
