Joomla = window.Joomla || {};

tinymce.PluginManager.add("Image", function(editor, url) {
	editor.addButton("Image", {
		text: 'Image',
		title: "Insert Media",
		icon: "image",
		onclick: function() {
			var options = Joomla.getOptions('xtd-image', {})
			editor.windowManager.open({
				title  : "Change or upload image",
				url    : options.tinyPath, // + editor.getContainer().id,
				width  : parent.document.body.getBoundingClientRect().width - 50,
				height : (window.innerHeight - 100),
				buttons: [{
					text   : "Insert",
					onclick: function (e) {
						Joomla.getImage(Joomla.selectedFile, editor);

						top.tinymce.activeEditor.windowManager.close();
					}
				}, {
					text   : "Close",
					onclick: "close"
				}]
			})
		}
	})
});