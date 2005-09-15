// <?php !! This fools phpdocumentor into parsing this file
/**
* @version $Id: submit.js 4 2005-09-06 19:22:37Z akede $
* @package Mambo
* @subpackage javascript
* @copyright (C) 2000 - 2005 Miro International Pty Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* Mambo is Free Software
*/

var submit_default = { 
	exec: function(caller, params) { 
		if(params.id) {
			cb = eval('document.adminForm.' + params.id);
			cb.checked = true;
		}
		submitbutton(params.task); }
};

var submit_remove = {
	exec: function(caller, params) { 
		if(params.msg) {
			if (confirm(params.msg)) {
				submit_default.exec(caller, params);
			}
		} else {
			submit_default.exec(caller, params);
		}
	}
};

var submit_back = {
	exec: function(caller, params) { 
		if(params.href) {
			window.location.href = params.href;
		} else {
			window.history.back();
		}
	}
}

var submit_link = {
	exec: function(caller, params) { 
		if(params.href) {
			window.location.href = params.href;
		}
	}
}

var submit_reorder = {
	exec: function(caller, params) {
		frm = document.adminForm;
		if (frm.orderCol.value == params.col) {
			frm.orderDirn.value = 1 - frm.orderDirn.value;
		} else {
			frm.orderDirn.value = 0;
		}
		frm.orderCol.value = params.col;
		submitform();
	}
}

var submit_saveorder = {
	exec: function(caller, params) {
		document.list.selectAllItems();
		submitform('saveorder');
	}
}

//form submit controller
var submit = {
	_tasks: { 
		'submit-default'		: submit_default,
		'submit-remove'		: submit_remove, 
		'submit-back'			: submit_back,
		'submit-link'			: submit_link,
		'submit-reorder'		: submit_reorder,
		'submit-saveorder'	: submit_saveorder
	},
	supportsTask : function(task)	{
		if(task.split('-')[0] == 'submit') {
			return true;
		}
	},
	doTask : function(task, caller, params) {
		if(task in this._tasks) {
			return this._tasks[task].exec(caller, params);
		} else {
			return submit_default.exec(caller, params);
		}
	},
	isTaskEnabled : function (task) {
		return true;
	},
	onEvent : function(event_name) {
		
	}
};

