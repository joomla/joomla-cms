/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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

		if ($li.find('ul.treeselect-sub').length) {
			// Add classes to Expand/Collapse icons
			$li.find('i').addClass('treeselect-toggle icon-minus');

			// Append drop down menu in nodes
			$div.find('label:first').after(treeselectmenu);

			if (!$li.find('ul.treeselect-sub ul.treeselect-sub').length) {
				$li.find('div.treeselect-menu-expand').remove();
			}
		}
	});

	// Takes care of the Expand/Collapse of a node
	$('i.treeselect-toggle').click(function()
	{
		$i = $(this);

		// Take care of parent UL
		if ($i.parent().find('ul.treeselect-sub').is(':visible')) {
			$i.removeClass('icon-minus').addClass('icon-plus');
			$i.parent().find('ul.treeselect-sub').hide();
			$i.parent().find('ul.treeselect-sub i.treeselect-toggle').removeClass('icon-minus').addClass('icon-plus');
		} else {
			$i.removeClass('icon-plus').addClass('icon-minus');
			$i.parent().find('ul.treeselect-sub').show();
			$i.parent().find('ul.treeselect-sub i.treeselect-toggle').removeClass('icon-plus').addClass('icon-minus');
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
		$('ul.treeselect ul.treeselect-sub').show();
		$('ul.treeselect i.treeselect-toggle').removeClass('icon-plus').addClass('icon-minus');
	});

	// Unchecks all checkboxes the tree
	$('#treeCollapseAll').click(function()
	{
		$('ul.treeselect ul.treeselect-sub').hide();
		$('ul.treeselect i.treeselect-toggle').removeClass('icon-minus').addClass('icon-plus');
	});

	// Take care of children check/uncheck all
	$('a.checkall').click(function()
	{
		$(this).parent().parent().parent().parent().parent().parent().find('ul.treeselect-sub input').attr('checked', 'checked');
	});
	$('a.uncheckall').click(function()
	{
		$(this).parent().parent().parent().parent().parent().parent().find('ul.treeselect-sub input').attr('checked', false);
	});

	// Take care of children toggle all
	$('a.expandall').click(function()
	{
		$parent = $(this).parent().parent().parent().parent().parent().parent().parent();
		$parent.find('ul.treeselect-sub').show();
		$parent.find('ul.treeselect-sub i.treeselect-toggle').removeClass('icon-plus').addClass('icon-minus');
		;
	});
	$('a.collapseall').click(function()
	{
		$parent = $(this).parent().parent().parent().parent().parent().parent().parent();
		$parent.find('li ul.treeselect-sub').hide();
		$parent.find('li i.treeselect-toggle').removeClass('icon-minus').addClass('icon-plus');
		;
	});
});