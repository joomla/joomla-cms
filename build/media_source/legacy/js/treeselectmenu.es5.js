/**
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
jQuery(function($)
{
	var treeselectmenu = $('div#treeselectmenu').html();
	var direction = (document.dir !== undefined) ? document.dir : document.getElementsByTagName("html")[0].getAttribute("dir");

	$('.treeselect li').each(function()
	{
		$li = $(this);
		$div = $li.find('div.treeselect-item:first');

		// Add icons
		$li.prepend('<span class="icon-"></span>');

		if ($li.find('ul.treeselect-sub').length) {
			// Add classes to Expand/Collapse icons
			$li.find('span.icon-').addClass('treeselect-toggle icon-chevron-down');

			// Append drop down menu in nodes
			$div.find('label:first').after(treeselectmenu);

			if (!$li.find('ul.treeselect-sub ul.treeselect-sub').length) {
				$li.find('div.treeselect-menu-expand').remove();
			}
		}
	});

	// Takes care of the Expand/Collapse of a node
	$('span.treeselect-toggle').click(function()
	{
		$i = $(this);

		if (direction === 'rtl') {
			var chevron = 'icon-chevron-left';
		} else {
			var chevron = 'icon-chevron-right';
		}

		// Take care of parent UL
		if ($i.parent().find('ul.treeselect-sub').is(':visible')) {
			$i.removeClass('icon-chevron-down').addClass(chevron);
			$i.parent().find('ul.treeselect-sub').hide();
			$i.parent().find('ul.treeselect-sub i.treeselect-toggle').removeClass('icon-chevron-down').addClass(chevron);
		} else {
			$i.removeClass(chevron).addClass('icon-chevron-down');
			$i.parent().find('ul.treeselect-sub').show();
			$i.parent().find('ul.treeselect-sub i.treeselect-toggle').removeClass(chevron).addClass('icon-chevron-down');
		}
	});

	// Takes care of the filtering
	$('#treeselectfilter').keyup(function()
	{
		var text = $(this).val().toLowerCase();
		var hidden = 0;
		$("#noresultsfound").hide();
		var $list_elements = $('.treeselect li');
		$list_elements.each(function()
		{
			if ($(this).text().toLowerCase().indexOf(text) == -1) {
				$(this).hide();
				hidden++;
			}
			else {
				$(this).show();
			}
		});
		if(hidden == $list_elements.length)
		{
			$("#noresultsfound").show();
		}
	});

	// Checks all checkboxes the tree
	$('#treeCheckAll').click(function()
	{
		$('.treeselect input').each(function() {
      var self = $(this);
      if (!self.prop('disabled')) {
        self.prop('checked', true);
      }
	  });
	});

	// Unchecks all checkboxes the tree
	$('#treeUncheckAll').click(function()
	{
		$('.treeselect input').each(function() {
      var self = $(this);
      if (!self.prop('disabled')) {
        self.prop('checked', false);
      }
	  });
	});

	// Checks all checkboxes the tree
	$('#treeExpandAll').click(function()
	{
		$('ul.treeselect ul.treeselect-sub').show();
		$('ul.treeselect span.treeselect-toggle').removeClass('icon-chevron-right').addClass('icon-chevron-down');
	});

	// Unchecks all checkboxes the tree
	$('#treeCollapseAll').click(function()
	{
		$('ul.treeselect ul.treeselect-sub').hide();
		$('ul.treeselect span.treeselect-toggle').removeClass('icon-chevron-down').addClass('icon-chevron-right');
	});
	// Take care of children check/uncheck all
	$('a.checkall').click(function()
	{
		$(this).parents().eq(4).find('input').each(function() {
      var self = $(this);
      if (!self.prop('disabled')) {
        self.prop('checked', true);
      }
	  });
	});
	$('a.uncheckall').click(function()
	{
		$(this).parents().eq(4).find('input').each(function() {
      var self = $(this);
      if (!self.prop('disabled')) {
        self.prop('checked', false);
      }
	  });
	});

	// Take care of children toggle all
	$('a.expandall').click(function()
	{
		var $parent = $(this).parents().eq(6);
		$parent.find('ul.treeselect-sub').show();
		$parent.find('ul.treeselect-sub i.treeselect-toggle').removeClass('icon-chevron-right').addClass('icon-chevron-down');
	});
	$('a.collapseall').click(function()
	{
		var $parent = $(this).parents().eq(6);
		$parent.find('li ul.treeselect-sub').hide();
		$parent.find('li i.treeselect-toggle').removeClass('icon-chevron-down').addClass('icon-chevron-right');
	});
});
