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

      // eslint-disable-next-line no-new
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
