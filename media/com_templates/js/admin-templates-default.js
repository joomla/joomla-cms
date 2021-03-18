/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var folders = [].slice.call(document.querySelectorAll('.folder-url, .component-folder-url, .plugin-folder-url, .layout-folder-url'));
    var innerLists = [].slice.call(document.querySelectorAll('.folder ul, .component-folder ul, .plugin-folder ul, .layout-folder ul'));
    var openLists = [].slice.call(document.querySelectorAll('.show > ul'));
    var fileModalFolders = [].slice.call(document.querySelectorAll('#fileModal .folder-url'));
    var folderModalFolders = [].slice.call(document.querySelectorAll('#folderModal .folder-url')); // Hide all the folders when the page loads

    innerLists.forEach(function (innerList) {
      innerList.classList.add('hidden');
    }); // Show all the lists in the path of an open file

    openLists.forEach(function (openList) {
      openList.classList.remove('hidden');
    }); // Stop the default action of anchor tag on a click event and release the inner list

    folders.forEach(function (folder) {
      folder.addEventListener('click', function (event) {
        event.preventDefault();
        var list = event.currentTarget.parentNode.querySelector('ul');

        if (!list.classList.contains('hidden')) {
          list.classList.add('hidden');
        } else {
          list.classList.remove('hidden');
        }
      });
    }); // File modal tree selector

    fileModalFolders.forEach(function (fileModalFolder) {
      fileModalFolder.addEventListener('click', function (event) {
        event.preventDefault();
        fileModalFolders.forEach(function (fileModalFold) {
          fileModalFold.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        var listElsAddressToAdd = [].slice.call(document.querySelectorAll('#fileModal input.address'));
        listElsAddressToAdd.forEach(function (element) {
          element.value = event.currentTarget.getAttribute('data-id');
        });
      });
    }); // Folder modal tree selector

    folderModalFolders.forEach(function (folderModalFolder) {
      folderModalFolder.addEventListener('click', function (event) {
        event.preventDefault();
        folderModalFolders.forEach(function (folderModalFldr) {
          folderModalFldr.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        var listElsAddressToAdd = [].slice.call(document.querySelectorAll('#folderModal input.address'));
        listElsAddressToAdd.forEach(function (element) {
          element.value = event.currentTarget.getAttribute('data-id');
        });
      });
    });
    var treeContainer = document.querySelector('#treeholder .treeselect');
    var listEls = [].slice.call(treeContainer.querySelectorAll('.folder.show'));
    var filePathEl = document.querySelector('p.lead.hidden.path');

    if (filePathEl) {
      var filePathTmp = document.querySelector('p.lead.hidden.path').innerText;

      if (filePathTmp && filePathTmp.charAt(0) === '/') {
        filePathTmp = filePathTmp.slice(1);
        filePathTmp = filePathTmp.split('/');
        filePathTmp = filePathTmp[filePathTmp.length - 1];
        listEls.forEach(function (element, index) {
          element.querySelector('a').classList.add('active');

          if (index === listEls.length - 1) {
            var parentUl = element.querySelector('ul');
            var allLi = [].slice.call(parentUl.querySelectorAll('li'));
            allLi.forEach(function (liElement) {
              var aEl = liElement.querySelector('a');
              var spanEl = aEl.querySelector('span');

              if (spanEl && spanEl.innerText.trim()) {
                aEl.classList.add('active');
              }
            });
          }
        });
      }
    } // Image cropper


    var image = document.getElementById('image-crop');

    if (image) {
      var width = document.getElementById('imageWidth').value;
      var height = document.getElementById('imageHeight').value; // eslint-disable-next-line no-new

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
      image.addEventListener('crop', function (e) {
        document.getElementById('x').value = e.detail.x;
        document.getElementById('y').value = e.detail.y;
        document.getElementById('w').value = e.detail.width;
        document.getElementById('h').value = e.detail.height;
      });
    }
  });
})();