(function( $ ){
	$.fn.cjtags = function(options) {
		
		var formfield = this;
		var inputbox = formfield.clone().removeAttr('id').attr('name', 'tags_dummy');
		
		var cfg = $.extend( {
		      'url'				: 'top',
		      'container'		: 'tag-container',
		      'max_tags'		: 10
		    }, options);
		
		var container = $('<div>', {'class': cfg.container});
		container.append($('<ul>'));
		formfield.after(inputbox, container).hide();
		var self = {
			add_tag: function(tag){
				var tags = $.isArray(tag) ? tag : [tag];
				for(var i = 0; i < tags.length; i++){
					container.find('ul').append(
						$('<li>', {'id': 0, 'class': 'label'}).append(
							$('<a>', {'class': 'btn-remove-tag', 'href': '#', 'onclick': 'return false'}).append($('<i>', {'class': 'icon-remove icon-white'})),
							'&nbsp;',
							$('<span>', {'class': 'tag-item'}).append(tags[i])
						));
					self.apply_tags();
				}
				inputbox.val('');
			},
			remove_tag: function(ele){
				ele.closest('li').hide('slow', function(){ 
					$(this).closest('li').remove();
					self.apply_tags();
				});
			},
			apply_tags: function(){
				formfield.val('');
				container.find('.tag-item').each(function(){
					var tag_item = $(this);
					formfield.val( $.trim(formfield.val()).length ? formfield.val()+','+tag_item.text() : tag_item.text() );
				});
			}
		};

		inputbox.typeahead({
			minLength: 1,
			menu: '<ul class="tag-suggestions typeahead dropdown-menu"></ul>',
			item: '<li><a href="#"></a></li>',
			source: function (query, process) {
				$.getJSON(cfg.url, { search: query }, function (data) {
					results = new Array();
					$.each(data.tags, function (i, tag) {
						var found = false;
						container.find('.tag-item').each(function(){
							if(tag.tag_text.toLowerCase() == $(this).text().toLowerCase()){
								found  = true;
								return false;
							}
						});
						if(!found) {
							if(null != tag.description){
								results.push('<li><div class="tag-value">'+tag.tag_text+'</div><div>'+tag.description.substring(0, 100)+'...</div></li>');
							}else{
								results.push('<li><div class="tag-value">'+tag.tag_text+'</div></li>');
							}
						}
					});
					process(results);
				});
			},
			highlighter: function (item) {
			    var regex = new RegExp( '(' + this.query + ')', 'gi' );
			    var value = $('<div>').html(item).find('.tag-value').html().replace(regex, "<strong>$1</strong>");
			    var rtn = $('<div>', {'class': 'dummy'}).html(item);
			    rtn.find('.tag-value').html(value);
			    return rtn.html();
			},
			updater: function (item) {
				var found = false;
				var value = $('<div>').html(item).find('.tag-value').text();
				container.find('.tag-item').each(function(){
					if(value.toLowerCase() == $(this).text().toLowerCase()){
						found  = true;
						return false;
					}
				});
				
				if($.trim(value).length && !found){
					self.add_tag(value);
				}
				
				return '';
			}
		});
		
		inputbox.keypress(function(e){
			if ( e.which == 13 || e.which == 59 || e.which == 44){
				e.preventDefault();
				var found = false;
				var value = $.trim(inputbox.val());
				
				if(value == '' || value.length < 2) return false;
				
				container.find('.tag-item').each(function(){
					if(value.toLowerCase() == $(this).text().toLowerCase()){
						found  = true;
						return false;
					}
				});
				
				if(!found){
					self.add_tag(value);
				}
			}
		});
		
		inputbox.blur(function(e){
			var e = jQuery.Event('keypress');
			e.which = 13;
			inputbox.trigger(e);
		});
		
		container.on('click', '.btn-remove-tag', function(){
			self.remove_tag($(this));
		});

		if($.trim(inputbox.val()).length > 0) {
			var values = $.trim(inputbox.val()).split(',');
			self.add_tag(values);
		}
	};
})( jQuery );