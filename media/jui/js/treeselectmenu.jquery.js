/**
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
jQuery(function($)
{
	var treeselectmenu = $('div#treeselectmenu').html();

	$('.treeselect li').each(function()
	{
		$li = $(this);
		$div = $li.find('div.treeselect-item:first');

		// Add icons
		$li.prepend('<i class="pull-left icon-"></i>');

		// Append clearfix
		$div.after('<div class="clearfix"></div>');

		if ($li.find('ul').length > 1 || $li.find('ul:first').children('li').length > 1) {
			// Add classes to Expand/Collapse icons
			$li.find('i').addClass('treeselect-toggle icon-minus');

			// Append drop down menu in nodes
			$div.find('label:first').after(treeselectmenu);

			// Add mouse actions for showing drop down menu
			$div
				.mouseenter(function()
				{
					$(this).find('.btn-group').removeClass('open').css('visibility', 'visible');
				})
				.mouseleave(function()
				{
					$(this).find('.btn-group').removeClass('open').css('visibility', 'hidden');
				});
		}
	});

	// Takes care of the Expand/Collapse of a node
	$('i.treeselect-toggle').click(function()
	{
		$i = $(this);
		$ulvisible = $i.parent().find('ul').is(':visible');

		// Take care of parent UL
		$i.removeClass('icon-plus icon-minus').addClass($ulvisible ? 'icon-plus' : 'icon-minus').parent().find('ul').toggle();

		// Take care of children image folders
		$i.parent().find('ul i.treeselect-toggle').removeClass('icon-plus icon-minus').addClass($ulvisible ? 'icon-plus' : 'icon-minus');

		// Take care of children ULs
		if ($ulvisible) {
			$i.parent()
				.find('ul')
				.hide();
		} else {
			$i.parent()
				.find('ul')
				.show();
		}
	});

	// Takes care of the filtering
	$('#treeselectfilter').keyup(function()
	{
		var text = $(this).val().toLowerCase();
		$('.treeselect li').each(function()
		{
			if ($(this).text().toLowerCase().indexOf(text) == -1) {
				$(this).hide();
			}
			else {
				$(this).show();
			}
		});
	});

	// Checks all checkboxes the tree
	$('#treeCheckAll').click(function()
	{
		$('.treeselect input').attr('checked', 'checked');
	});

	// Unchecks all checkboxes the tree
	$('#treeUncheckAll').click(function()
	{
		$('.treeselect input').attr('checked', false);
	});

	// Checks all checkboxes the tree
	$('#treeExpandAll').click(function()
	{
		$('ul.treeselect ul').show();
		$('ul.treeselect i.treeselect-toggle').removeClass('icon-plus').addClass('icon-minus');
	});

	// Unchecks all checkboxes the tree
	$('#treeCollapseAll').click(function()
	{
		$('ul.treeselect ul').hide();
		$('ul.treeselect i.treeselect-toggle').removeClass('icon-minus').addClass('icon-plus');
	});

	// Take care of children check/uncheck all
	$('a.checkall').click(function()
	{
		$(this).parent().parent().parent().parent().parent().parent().find('ul input').attr('checked', 'checked');
	});
	$('a.uncheckall').click(function()
	{
		$(this).parent().parent().parent().parent().parent().parent().find('ul input').attr('checked', false);
	});

	// Take care of children toggle all
	$('a.expandall').click(function()
	{
		$(this).parent().parent().parent().parent().parent().parent().find('li > ul').show();
	});
	$('a.collapseall').click(function()
	{
		$(this).parent().parent().parent().parent().parent().parent().find('li > ul').hide();
	});
});