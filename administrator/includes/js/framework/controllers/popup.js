// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: popup.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @subpackage javascript
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

var popup_default = {
	exec: function(caller, params) {
		
		features = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,directories=no,location=no';
		
		if(params.top)  features += ',top='+params.top;
		if(params.left) features += ',left='+params.left;
		
		features += ',width='+params.width;
		features += ',height='+params.height;
		
		window.open( params.url, 'win1',  features); 
	}
}

var popup_previewarchive = {
	exec: function(caller, params) {
			id = this.getSelectedItem();	
			params.url = 'index2.php?option=com_content&task=preview&no_html=1&id=' + id;
			popup_default.exec(caller, params);
		},
	getSelectedItem: function() {
			if(document.list) {
				if(items = document.list.getSelectedItems()) {
					return items[0].getId();
				} else {
					return null;
				}
			}
			return null;
	}
}

var popup_previewfrontpage = {
	exec: function(caller, params) {
			id = this.getSelectedItem();	
			params.url = 'index2.php?option=com_content&task=preview&no_html=1&id=' + id;
			popup_default.exec(caller, params);
		},
	getSelectedItem: function() {
			if(document.list) {
				if(items = document.list.getSelectedItems()) {
					return items[0].getId();
				} else {
					return null;
				}
			}
			return null;
	}
}

var popup_previewcontent = {
	exec: function(caller, params) {
			id = this.getSelectedItem();
			params.url =  'index2.php?option=com_content&task=preview&no_html=1&id=' + id;
			popup_default.exec(caller, params);
		},
	getSelectedItem: function() {
			if(document.list) {
				if(items = document.list.getSelectedItems()) {	
					return items[0].getId();
				} else {
					return null;
				}
			}
			return null;
	}
}

var popup_previewmodule = {
	exec: function(caller, params) {
			content 	= content.replace('#', '');  
			var title 	= document.adminForm.title.value; 
			title 		= title.replace('#', ''); 
			params.url 	= 'index2.php?option=com_modules&task=preview&no_html=1&title=' + title + '&content' + content; 
			popup_default.exec(caller, params);
	}
}

var popup_previewpoll = {
	exec: function(caller, params) {
			id = document.adminForm.id.value;
			params.url 		= 'index2.php?option=com_poll&task=preview&no_html=1&pollid='+id;
			popup_default.exec(caller, params);
	}
}

/*var showPreview = {
	exec: function(caller, params) { 
		if (parseBool(params.update)) {
				getEditorContents( 'editor1', 'introtext' );
				getEditorContents( 'editor2', 'fulltext' );
		}		
		window.open( 'index2.php?option=com_content&task=preview&no_html=1', 'win1', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');
	}
}*/

//window popup controller
var popup = {
	_tasks: { 
		'popup-default'				: popup_default,
		'popup-previewarchive'		: popup_previewarchive,
		'popup-previewfrontpage'	: popup_previewfrontpage,
		'popup-previewcontent'		: popup_previewcontent,
		'popup-previewmodule'		: popup_previewmodule,
		'popup-previewpoll'			: popup_previewpoll
	},
	supportsTask : function(task)	{
		if(task.split('-')[0] == 'popup') {
			return true;
		}
	},
	doTask : function(task, caller, params) {
		if(task in this._tasks) {
			return this._tasks[task].exec(caller, params);
		} else {
			return popup_default.exec(caller, params);
		}
	},
	isTaskEnabled : function (task) {
		return true;
	},
	onEvent : function(event_name) {
		
	}
};