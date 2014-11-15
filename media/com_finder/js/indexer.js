var FinderIndexer = function(){
	var totalItems= null;
	var batchSize= null;
	var offset= null;
	var progress= null;
	var optimized= false;
	var pb;
	var $ = jQuery.noConflict();
	var path = 'index.php?option=com_finder&tmpl=component&format=json';

	var initialize = function () {
		offset = 0;
		progress = 0;
		pb = new Fx.ProgressBar(document.getElementById('finder-progress-container'));
		path = path + '&' + $('#finder-indexer-token').attr('name') + '=1';
		getRequest('indexer.start');
	};

	var getRequest= function (task) {
       $.ajax({
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
			if (pb) {
			    $(pb.element).remove();
			}
			try {
				if (json.error) {
					$('#finder-progress-header').text(json.header).addClass('finder-error');
					$('#finder-progress-message').html(json.message).addClass('finder-error');
				}
			} catch (ignore) {
				if (error === '') {
					error = Joomla.JText._('COM_FINDER_NO_ERROR_RETURNED');
				}
				$('#finder-progress-header').text(Joomla.JText._('COM_FINDER_AN_ERROR_HAS_OCCURRED')).addClass('finder-error');
				$('#finder-progress-message').html(error).addClass('finder-error');
			}
		}
		return true;
	};

	var handleFailure= function (xhr) {
		json = (typeof xhr == 'object' && xhr.responseText) ? xhr.responseText : null;
		json = json ? JSON.decode(json, true) : null;
        if (pb) {
            $(pb.element).remove();
        };
		if (json) {
			json = json.responseText != null ? Json.evaluate(json.responseText, true) : json;
		}
		var header = json ? json.header : Joomla.JText._('COM_FINDER_AN_ERROR_HAS_OCCURRED');
		var message = json ? json.message : Joomla.JText._('COM_FINDER_MESSAGE_RETURNED') + ' <br />' + json;
		$('#finder-progress-header').text(header).addClass('finder-error');
		$('#finder-progress-message').html(message).addClass('finder-error');
	};

	var updateProgress = function (header, message) {
		progress = (offset / totalItems) * 100;
		$('#finder-progress-header').text(header);
		$('#finder-progress-message').html(message);
		if (pb && progress < 100) {
			pb.set(progress);
		} else if (pb) {
	        $(pb.element).remove();
			pb = false;
		}
	};

	initialize();
};

jQuery(function ($) {
	Indexer = new FinderIndexer();
	if (typeof window.parent.SqueezeBox == 'object') {
		$(window.parent.SqueezeBox).on('close', function () {
			window.parent.location.reload(true);
		});
	}
});
