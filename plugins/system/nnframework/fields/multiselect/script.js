/*
 Copyright (c) 2009 Justin Donato (http://www.justindonato.com)

 Permission is hereby granted, free of charge, to any person obtaining a copy
 of this software and associated documentation files (the "Software"), to deal
 in the Software without restriction, including without limitation the rights
 to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the Software is
 furnished to do so, subject to the following conditions:

 The above copyright notice and this permission notice shall be included in
 all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 THE SOFTWARE.

 nn_multiselect: An widget to make more user friendly interfaces
 for a multiselect box, using Mootools

 Usage:

 Link to this file, then add

 window.addEvent('domready', function (){
 $$('.multiselect').each(function(multiselect){
 new MTMultiWidget({'datasrc': multiselect});
 });

 });

 to your page, where '.multiselect' is a selector that picks out a class of
 <select MULTIPLE> framework. This element acts as the data source for the
 widget. Then create a css file and style the widget however you'd like.

 Options: add these to the object you pass to the MTMultiWidget constructor.
 Only datasrc is required.

 widgetcls:
 The css class applied to your final widget.
 Default is 'nn_multiselect'

 datasrc:
 A select multiple dom object

 selectedcls:
 The css class of the selected list item.
 Default is  'selected'

 case_sensitive:
 Determines if the filter form is case_sensitive or not
 Default is False

 */

