/**
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const folders = document.querySelectorAll('.folder-url, .component-folder-url, .plugin-folder-url, .layout-folder-url');
    const innerLists = document.querySelectorAll('.folder ul, .component-folder ul, .plugin-folder ul, .layout-folder ul');
    const openLists = document.querySelectorAll('.show > ul');
    const fileModalFolders = document.querySelectorAll('#fileModal .folder-url');
    const folderModalFolders = document.querySelectorAll('#folderModal .folder-url');
    // Hide all the folders when the page loads
    innerLists.forEach((innerList) => {
      innerList.classList.add('hidden');
    });

    // Show all the lists in the path of an open file
    openLists.forEach((openList) => {
      openList.classList.remove('hidden');
    });

    // Stop the default action of anchor tag on a click event and release the inner list
    folders.forEach((folder) => {
      folder.addEventListener('click', (event) => {
        event.preventDefault();

        const list = event.currentTarget.parentNode.querySelector('ul');

        if (!list) {
          return;
        }

        if (!list.classList.contains('hidden')) {
          list.classList.add('hidden');
        } else {
          list.classList.remove('hidden');
        }

        if (event.currentTarget.hasAttribute('aria-expanded')) {
          event.currentTarget.setAttribute('aria-expanded', !list.classList.contains('hidden'));
        }
      });
    });

    // Keyboard support for the trees on the create overrides tab
    document.querySelectorAll('[role="tree"]').forEach((tree) => {
      const getItems = () => [...tree.querySelectorAll('[role="treeitem"]')]
        .filter((item) => !item.closest('ul.hidden'));

      const focusItem = (item) => {
        if (!item) {
          return;
        }

        tree.querySelectorAll('[role="treeitem"]').forEach((el) => el.setAttribute('tabindex', '-1'));
        item.setAttribute('tabindex', '0');
        item.focus();
      };

      const items = tree.querySelectorAll('[role="treeitem"]');
      if (items.length) {
        items[0].setAttribute('tabindex', '0');
        items.forEach((item) => {
          if (item !== items[0]) {
            item.setAttribute('tabindex', '-1');
          }
        });
      }

      tree.addEventListener('focusin', (event) => {
        const item = event.target.closest('[role="treeitem"]');

        if (item) {
          tree.querySelectorAll('[role="treeitem"]').forEach((el) => el.setAttribute('tabindex', '-1'));
          item.setAttribute('tabindex', '0');
        }
      });

      tree.addEventListener('keydown', (event) => {
        const item = event.target.closest('[role="treeitem"]');

        if (!item || event.altKey || event.ctrlKey || event.metaKey) {
          return;
        }

        const visible = getItems();
        const index = visible.indexOf(item);
        const isFolder = item.hasAttribute('aria-expanded');
        const expanded = item.getAttribute('aria-expanded') === 'true';
        let handled = true;

        switch (event.key) {
          case 'ArrowDown':
            focusItem(visible[index + 1]);
            break;
          case 'ArrowUp':
            focusItem(visible[index - 1]);
            break;
          case 'Home':
            focusItem(visible[0]);
            break;
          case 'End':
            focusItem(visible[visible.length - 1]);
            break;
          case 'ArrowRight':
            if (isFolder && !expanded) {
              item.click();
            } else if (isFolder) {
              focusItem(getItems()[index + 1]);
            }
            break;
          case 'ArrowLeft':
            if (isFolder && expanded) {
              item.click();
            } else {
              const parentFolder = item.closest('ul[role="group"]')?.parentNode.querySelector('[role="treeitem"][aria-expanded]');
              focusItem(parentFolder);
            }
            break;
          default:
            handled = false;
        }

        if (handled) {
          event.preventDefault();
        }
      });
    });

    // File modal tree selector
    fileModalFolders.forEach((fileModalFolder) => {
      fileModalFolder.addEventListener('click', (event) => {
        event.preventDefault();

        fileModalFolders.forEach((fileModalFold) => {
          fileModalFold.classList.remove('selected');
        });

        event.currentTarget.classList.add('selected');
        const ismedia = event.currentTarget.dataset.base === 'media' ? 1 : 0;

        document.querySelectorAll('#fileModal input.address').forEach((element) => {
          element.value = event.currentTarget.getAttribute('data-id');
        });

        document.querySelectorAll('#fileModal input[name="isMedia"]').forEach((el) => {
          el.value = ismedia;
        });
      });
    });

    // Folder modal tree selector
    folderModalFolders.forEach((folderModalFolder) => {
      folderModalFolder.addEventListener('click', (event) => {
        event.preventDefault();

        folderModalFolders.forEach((folderModalFldr) => {
          folderModalFldr.classList.remove('selected');
        });

        event.currentTarget.classList.add('selected');
        const ismedia = event.currentTarget.dataset.base === 'media' ? 1 : 0;

        document.querySelectorAll('#folderModal input.address').forEach((element) => {
          element.value = event.currentTarget.getAttribute('data-id');
        });

        document.querySelectorAll('#folderModal input[name="isMedia"]').forEach((el) => {
          el.value = ismedia;
        });
      });
    });

    const treeContainer = document.querySelector('#treeholder .treeselect');
    const listEls = treeContainer.querySelectorAll('.folder.show');
    const filePathEl = document.querySelector('p.lead.hidden.path');

    if (filePathEl) {
      let filePathTmp = document.querySelector('p.lead.hidden.path').innerText;

      if (filePathTmp && filePathTmp.charAt(0) === '/') {
        filePathTmp = filePathTmp.slice(1);
        filePathTmp = filePathTmp.split('/');
        filePathTmp = filePathTmp[filePathTmp.length - 1];

        listEls.forEach((element, index) => {
          element.querySelector('a').classList.add('active');
          if (index === listEls.length - 1) {
            const parentUl = element.querySelector('ul');

            parentUl.querySelectorAll('li').forEach((liElement) => {
              const aEl = liElement.querySelector('a');
              const spanEl = aEl.querySelector('span');

              if (spanEl && spanEl.innerText.trim()) {
                aEl.classList.add('active');
              }
            });
          }
        });
      }
    }

    // Image cropper
    const image = document.getElementById('image-crop');
    if (image) {
      const width = document.getElementById('imageWidth').value;
      const height = document.getElementById('imageHeight').value;

      new window.Cropper(image, {
        viewMode: 1,
        scalable: true,
        zoomable: false,
        movable: false,
        dragMode: 'crop',
        cropBoxMovable: true,
        cropBoxResizable: true,
        autoCrop: true,
        autoCropArea: 1,
        background: true,
        center: true,
        minCanvasWidth: width,
        minCanvasHeight: height,
      });

      image.addEventListener('crop', (e) => {
        document.getElementById('x').value = e.detail.x;
        document.getElementById('y').value = e.detail.y;
        document.getElementById('w').value = e.detail.width;
        document.getElementById('h').value = e.detail.height;
      });
    }
  });
})();
