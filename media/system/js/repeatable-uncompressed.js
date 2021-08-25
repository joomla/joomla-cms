/**
 * @package		Joomla.JavaScript
 * @copyright	(C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Options:
 * 		see defaults $.JRepeatable.defaults,
 * Options can be set through "data" atribute of the JRepeatable container (see example markup)
 *
 * Events:
 * 		$('input.form-field-repeatable')
 * 		.on('weready', function(e){
 * 			// fires when JRepeatable initialized
 * 		})
 * 		.on('prepare-template', function(e, template){
 * 			// fires when row template initialized
 * 		})
 * 		.on('prepare-modal', function(e, modal){
 * 			// fires when modal container initialized
 * 		})
 * 		.on('row-add', function(e, row){
 * 			// fires when new row added
 * 		})
 * 		.on('row-remove', function(e, row){
 * 			// fires before row removing
 * 		})
 * 		.on('value-update', function(e, value){
 * 			// fires before when value in hidden input was updated
 * 		});
 *
 * Dependancies: jQuery, Bootsrap.modal
 *
 * Example fields initial markup:
 *
 * <div id="jform_somename_container">
 *	<div id="jform_somename_modal" class="modal hide">
 * 		<table>
 * 			<thead>
 * 				<tr>
 * 					<th>Field label 1</th>
 * 					<th>Field label 2</th>
 * 					<th><a href="#" class="add">Add new</a></th>
 * 				</tr>
 * 			</thead>
 * 			<tbody>
 * 				<tr>
 * 					<td><input type="text" name="field1" /></td>
 * 					<td><input type="text" name="field2" /></td>
 * 					<td>
 * 						<a href="#" class="add">Add new after</a>
 * 						<a href="#" class="remove">Remove</a>
 * 					</td>
 * 				</tr>
 * 			</tbody>
 * 		</table>
 * 		<a href="#" class="close-modal">Close</a>
 * 	</div>
 * </div>
 * <button id="jform_somename_button" >Open modal</button>
 * <input type="hidden" name="jform[somename]" id="jform_somename" value=""
 * 		class="form-field-repeatable"
 * 		data-container="#jform_somename_container"
 * 		data-modal-element="#jform_somename_modal"
 * 		data-repeatable-element="table tbody tr"
 * 		data-bt-add="a.add" data-bt-remove="a.remove"
 * 		data-bt-modal-open="#jform_somename_button"
 * 		data-bt-modal-close="a.close-modal"
 * 		data-maximum="3" data-input="#jform_somename"
 * 		/>
 *
 * data-repeatable-element="table tbody tr" - means that <tr> inside <tbody> will be repeatable
 */

