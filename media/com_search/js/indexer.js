var SearchIndexer = function(){
	var totalItems= null;
	var batchSize= null;
	var offset= null;
	var progress= null;
	var optimized= false;
	var path = 'index.php?option=com_search&tmpl=component&format=json';

	var initialize = function () {
		offset = 0;
		progress = 0;
		path = path + '&' + jQuery('#search-indexer-token').attr('name') + '=1';
		getRequest('indexer.start');
	};

	var getRequest= function (task) {
		jQuery.ajax({
			type : "GET",
			url : path,
			data :  'task=' + task,
			dataType : 'json',
			success : handleResponse,
			error : handleFailure
		});
	};

	var handleResponse = function (json, resp) {
		try {
			if (json === null) {
				throw resp;
			}
			if (json.error) {
				throw json;
			}
			if (json.start) {
				totalItems = json.totalItems;
			}
			offset += json.batchOffset;
			updateProgress(json.header, json.message);
			if (offset < totalItems) {
				getRequest('indexer.batch');
			} else if (!optimized) {
				optimized = true;
				getRequest('indexer.optimize');
			}
		} catch (error) {
			jQuery('#progress').remove();
			try {
				if (json.error) {
					jQuery('#search-progress-header').text(json.header).addClass('search-error');
					jQuery('#search-progress-message').html(json.message).addClass('search-error');
				}
			} catch (ignore) {
				if (error === '') {
					error = Joomla.JText._('COM_FINDER_NO_ERROR_RETURNED');
				}
				jQuery('#search-progress-header').text(Joomla.JText._('COM_FINDER_AN_ERROR_HAS_OCCURRED')).addClass('search-error');
				jQuery('#search-progress-message').html(error).addClass('search-error');
			}
		}
		return true;
	};

	var handleFailure= function (xhr) {
		json = (typeof xhr == 'object' && xhr.responseText) ? xhr.responseText : null;
		json = json ? jQuery.parseJSON(json) : null;
		jQuery('#progress').remove();
		if (json) {
			json = json.responseText != null ? Json.evaluate(json.responseText, true) : json;
		}
		var header = json ? json.header : Joomla.JText._('COM_FINDER_AN_ERROR_HAS_OCCURRED');
		var message = json ? json.message : Joomla.JText._('COM_FINDER_MESSAGE_RETURNED') + ' <br />' + json;
		jQuery('#search-progress-header').text(header).addClass('search-error');
		jQuery('#search-progress-message').html(message).addClass('search-error');
	};

	var updateProgress = function (header, message) {
		progress = (offset / totalItems) * 100;
		jQuery('#search-progress-header').text(header);
		jQuery('#search-progress-message').html(message);
		if (progress < 100) {
			jQuery('#progress-bar').css('width', progress + '%').attr('aria-valuenow', progress);
		}
		else {
			jQuery('#progress-bar').removeClass('bar-success').addClass('bar-warning').attr('aria-valuemin', 100).attr('aria-valuemax', 200);
			jQuery('#progress-bar').css('width', progress + '%').attr('aria-valuenow', progress);
		}
		if (message == msg) {
			jQuery('#progress').remove();
			window.parent.jQuery('#modal-archive', parent.document).modal('hide');
		}
	};

	initialize();
};

jQuery(function () {
	Indexer = new SearchIndexer();
	if (typeof window.parent.SqueezeBox == 'object') {
		jQuery(window.parent.SqueezeBox).on('close', function () {
			window.parent.location.reload(true);
		});
	}
});
