function init() {
	tinyMCEPopup.resizeToInnerSize();
}

function insertEmotion(file_name, title) {
	tinyMCE.insertImage(tinyMCE.baseURL + "/plugins/emotions/images/" + file_name, tinyMCE.getLang(title));
	tinyMCEPopup.close();
}
