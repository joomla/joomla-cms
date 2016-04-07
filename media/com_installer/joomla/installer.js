
var com_installer_execute = function( params ){
	jQuery.getJSON( params.callurl, function( data ){
		if( data.status ){
			var stat_percent  = data.percent_complete;
			var stat_inbytes  = data.bytes_read;
			var stat_outbytes = data.bytes_extracted;
			var stat_files    = data.files_extracted;

			if( stat_percent >= 0 && stat_percent <= 100 ){
				jQuery('#progress-bar').css('width', stat_percent + '%').attr('aria-valuenow', stat_percent);
			}
			jQuery('#extpercent').text(stat_percent.toFixed(1));
			jQuery('#extbytesin').text(stat_inbytes);
			jQuery('#extbytesout').text(stat_outbytes);
			jQuery('#extfiles').text(stat_files);
		}
		var returnUrl = params.returnurl
									+ (/\?/.test(params.returnurl)?'&':'?')
									+ 'success=' + (data.status && data.status == 'success' ? 1 : 0) + '&'
									+ 'message=' + (data.message ? encodeURI(data.message) : '');
		document.location = returnUrl;
	});
}