;(function($){
	"use strict";

    $.JRepeatable = function(input, options){
        // To avoid scope issues,
        var self = this;

        //direct call
        if(!self || self === window){
        	return new $.JRepeatable(input, options);
        }

        self.$input = $(input);

        // check if alredy exist
        if(self.$input.data("JRepeatable")){
        	return self;
        }

        // Add a reverse reference to the DOM object
        self.$input.data("JRepeatable", self);

        // method initialize
        self.init = function(){
        	// merge options
            self.options = $.extend({}, $.JRepeatable.defaults, options);

            self.$container = $(self.options.container);
            // Move out form the Form container
            // for prevent sending to server
            $('body').append(self.$container);

            // container where the rows is live
            self.$rowsContainer = self.$container.find(self.options.repeatableElement).parent();

            // prepare modal window
            self.prepareModal();

            // container for storing info about inputs
            self.inputs = [];
            self.values = {};

            // prepare a row template, and find available field names
            self.prepareTemplate();

            // check the values and keep it as object
            var val = self.$input.val();
            if(val){
            	// value can be not valid JSON
            	try {
            		self.values = JSON.parse(val);
				} catch (e) {
					if(e instanceof SyntaxError){
						// guess there a single quote problem
						try {
							val = val.replace(/'/g, '"').replace(/\\"/g, "\'");// ho ho ho
		            		self.values = JSON.parse(val);
						} catch (e) {
							// nop
							if(window.console){
    							console.log(e);
    						}
						}
					} else if(window.console){
						console.log(e);
					}
				}
            }

            // so init the form depend from values that we have
            self.buildRows();

            // bind open the modal
            $(document).on('click', self.options.btModalOpen, function (e) {
            	e.preventDefault();
            	self.$modalWindow.modal('show');
            });
            // bind close the modal
            self.$modalWindow.on('click', self.options.btModalClose, function (e) {
            	e.preventDefault();
            	self.$modalWindow.modal('hide');
            	// rollback
            	self.buildRows();
            });

            // bind save the modaldata
            self.$modalWindow.on('click', self.options.btModalSaveData, function (e) {
            	e.preventDefault();
            	self.$modalWindow.modal('hide');
            	self.refreshValue();
            });

            // bind add button
            self.$container.on('click', self.options.btAdd, function (e) {
            	e.preventDefault();
            	var after = $(this).parents(self.options.repeatableElement);
            	if(!after.length){
            		after = null;
            	}
            	self.addRow(after);
            });
            // bind remove button
            self.$container.on('click', self.options.btRemove, function (e) {
            	e.preventDefault();
            	var row = $(this).parents(self.options.repeatableElement);
            	self.removeRow(row);
            });

            // tell all that we a ready
            self.$input.trigger('weready');
        };

        // prepare a template that we will use for repeating
        self.prepareTemplate = function(){
        	//find available
        	var $rows = self.$container.find(self.options.repeatableElement);
        	var $row = $($rows.get(0));
        	// clear scripts that can be attached to the fields
        	try {
        		self.clearScripts($row);
			} catch (e) {
				if(window.console){
					console.log(e);
				}
			}

        	var inputs = $row.find('*[name]');
        	//keep the name and type for each
        	for(var i = 0, l = inputs.length; i < l; i++){
        		var name = $(inputs[i]).attr('name');
        		// check if alredy exist, for radio case
        		if(self.values[name]){
        			continue;
        		}
        		self.inputs.push({
        			name: name,
        			type: $(inputs[i]).attr('type') || inputs[i].tagName.toLowerCase()
        		});
        		// initialize values
        		self.values[name] = [];
        	}

        	// keep template
        	self.template = $row.prop('outerHTML');
        	// remove
        	$rows.remove();

        	// tell all that the template ready
            self.$input.trigger('prepare-template', self.template);
        };

        // prepare modal window
        self.prepareModal = function(){
        	var modalEl = $(self.options.modalElement);

        	// fix modal style
        	modalEl.css({
        		position: 'absolute',
        		width: 'auto',
        		'max-width': '100%'
        	});

        	modalEl.on('shown', function () {
        		self.resizeModal();
        	});
        	$(window).resize(function() {
        		self.resizeModal();
        	});

        	// init bootstrap modal
        	self.$modalWindow = modalEl.modal({show: false, backdrop: 'static'});

        	// tell all that the modal are ready
            self.$input.trigger('prepare-modal', self.$modalWindow);
        };

        //resize and count position for the modal popup
        self.resizeModal = function (){
        	if(!self.$modalWindow.is(':visible')){
        		// do nothing with hidden
        		return;
        	}
        	var docHalfWidth = $(document).width() / 2,
      	 	 	modalHalfWidth = self.$modalWindow.width() / 2,
      	 	 	rowsHalfWidth = self.$rowsContainer.width() / 2,
      	 	 	marginLeft = modalHalfWidth >= docHalfWidth ? 0 : -modalHalfWidth,
      	 	 	left = marginLeft ? '50%' : 0,
      	 	 	top = $(document).scrollTop() + $(window).height() * 0.2;//20% from top of visible win

        	self.$modalWindow.css({
       	    	 top: top,
       	    	 left: left,
       	         'margin-left': marginLeft,
       	         overflow: rowsHalfWidth > modalHalfWidth ? 'auto' : 'visible'
       	    });

        };

        // build rows
        self.buildRows = function(){
        	// clean up any old
        	var $oldRows = self.$rowsContainer.children();
        	if($oldRows.length){
        		self.removeRow($oldRows);
        	}

	        // go through values and add a new copy
	        // but make sure that at least one will be added
	        var count = self.values[Object.keys(self.values)[0]].length || 1,
            	row = null;
            for(var i = 0; i < count; i++){
            	row = self.addRow(row, i);
            }
        };

        // add new row
        self.addRow = function(after, valueKey){
        	// count how much we already have
        	var count = self.$container.find(self.options.repeatableElement).length;
        	if(count >= self.options.maximum){
        		return null;
        	}

        	// make new from template
        	var row = $.parseHTML(self.template);

        	//add to container
        	if(after){
        		$(after).after(row);
        	} else {
        		self.$rowsContainer.append(row);
        	}

        	var $row = $(row);
        	// fix names and id`s
        	self.fixUniqueAttributes($row, count + 1);
        	// set values
        	if(valueKey !== null && valueKey !== undefined){
            	for(var i = 0, l = self.inputs.length; i < l; i++){
            		var name  = self.inputs[i].name,
            			type  = self.inputs[i].type,
            			value = null;
            		if(self.values[name]){
            			value = self.values[name][valueKey];
            		}
            		// skip undefined
            		if(value === null || value === undefined){
            			continue;
            		}

            		if(type === 'radio'){
            			$row.find('*[name*="'+name+'"][value="' + value + '"]').attr('checked', 'checked');
            		}else if(type === 'checkbox'){
            			// check if there a multiple
            			if(value.length){
            				for(var v = 0, vl = value.length; v < vl; v++){
            					$row.find('*[name*="'+name+'"][value="' + value[v] + '"]').attr('checked', 'checked');
            				}
            			} else {
            				$row.find('*[name*="'+name+'"][value="' + value + '"]').attr('checked', 'checked');
            			}

            		} else {
            			$row.find('*[name*="'+name+'"]').val(value);
            		}
            	}
        	}

        	// try find out with related scripts,
        	// tricky thing, so be careful
        	try {
        		self.fixScripts($row);
			} catch (e) {
				if(window.console){
					console.log(e);
				}
			}

			// tell all about new row
            self.$input.trigger('row-add', $row);

        	return $row;
        };

        // remove row from container
        self.removeRow = function(row){
        	// tell all about row removing
            self.$input.trigger('row-remove', row);

        	$(row).remove();
        };

        //fix names ind id`s for field that in $row
        self.fixUniqueAttributes = function($row, count){
        	//all elements that have a "id" attribute
        	var haveIds = $row.find('*[id]');
        	self.increaseAttrName(haveIds, 'id', count);
        	// all labels that have a "for" attribute
        	var haveFor = $row.find('label[for]');
        	self.increaseAttrName(haveFor, 'for', count);
        	// all inputs that have a "name" attribute
        	var haveName = $row.find('*[name]');
        	self.increaseAttrName(haveName, 'name', count);
        };

        // increase attribute name like: attribute_value + '-' + count
        self.increaseAttrName = function (elements, attr, count){
        	for(var i = 0, l = elements.length; i < l; i++){
        		var $el =  $(elements[i]);
        		var oldValue = $el.attr(attr);
        		// set new
        		$el.attr(attr, count + '-' + oldValue);
        	}
        };

        // refresh value in the main input
        self.refreshValue = function(){
        	var $rows = self.$container.find(self.options.repeatableElement);
        	// reset existing
        	self.values = {};
        	// go through available input names
            for(var i = 0, l = self.inputs.length; i < l; i++){
            	var name = self.inputs[i].name,
            		type = self.inputs[i].type;
            	// init new
            	self.values[name] = [];
            	// find all inputs and take their values
            	for(var r = 0, rl = $rows.length; r < rl; r++){
            		var $row = $($rows[r]),
            			val  = null;
            		if(type === 'radio'){
            			val = $row.find('*[name*="'+name+'"]:checked').val();
            		}else if(type === 'checkbox'){
            			var checked = $row.find('*[name*="'+name+'"]:checked');
            			// test for multiple
            			if(checked.length > 1){
            				val = [];
            				for(var c = 0, cl = checked.length; c < cl; c++){
            					val.push($(checked[c]).val());
            				}
            			} else {
            				// single checkbox
            				val = checked.val();
            			}
            		}else{
            			val = $row.find('*[name*="'+name+'"]').val();
            		}
            		val = val === null ? '' : val;

            		self.values[name].push(val)
            	}
        	}
        	// put in to the main input
            self.$input.val(JSON.stringify(self.values));

            // tell all about value changed
            self.$input.trigger('value-update', self.values);
        };

        // remove scripts attached to fields
        self.clearScripts = function($row){
        	// destroy chosen if any
        	if($.fn.chosen){
                $row.find('select').each(function(){
					var $el = $(this);
					if ($el.data('chosen')) {
						$el.chosen('destroy');
						$el.addClass('here-was-chosen');
					}
				});
        	}
        	// colorpicker
        	if($.fn.minicolors){
        		$row.find('.minicolors input').each(function(){
        			$(this).minicolors('destroy', $(this));
        		});
        	}
        };

        // method for hack the scripts that can be related
        // to the one of field that in given $row
        self.fixScripts = function($row){

			// Chosen.js
			if ($.fn.chosen) {
				$row.find('select.here-was-chosen').removeClass('here-was-chosen').chosen();
			}

        	//color picker
        	$row.find('.minicolors').each(function() {
        		var $el = $(this);
        		$el.minicolors({
					control: $el.attr('data-control') || 'hue',
					position: $el.attr('data-position') || 'right',
					theme: 'bootstrap'
				});
			});

        	// fix media field
        	$row.find('a[onclick*="jInsertFieldValue"]').each(function(){
        		var $el = $(this),
        			inputId = $el.siblings('input[type="text"]').attr('id'),
        			$select = $el.prev(),
        			oldHref = $select.attr('href');
        		// update the clear button
        		$el.attr('onclick', "jInsertFieldValue('', '" + inputId + "');return false;");
        		// update select button
        		$select.attr('href', oldHref.replace(/&fieldid=(.+)&/, '&fieldid=' + inputId + '&'));
				jMediaRefreshPreview(inputId);
        	});
		
		// fix media field in ISIS Template
		$row.find('.field-media-wrapper').each(function(){
			var $el = $(this);
			$el.fieldMedia();
		});

        	// another modals
        	if(window.SqueezeBox && window.SqueezeBox.assign){
        		SqueezeBox.assign($row.find('a.modal').get(), {parse: 'rel'});
        	}
        };

        // Run initializer
        self.init();
    };

    // defaults
    $.JRepeatable.defaults = {
    	modalElement: "#modal-container", // id of the modal container
    	btModalOpen: "#open-modal", // id of the button for initiate the modal window
    	btModalClose: ".close-modal", // button for close the modal window, and rollback all changes
    	btModalSaveData: ".save-modal-data", // button for close the modal window, and keep the all changes
    	btAdd: "a.add", //  button selector for "add" action
    	btRemove: "a.remove",//  button selector for "remove" action
    	maximum: 10, // maximum repeating
    	repeatableElement: "table tbody tr"
    };

    $.fn.JRepeatable = function(options){
        return this.each(function(){
        	var options = options || {},
        		data = $(this).data();

        	for (var p in data) {
                // check options in the element
                if (data.hasOwnProperty(p)) {
                     options[p] = data[p];
                }
            }
         	new $.JRepeatable(this, options);
        });
    };

    // initialise all available
    // wait when all will be loaded, important for scripts fix
	$(window).on('load', function(){
		$('input.form-field-repeatable').JRepeatable();
	})

})(jQuery);

