<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string   $tableId                 The id of the table
 * @var  string   $formId                  The id of the form
 * @var  string   $saveOrderingUrl         Save the ordering URL?
 * @var  string   $sortDir                 The direction of the order (asc/desc)
 * @var  string   $nestedList              Is it nested list?
 * @var  string   $proceedSaveOrderButton  Is there a button to initiate the ordering?
 */

extract($displayData);

// Depends on jQuery UI
JHtml::_('jquery.ui', array('core', 'sortable'));

JHtml::_('script', 'jui/sortablelist.js', array('version' => 'auto', 'relative' => true));
JHtml::_('stylesheet', 'jui/sortablelist.css', array('version' => 'auto', 'relative' => true));

// Attach sortable to document
JFactory::getDocument()->addScriptDeclaration(
	"
		jQuery(document).ready(function ($){
			var sortableList = new $.JSortableList('#"
	. $tableId . " tbody','" . $formId . "','" . $sortDir . "' , '" . $saveOrderingUrl . "','','" . $nestedList . "');
		});
	"
);

if ($proceedSaveOrderButton)
{
	JFactory::getDocument()->addScriptDeclaration(
		"
		jQuery(document).ready(function ($){
			var saveOrderButton = $('.saveorder');
			saveOrderButton.css({'opacity':'0.2', 'cursor':'default'}).attr('onclick','return false;');
			var oldOrderingValue = '';
			$('.text-area-order').focus(function ()
			{
				oldOrderingValue = $(this).attr('value');
			})
			.keyup(function (){
				var newOrderingValue = $(this).attr('value');
				if (oldOrderingValue != newOrderingValue)
				{
					saveOrderButton.css({'opacity':'1', 'cursor':'pointer'}).removeAttr('onclick')
				}
			});
		});
		"
	);
}
