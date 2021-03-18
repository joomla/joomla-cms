/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const folders = [].slice.call(document.querySelectorAll('.folder-url, .component-folder-url, .plugin-folder-url, .layout-folder-url'));
    const innerLists = [].slice.call(document.querySelectorAll('.folder ul, .component-folder ul, .plugin-folder ul, .layout-folder ul'));
    const openLists = [].slice.call(document.querySelectorAll('.show > ul'));
    const fileModalFolders = [].slice.call(document.querySelectorAll('#fileModal .folder-url'));
    const folderModalFolders = [].slice.call(document.querySelectorAll('#folderModal .folder-url')); // Hide all the folders when the page loads

    innerLists.forEach(innerList => {
      innerList.classList.add('hidden');
    }); // Show all the lists in the path of an open file

    openLists.forEach(openList => {
      openList.classList.remove('hidden');
    }); // Stop the default action of anchor tag on a click event and release the inner list

    folders.forEach(folder => {
      folder.addEventListener('click', event => {
        event.preventDefault();
        const list = event.currentTarget.parentNode.querySelector('ul');

        if (!list.classList.contains('hidden')) {
          list.classList.add('hidden');
        } else {
          list.classList.remove('hidden');
        }
      });
    }); // File modal tree selector

    fileModalFolders.forEach(fileModalFolder => {
      fileModalFolder.addEventListener('click', event => {
        event.preventDefault();
        fileModalFolders.forEach(fileModalFold => {
          fileModalFold.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        const listElsAddressToAdd = [].slice.call(document.querySelectorAll('#fileModal input.address'));
        listElsAddressToAdd.forEach(element => {
          element.value = event.currentTarget.getAttribute('data-id');
        });
      });
    }); // Folder modal tree selector

    folderModalFolders.forEach(folderModalFolder => {
      folderModalFolder.addEventListener('click', event => {
        event.preventDefault();
        folderModalFolders.forEach(folderModalFldr => {
          folderModalFldr.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        const listElsAddressToAdd = [].slice.call(document.querySelectorAll('#folderModal input.address'));
        listElsAddressToAdd.forEach(element => {
          element.value = event.currentTarget.getAttribute('data-id');
        });
      });
    });
    const treeContainer = document.querySelector('#treeholder .treeselect');
    const listEls = [].slice.call(treeContainer.querySelectorAll('.folder.show'));
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
            const allLi = [].slice.call(parentUl.querySelectorAll('li'));
            allLi.forEach(liElement => {
              const aEl = liElement.querySelector('a');
              const spanEl = aEl.querySelector('span');

              if (spanEl && spanEl.innerText.trim()) {
                aEl.classList.add('active');
              }
            });
          }
        });
      }
    } // Image cropper


    const image = document.getElementById('image-crop');

    if (image) {
      const width = document.getElementById('imageWidth').value;
      const height = document.getElementById('imageHeight').value; // eslint-disable-next-line no-new

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
        minCanvasHeight: height
      });
      image.addEventListener('crop', e => {
        document.getElementById('x').value = e.detail.x;
        document.getElementById('y').value = e.detail.y;
        document.getElementById('w').value = e.detail.width;
        document.getElementById('h').value = e.detail.height;
      });
    }
  });
})();