if (typeof( window['NNMultiSelect'] ) == "undefined") {
	window.addEvent('domready', function()
	{
		$$('.nn_multiselect').each(function(multiselect)
		{
			new NNMultiSelect({'datasrc': multiselect});
		});
	});

	var NNMultiSelect = new Class({

		Implements: [Options],

		options: {
			'widgetcls': 'nn_multiselect', // the class for your final widget
			'datasrc': null  // a select multiple dom object
		},

		handleDisplayEvent: function()
		{
			this.filterform.update();
		},

		handleFilterEvent: function(list)
		{
			this.curlist = list;
			this.displaylist.build(list);
		},

		initialize: function(options)
		{
			this.setOptions(options);
			// Hide the original data source and create a new div for the widget
			// and inect this new widget into the Dom.
			options.datasrc.setStyles({'display': 'None'});
			var view = new Element('div', {'id': 'nnms_'+options.datasrc.id, 'class': this.options.widgetcls});
			view.injectAfter(options.datasrc);
			//options.datasrc.getParent().grab(view);
			// Add the widget to the options. Pass them into a new DisplayList
			options.view = view;

			this.displaylist = new DisplayList(options);

			if (this.options.datasrc.length > 5) {
				this.filterform = new FilterForm(options);
				this.displaylist.addEvent('rebuild', this.handleDisplayEvent.bind(this));
				this.filterform.addEvent('rebuild', this.handleFilterEvent.bind(this));
				this.filterform.build();
			} else {
				empty = new Element('div', {'class': 'nnms_empty' });
				options.view.grab(empty, options.inputpos);
			}

			this.curlist = this.options.datasrc.getChildren();
			this.displaylist.build(this.curlist);
		}
	});

	/*
	 DisplayList
	 */
	var DisplayList = new Class({

		Implements: [Options, Events],

		options: {
			selectedcls: 'selected', // class of the item when its selected
			disabledcls: 'disabled', // class of the item when its selected
			datasrc: null, // a multiple select dom elemenet
			view: null // A parent or wrapper dom element where this element lives
		},

		initialize: function(options)
		{
			this.setOptions(options);
		},

		build: function(opts)
		{

			// If there's already an ol, remove it.
			var old = this.options.view.getElement('ol');
			if (old !== null) {
				old.destroy();
			}

			// create the list to hold the visible framework
			list = new Element('ol', {'id': 'nnms_ol_'+this.options.datasrc.id });
			this.options.view.grab(list, this.options.inputpos);

			opts.each(function(item)
			{
				var li = new Element('li', {
					'class': item.disabled ? this.options.disabledcls : ( item.selected ? this.options.selectedcls : null ),
					'text': item.get('text'),
					'title': item.get('text').trim()
				});
				li.setProperty('style', item.getProperty('style'));
				li.store('select', item);
				list.grab(li);
				if (!item.disabled) {
					li.addEvent('click', function(evt)
					{
						evt.target.toggleClass('selected');
						evt.target.retrieve('select').selected = evt.target.hasClass('selected');
						this.fireEvent('rebuild');
					}.bind(this));
				}
			}.bind(this));
		}
	});

	/*
	 FilterForm
	 */
	var FilterForm = new Class({

		Implements: [Options, Events],

		options: {
			view: null,
			case_sensitive: false,
			displaylist: null,
			inputpos: 'top',
			classes: {
				'total': 'mttotal',
				'selected': 'mtselected'
			}
		},

		initialize: function(options)
		{
			this.setOptions(options);
			this.updatetotals();
		},

		build: function()
		{
			// infofilter bar is made out of a u list
			var ul = new Element('ul', {'class': 'nnms_filtercontrols'});
			this.options.view.grab(ul, this.options.inputpos);
			this.totalbtn = 0;

			this.selectallbtn = this.makebtn(nn_texts['selectall'],
				this.selectall);
			ul.grab(this.selectallbtn);

			this.unselectallbtn = this.makebtn(nn_texts['unselectall'],
				this.unselectall);
			ul.grab(this.unselectallbtn);

			this.totalbtn = this.makebtn(nn_texts['total'],
				this.showtotal,
				this.total);
			ul.grab(this.totalbtn);

			this.selectedbtn = this.makebtn(nn_texts['selected'],
				this.showselected,
				this.numselected);
			ul.grab(this.selectedbtn);

			this.unselectedbtn = this.makebtn(nn_texts['unselected'],
				this.showunselected,
				this.total-this.numselected);
			ul.grab(this.unselectedbtn);

			this.togglebtn = this.makebtn(nn_texts['maximize'],
				this.toggle);
			ul.grab(this.togglebtn);

			// Make text field for filter
			// On keyup, the displaylist.filter is called with a function
			// that filters based on what's been entered in the textfield
			filterbox_container = new Element('div', {'class': 'nnms_filterbox'});
			this.filterbox = new Element('input', {
				'events': {
					'keyup': function(evt)
					{
						if (this.options.case_sensitive) {
							filter_by_text = function(item) { return item.text.contains(evt.target.value)};
						} else {
							filter_by_text = function(item) { return item.text.toLowerCase().contains(evt.target.value.toLowerCase()) };
						}
						this.filter(this.options.datasrc.getChildren(), filter_by_text);
					}.bind(this)
				}
			});
			filterbox_container.grab(this.filterbox);

			this.options.view.grab(filterbox_container, this.options.inputpos);
		},

		/*
		 label: clickable link text
		 func: the function that is called when clicked
		 prefix: some bit of text that precedes the label. (Used to show
		 counts here.)

		 */
		makebtn: function(label, func, prefix)
		{
			var li = new Element('li');
			var btn = new Element('a', {
				'html': label,
				'href': '#cpcspswdnbd',
				'events': {
					// You might have to bind this differently
					'click': func.bind(this)
				}
			});
			li.grab(btn);
			if (prefix !== undefined) {
				prefix = new Element('span', {'text': prefix});
				li.grab(prefix);
			}
			return li;
		},

		showtotal: function()
		{
			// return true for every item in the datasrc
			this.filter(this.options.datasrc.getChildren(),
				function(item)
				{
					return true;
				}
			);
		},
		showselected: function()
		{
			// return true for every selected item in the datasrc
			this.filter(this.options.datasrc.getChildren(),
				function(item)
				{
					return (item.disabled || item.selected === true);
				}
			);
		},
		showunselected: function()
		{
			// return true for every non-selected item in the datasrc
			this.filter(this.options.datasrc.getChildren(),
				function(item)
				{
					return (item.disabled || item.selected !== true);
				}
			);
		},
		selectall: function()
		{
			// select all
			this.options.view.getElement('ol').getChildren().each(function(el)
			{
				item = el.retrieve('select');
				if (!item.disabled) {
					el.addClass('selected');
					item.selected = true;
				}
			});
			this.update();
		},
		unselectall: function()
		{
			// select all
			this.options.view.getElement('ol').getChildren().each(function(el)
			{
				item = el.retrieve('select');
				if (!item.disabled) {
					el.removeClass('selected');
					item.selected = false;
				}
			});
			this.update();
		},
		toggle: function()
		{
			el = this.options.view.getElement('ol');
			if (el.getStyle('max-height').toInt() == 200) {
				el.setStyle('max-height', 500);
				this.togglebtn.getElement('a').set('text', nn_texts['minimize']);
			} else {
				el.setStyle('max-height', 200);
				this.togglebtn.getElement('a').set('text', nn_texts['maximize']);
			}
		},
		// list is the list of option dom framework from the select elem
		// test is a function that gets used in the filter
		filter: function(list, test, reset)
		{
			results = list.filter(function(item, index)
			{
				return test(item);
			});

			if (reset) {
				this.filterbox.value = "";
			}
			this.fireEvent('rebuild', [results]);
		},
		update: function()
		{
			this.updatetotals();
			this.totalbtn.getElement('span').set('text', this.total);
			this.selectedbtn.getElement('span').set('text', this.numselected);
			this.unselectedbtn.getElement('span').set('text', this.total-this.numselected);
		},

		updatetotals: function()
		{
			var self = this;
			this.total = 0;
			this.numselected = 0;
			this.options.datasrc.getChildren().each(function(item)
			{
				if (!item.disabled) {
					self.total++;
					if (item.selected) {
						self.numselected++;
					}
				}
			});
		}
	});

	Element.Events.rebuild = {
		'base': 'change',
		'condition': function(evt)
		{
			return;
		}
	};
}