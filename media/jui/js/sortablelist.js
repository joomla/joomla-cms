/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(function ($) {
	$.JSortableList = function (tableWrapper, formId, sortDir, saveOrderingUrl, options, nestedList) {
		var root = this;
		var disabledOrderingElements = '';
		var sortableGroupId = '';
		var sortableRange;
		var childrenNodes;
		var sameLevelNodes;
		if (sortDir != 'desc') {
			sortDir = 'asc';
		}

		var ops = $.extend({
			orderingIcon:'add-on', //class name of order icon
			orderingWrapper:'input-prepend', //ordering control wrapper class name
			orderingGroup:'sortable-group-id', //sortable-group-id
			sortableClassName:'dndlist-sortable',
			placeHolderClassName:'dnd-list-highlight dndlist-place-holder',
			sortableHandle:'.sortable-handler'
		}, options);

		$('tr', tableWrapper).removeClass(ops.sortableClassName).addClass(ops.sortableClassName);
		//make wrapper table position be relative, to fix y-axis drag problem on Safari
		$(tableWrapper).parents('table').css('position', 'relative');
		$(ops.sortableHandle, tableWrapper).css('cursor', 'move');
		$('#' + formId).attr('autocomplete', 'off');

		var _handle = $(ops.sortableHandle, $(tableWrapper)).length > 0 ? ops.sortableHandle : '';

		$(tableWrapper).sortable({
			axis:'y',
			cursor:'move',
			handle:_handle,
			items:'tr.' + ops.sortableClassName,
			placeholder:ops.placeHolderClassName,
			helper:function (e, ui) {
				//hard set left position to fix y-axis drag problem on Safari
				$(ui).css({'left':'0px'})

				ui.children().each(function () {
					$(this).width($(this).width());
				});
				$(ui).children('td').addClass('dndlist-dragged-row');
				return ui;
			},

			start:function (e, ui) {
				root.sortableGroupId = ui.item.attr(ops.orderingGroup);
				if (root.sortableGroupId) {
					root.sortableRange = $('tr[' + ops.orderingGroup + '=' + root.sortableGroupId + ']');
				} else {
					root.sortableRange = $('.' + ops.sortableClassName);
				}
				//Disable sortable for other group's records
				root.disableOtherGroupSort(e, ui);

				//Proceed nested list
				if (nestedList){
					root.hideChildrenNodes(ui.item.attr('item-id'));
					root.hideSameLevelChildrenNodes(ui.item.attr('level'));
					$(tableWrapper).sortable('refresh');
				}
			},

			stop:function (e, ui) {
				$('td', $(this)).removeClass('dndlist-dragged-row');
				$(ui.item).css({opacity:0});
				$(ui.item).animate({
					opacity:1,
				}, 800, function (){
					$(ui.item).css('opacity','');
				});


				root.enableOtherGroupSort(e, ui);

				root.rearrangeOrderingValues(root.sortableGroupId, ui);
				if (saveOrderingUrl) {
					//clone and check all the checkboxes in sortable range to post
					root.cloneMarkedCheckboxes();

					// Detach task field if exists
					var f  = $('#' + formId);
					var ft = $('input[name|="task"]', f);

					if (ft.length) ft.detach();

					//serialize form then post to callback url
					$.post(saveOrderingUrl, f.serialize());

					// Re-Append original task field
					if (ft.length) ft.appendTo(f);

					//remove cloned checkboxes
					root.removeClonedCheckboxes();
				}
				root.disabledOrderingElements = '';
				//Proceed nested list
				if (nestedList){
					root.showChildrenNodes(ui.item);
					root.showSameLevelChildrenNodes(ui.item);
					$(tableWrapper).sortable('refresh');
				}
			}
		});
		
		this.hideChildrenNodes = function (itemId) {
			root.childrenNodes = root.getChildrenNodes(itemId);				
			root.childrenNodes.hide();
		}

		this.showChildrenNodes = function (item) {
			item.after(root.childrenNodes)
			root.childrenNodes.show();
			root.childrenNodes="";
		}

		this.hideSameLevelChildrenNodes = function (level) {
			root.sameLevelNodes = root.getSameLevelNodes(level);
			root.sameLevelNodes.each(function (){
				_childrenNodes = root.getChildrenNodes($(this).attr('item-id'));
				_childrenNodes.addClass('child-nodes-tmp-hide');
				_childrenNodes.hide();
			});
		}

		this.showSameLevelChildrenNodes = function (item) {
			prevItem = item.prev();
			prevItemChildrenNodes = root.getChildrenNodes(prevItem.attr('item-id'));
			prevItem.after(prevItemChildrenNodes);
			$('tr.child-nodes-tmp-hide').show().removeClass('child-nodes-tmp-hide');
			root.sameLevelNodes = "";
		}


		this.disableOtherGroupSort = function (e, ui) {
			if (root.sortableGroupId) {
				var _tr = $('tr[' + ops.orderingGroup + '!=' + root.sortableGroupId + ']', $(tableWrapper));
				_tr.removeClass(ops.sortableClassName).addClass('dndlist-group-disabled');

				$(tableWrapper).sortable('refresh');
			}
		}

		this.enableOtherGroupSort = function (e, ui) {
			var _tr = $('tr', $(tableWrapper)).removeClass(ops.sortableClassName);
			_tr.addClass(ops.sortableClassName)
				.removeClass('dndlist-group-disabled');
			$(tableWrapper).sortable('refresh');
		}

		this.disableOrderingControl = function () {
			$('.' + ops.orderingWrapper + ' .add-on a', root.sortableRange).hide();
		}

		this.enableOrderingControl = function () {
			$('.' + ops.orderingWrapper + ' .add-on a', root.disabledOrderingElements).show();
		}

		this.rearrangeOrderingControl = function (sortableGroupId, ui) {
			var range;
			if (sortableGroupId) {
				root.sortableRange = $('tr[' + ops.orderingGroup + '=' + sortableGroupId + ']');
			} else {
				root.sortableRange = $('.' + ops.sortableClassName);
			}
			range = root.sortableRange;
			var count = range.length;
			var i = 0;
			if (count > 1) {
				range.each(function () {
					//firstible, add both ordering icons for missing-icon item
					var upIcon = $('.' + ops.orderingWrapper + ' .add-on:first a', $(this)); //get orderup icon of current dropped item
					var downIcon = $('.' + ops.orderingWrapper + ' .add-on:last a', $(this)); //get orderup icon of current dropped item
					if (upIcon.get(0) && downIcon.get(0)) {
						//do nothing
					} else if (upIcon.get(0)) {
						upIcon.removeAttr('title');
						upIcon = $('.' + ops.orderingWrapper + ' .add-on:first', $(this)).html();
						downIcon = upIcon.replace('icon-uparrow', 'icon-downarrow');
						downIcon = downIcon.replace('.orderup', '.orderdown');
						$('.' + ops.orderingWrapper + ' .add-on:last', $(this)).html(downIcon);
					} else if (downIcon.get(0)) {
						downIcon.removeAttr('title');
						downIcon = $('.' + ops.orderingWrapper + ' .add-on:last', $(this)).html();
						upIcon = downIcon.replace('icon-downarrow', 'icon-uparrow');
						upIcon = upIcon.replace('.orderdown', '.orderup');
						$('.' + ops.orderingWrapper + ' .add-on:first', $(this)).html(upIcon);
					}
				});

				//remove orderup icon for first record
				$('.' + ops.orderingWrapper + ' .add-on:first a', range[0]).remove();
				//remove order down icon for last record
				$('.' + ops.orderingWrapper + ' .add-on:last a', range[(count - 1)]).remove();
			}
		}

		this.rearrangeOrderingValues = function (sortableGroupId, ui) {
			var range;
			if (sortableGroupId) {
				root.sortableRange = $('tr[' + ops.orderingGroup + '=' + sortableGroupId + ']');
			} else {
				root.sortableRange = $('.' + ops.sortableClassName);
			}
			range = root.sortableRange;
			var count = range.length;
			var i = 0;

			if (count > 1) {
				//recalculate order number
				if (ui.originalPosition.top > ui.position.top) //if item moved up
				{
					if (ui.item.position().top != ui.originalPosition.top){
						$('[name="order[]"]', ui.item).attr('value', parseInt($('[type=text]', ui.item.next()).attr('value')));
					}
					$(range).each(function () {
						var _top = $(this).position().top;
						if ( ui.item.get(0) !== $(this).get(0)){
							if (_top > ui.item.position().top && _top < ui.originalPosition.top + ui.item.outerHeight()) {
								if (sortDir == 'asc') {
									var newValue = parseInt($('[name="order[]"]', $(this)).attr('value')) + 1;
								} else {
									var newValue = parseInt($('[name="order[]"]', $(this)).attr('value')) - 1;
								}

								$('[name="order[]"]', $(this)).attr('value', newValue);
							}
						}
					});
				} else if (ui.originalPosition.top < ui.position.top) {
					if (ui.item.position().top != ui.originalPosition.top){
						$('[name="order[]"]', ui.item).attr('value', parseInt($('[name="order[]"]', ui.item.prev()).attr('value')));
					}
					$(range).each(function () {
						var _top = $(this).position().top;
						if ( ui.item.get(0) !== $(this).get(0)){
							if (_top < ui.item.position().top && _top >= ui.originalPosition.top) {
								if (sortDir == 'asc') {
									var newValue = parseInt($('[name="order[]"]', $(this)).attr('value')) - 1;
								} else {
									var newValue = parseInt($('[name="order[]"]', $(this)).attr('value')) + 1;
								}
								$('[name="order[]"]', $(this)).attr('value', newValue);
							}
						}

					});
				}
			}
		}

		this.cloneMarkedCheckboxes = function () {
			$('[name="order[]"]', $(tableWrapper)).attr('name', 'order-tmp');
			$('[type=checkbox]', root.sortableRange).each(function () {
				var _shadow = $(this).clone();
				$(_shadow).attr({'checked':'checked', 'shadow':'shadow', 'id':''});
				$('#' + formId).append($(_shadow));

				$('[name="order-tmp"]', $(this).parents('tr')).attr('name', 'order[]');
			});
		}

		this.removeClonedCheckboxes = function () {
			$('[shadow=shadow]').remove();
			$('[name="order-tmp"]', $(tableWrapper)).attr('name', 'order[]');
		}

		this.getChildrenNodes = function (parentId) {
			return $('tr[parents~="'+parentId+'"]');
		}

		this.getSameLevelNodes = function (level) {
			return $('tr[level='+level+']');
		}

	}
})(jQuery);
