/**
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const treeselectmenu = document.getElementById('treeselectmenu');
const direction = (document.dir !== undefined) ? document.dir : document.documentElement.dir;

document.querySelectorAll('.treeselect li').forEach((li) => {
  // Add icons
  const icon = document.createElement('span');
  // add tabindex to the span
  icon.setAttribute('tabindex', '0');

  icon.classList.add('icon-');
  li.prepend(icon);

  if (li.querySelector('ul.treeselect-sub')) {
    // Add classes to Expand/Collapse icons
    li.querySelector('span.icon-').classList.add('treeselect-toggle', 'icon-chevron-down');

    // Append drop down menu in nodes
    if (treeselectmenu) {
      li.querySelector('div.treeselect-item label').insertAdjacentHTML('afterend', treeselectmenu.innerHTML);
    }

    const sub = li.querySelector('ul.treeselect-sub');
    const expand = li.querySelector('div.treeselect-menu-expand');
    if (!sub.querySelector('ul.treeselect-sub') && expand) {
      expand.remove();
    }
  }
});

// Takes care of the Expand/Collapse of a node
document.querySelectorAll('span.treeselect-toggle').forEach((toggle) => {
  toggle.addEventListener('click', ({ target }) => {
    const chevron = direction === 'rtl' ? 'icon-chevron-left' : 'icon-chevron-right';

    // Take care of parent UL
    const { parentNode } = target;
    if (parentNode.querySelector('ul.treeselect-sub').classList.contains('hidden')) {
      target.classList.remove(chevron);
      target.classList.add('icon-chevron-down');
      parentNode.querySelectorAll('ul.treeselect-sub').forEach((item) => item.classList.remove('hidden'));
      parentNode.querySelectorAll('ul.treeselect-sub i.treeselect-toggle').forEach((item) => {
        item.classList.add('icon-chevron-down');
        item.classList.remove(chevron);
      });
    } else {
      target.classList.add(chevron);
      target.classList.remove('icon-chevron-down');

      parentNode.querySelectorAll('ul.treeselect-sub').forEach((item) => item.classList.add('hidden'));
      parentNode.querySelectorAll('ul.treeselect-sub i.treeselect-toggle').forEach((item) => {
        item.classList.remove('icon-chevron-down');
        item.classList.add(chevron);
      });
    }
  });

  toggle.addEventListener('keypress', (event) => {
    if (event.key === 'Enter') {
      toggle.click();
    }
  });
});

// Takes care of the filtering
document.getElementById('treeselectfilter').addEventListener('keyup', ({ target }) => {
  const noResults = document.getElementById('noresultsfound');
  const text = target.value.toLowerCase();
  let hidden = 0;

  noResults.classList.add('hidden');

  const listItems = document.querySelectorAll('.treeselect li');
  listItems.forEach((item) => {
    if (item.innerText.toLowerCase().includes(text)) {
      item.classList.remove('d-none');
    } else {
      item.classList.add('d-none');
      hidden += 1;
    }
  });

  if (hidden === listItems.length) {
    noResults.classList.remove('hidden');
  }
});

// Checks all checkboxes the tree
document.getElementById('treeCheckAll').addEventListener('click', () => {
  document.querySelectorAll('.treeselect input').forEach((input) => {
    input.checked = true;
  });
});

// Unchecks all checkboxes the tree
document.getElementById('treeUncheckAll').addEventListener('click', () => {
  document.querySelectorAll('.treeselect input').forEach((input) => {
    input.checked = false;
  });
});

// Expands all subtrees
document.getElementById('treeExpandAll').addEventListener('click', () => {
  document.querySelectorAll('ul.treeselect ul.treeselect-sub').forEach((input) => input.classList.remove('hidden'));
  document.querySelectorAll('ul.treeselect span.treeselect-toggle').forEach((item) => {
    item.classList.remove('icon-chevron-right');
    item.classList.add('icon-chevron-down');
  });
});

// Collapses all subtrees
document.getElementById('treeCollapseAll').addEventListener('click', () => {
  document.querySelectorAll('ul.treeselect ul.treeselect-sub').forEach((input) => input.classList.add('hidden'));
  document.querySelectorAll('ul.treeselect span.treeselect-toggle').forEach((item) => {
    item.classList.remove('icon-chevron-down');
    item.classList.add('icon-chevron-right');
  });
});

// Take care of children check/uncheck all
document.querySelectorAll('a.checkall').forEach((item) => {
  item.addEventListener('click', ({ target }) => {
    target.closest('li').querySelectorAll('ul.treeselect-sub input').forEach((input) => {
      input.checked = true;
    });
  });
});
document.querySelectorAll('a.uncheckall').forEach((item) => {
  item.addEventListener('click', ({ target }) => {
    target.closest('li').querySelectorAll('ul.treeselect-sub input').forEach((input) => {
      input.checked = false;
    });
  });
});

// Take care of children toggle all
document.querySelectorAll('a.expandall').forEach((item) => {
  item.addEventListener('click', ({ target }) => {
    const parent = target.closest('ul');
    parent.querySelectorAll('ul.treeselect-sub').forEach((input) => input.classList.remove('hidden'));
    parent.querySelectorAll('ul.treeselect-sub .treeselect-toggle').forEach((toggle) => {
      toggle.classList.remove('icon-chevron-right');
      toggle.classList.add('icon-chevron-down');
    });
  });
});
document.querySelectorAll('a.collapseall').forEach((item) => {
  item.addEventListener('click', ({ target }) => {
    const parent = target.closest('ul');
    parent.querySelectorAll('ul.treeselect-sub').forEach((input) => input.classList.add('hidden'));
    parent.querySelectorAll('ul.treeselect-sub .treeselect-toggle').forEach((toggle) => {
      toggle.classList.remove('icon-chevron-down');
      toggle.classList.add('icon-chevron-right');
    });
  });
});
