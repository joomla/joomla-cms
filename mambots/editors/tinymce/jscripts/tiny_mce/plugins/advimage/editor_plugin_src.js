/* Import plugin specific language pack */
tinyMCE.importPluginLanguagePack('advimage', 'en,de,sv,zh_cn,cs,fa,fr_ca,fr,pl,pt_br,nl');

/**
 * Insert image template function.
 */
function TinyMCE_advimage_getInsertImageTemplate() {
    var template = new Array();

    template['file']   = '../../plugins/advimage/image.htm';
    template['width']  = 430;
    template['height'] = 380; 

    // Language specific width and height addons
    template['width']  += tinyMCE.getLang('lang_insert_image_delta_width', 0);
    template['height'] += tinyMCE.getLang('lang_insert_image_delta_height', 0);

    return template;
}

function TinyMCE_advimage_cleanup(type, content) {
	switch (type) {
		case "insert_to_editor_dom":
			var imgs = content.getElementsByTagName("img");
			for (var i=0; i<imgs.length; i++) {
				var onmouseover = tinyMCE.cleanupEventStr(tinyMCE.getAttrib(imgs[i], 'onmouseover'));
				var onmouseout = tinyMCE.cleanupEventStr(tinyMCE.getAttrib(imgs[i], 'onmouseout'));

				if ((src = tinyMCE.getImageSrc(onmouseover)) != "") {
					src = tinyMCE.convertRelativeToAbsoluteURL(tinyMCE.settings['base_href'], src);
					imgs[i].setAttribute('onmouseover', "this.src='" + src + "';");
				}

				if ((src = tinyMCE.getImageSrc(onmouseout)) != "") {
					src = tinyMCE.convertRelativeToAbsoluteURL(tinyMCE.settings['base_href'], src);
					imgs[i].setAttribute('onmouseout', "this.src='" + src + "';");
				}
			}
			break;

		case "get_from_editor_dom":
			var imgs = content.getElementsByTagName("img");
			for (var i=0; i<imgs.length; i++) {
				var onmouseover = tinyMCE.cleanupEventStr(tinyMCE.getAttrib(imgs[i], 'onmouseover'));
				var onmouseout = tinyMCE.cleanupEventStr(tinyMCE.getAttrib(imgs[i], 'onmouseout'));

				if ((src = tinyMCE.getImageSrc(onmouseover)) != "") {
					src = eval(tinyMCE.settings['urlconverter_callback'] + "(src, null, true);");
					imgs[i].setAttribute('onmouseover', "this.src='" + src + "';");
				}

				if ((src = tinyMCE.getImageSrc(onmouseout)) != "") {
					src = eval(tinyMCE.settings['urlconverter_callback'] + "(src, null, true);");
					imgs[i].setAttribute('onmouseout', "this.src='" + src + "';");
				}
			}
			break;
	}

	return content;
}
