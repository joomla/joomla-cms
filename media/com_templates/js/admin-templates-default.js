/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

(function() {
	'use strict';

	document.addEventListener('DOMContentLoaded', function() {

		var folders = document.querySelectorAll('.folder-url, .component-folder-url, .layout-folder-url');

		// Hide all the folders when the page loads
		var innerLists = document.querySelectorAll('.folder ul, .component-folder ul, .layout-folder ul');
		for (var i = 0, l = innerLists.length; i < l; i++) {
			innerLists[i].style.display = 'none';
		}

		// Show all the lists in the path of an open file
		var openLists = document.querySelectorAll('.show > ul');
		for (var i = 0, l = openLists.length; i < l; i++) {
			openLists[i].style.display = 'block';
		}

		// Stop the default action of anchor tag on a click event and release the inner list
		for (var i = 0, l = folders.length; i < l; i++) {
			folders[i].addEventListener('click', function(event) {
				event.preventDefault();

				var list = this.parentNode.querySelector('ul');

				if (list.style.display !== 'none') {
					list.style.display = 'none';
				}
				else {
					list.style.display = 'block';
				}
			});
		}

		// File modal tree selector
		var fileModalFolders = document.querySelectorAll('#fileModal .folder-url');
		for (var i = 0, l = fileModalFolders.length; i < l; i++) {
			fileModalFolders[i].addEventListener('click', function(event) {
				event.preventDefault();

				for (var i = 0, l = fileModalFolders.length; i < l; i++) {
					fileModalFolders[i].classList.remove('selected');
				}

				event.target.classList.add('selected');

				var listElsAddressToAdd = [].slice.call(document.querySelectorAll('#fileModal input.address'));

				listElsAddressToAdd.forEach(function(element) {
				  element.value = event.target.getAttribute('data-id');
				});
			});
		}

		// Folder modal tree selector
		var folderModalFolders = document.querySelectorAll('#fileModal .folder-url');
		for (var i = 0, l = folderModalFolders.length; i < l; i++) {
			folderModalFolders[i].addEventListener('click', function(event) {
				event.preventDefault();

				for (var i = 0, l = folderModalFolders.length; i < l; i++) {
					folderModalFolders[i].classList.remove('selected');
				}

				event.target.classList.add('selected');

				var listElsAddressToAdd = [].slice.call(document.querySelectorAll('#fileModal input.address'));

				listElsAddressToAdd.forEach(function(element) {
				  element.value = event.target.getAttribute('data-id');
				});
			});
		}

		var treeContainer = document.querySelector('#treeholder .treeselect'),
		    listEls       = treeContainer.querySelectorAll('.folder.show'),
		    filePathEl    = document.querySelector('p.lead.hidden.path');

		if (filePathEl) {
			var filePathTmp = document.querySelector('p.lead.hidden.path').innerText;
		}

		if (filePathTmp && filePathTmp.charAt(0) === '/') {
			filePathTmp = filePathTmp.slice(1);
			filePathTmp = filePathTmp.split('/');
			filePathTmp = filePathTmp[filePathTmp.length - 1];
			var re = new RegExp(filePathTmp);

			for (var i = 0, l = listEls.length; i < l; i++) {
				listEls[i].querySelector('a').classList.add('active');
				if (i === listEls.length - 1) {
					var parentUl = listEls[i].querySelector('ul'),
						allLi    = parentUl.querySelectorAll('li');

					for (var i = 0, l = allLi.length; i < l; i++) {
						var aEl    = allLi[i].querySelector('a'),
						    spanEl = aEl.querySelector('span');

						if (spanEl && re.test(spanEl.innerText)) {
							aEl.classList.add('active');
						}
					}
				}
			}
		}

		// Image cropper
		var image = document.getElementById('image-crop');
		if (image) {
			var width  = document.getElementById('imageWidth').value,
				height = document.getElementById('imageHeight').value;

			var cropper = new Cropper(image, {
				viewMode: 0,
				scalable: true,
				zoomable: true,
				minCanvasWidth: width,
				minCanvasHeight: height,
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
