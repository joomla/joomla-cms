tinymce.PluginManager.add('jdragdrop', function(editor) {

	// Reset the drop area border
	tinyMCE.DOM.bind(document, 'dragleave', function(e) {
		e.stopPropagation();
		e.preventDefault();
		tinyMCE.activeEditor.contentAreaContainer.style.borderWidth='';

		return false;
	});

	// The upload logic
	function UploadFile(file) {
		var fd = new FormData();
		fd.append('Filedata', file);
		fd.append('folder', mediaUploadPath);

		var xhr = new XMLHttpRequest();

		xhr.upload.onprogress = function(e) {
			var percentComplete = (e.loaded / e.total) * 100;
			jQuery('.bar').width(percentComplete + '%');
		};

		removeProgessBar = function(){
			setTimeout(function(){
				jQuery('#jloader').remove();
				editor.contentAreaContainer.style.borderWidth = '';
			}, 200);
		};

		xhr.onload = function() {
			var resp = JSON.parse(xhr.responseText);

			if (xhr.status == 200) {
				if (resp.status == '0') {
					removeProgessBar();

					tinyMCE.activeEditor.windowManager.alert(resp.message + ': ' + setCustomDir + resp.location);

				}

				if (resp.status == '1') {
					removeProgessBar();

					// Create the image tag
					var newNode = tinyMCE.activeEditor.getDoc().createElement ('img');
					newNode.src= setCustomDir + resp.location;
					tinyMCE.activeEditor.execCommand('mceInsertContent', false, newNode.outerHTML);
				}
			} else {
				removeProgessBar();
			}
		};

		xhr.onerror = function() {
			removeProgessBar();
		};

		xhr.open("POST", uploadUri, true);
		xhr.send(fd);

	}

	// Listers for drag and drop
	if (typeof FormData != 'undefined'){

		// Fix for Chrome
		editor.on('dragenter', function(e) {
			e.stopPropagation();

			return false;
		});


		// Notify user when file is over the drop area
		editor.on('dragover', function(e) {
			e.preventDefault();
			tinyMCE.activeEditor.contentAreaContainer.style.borderStyle = 'dashed';
			tinyMCE.activeEditor.contentAreaContainer.style.borderWidth = '5px';

			return false;
		});

		// Logic for the dropped file
		editor.on('drop', function(e) {

			// We override only for files
			if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
				for (var i = 0, f; f = e.dataTransfer.files[i]; i++) {

					// Only images allowed
					if (f.name.toLowerCase().match(/\.(jpg|jpeg|png|gif)$/)) {

						// Display a spining Joomla! logo
						jQuery('.mce-toolbar-grp').append(
							'<div id=\"jloader\">' +
							'   <div class=\"progress progress-success progress-striped active\" style=\"width:100%;height:30px;\">' +
							'       <div class=\"bar\" style=\"width: 0%\"></div>' +
							'   </div>' +
							'</div>');
						editor.contentAreaContainer.style.borderWidth = '';

						// Upload the file(s)
						UploadFile(f);
					}

					e.preventDefault();
				}
			}
			editor.contentAreaContainer.style.borderWidth = '';
		});
	} else {
		Joomla.renderMessages({'error': [Joomla.JText._("PLG_TINY_ERR_UNSUPPORTEDBROWSER")]});
		editor.on('drop', function(e) {
			e.preventDefault();

			return false;
		});
	}
});
