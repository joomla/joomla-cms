<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string   $itemId The item id number
 * @var  string   $typeId The type id number
 * @var  string   $title The link text
 * @var  string   $typeAlias The component type
 */
extract($displayData);

$link = 'index.php?option=com_contenthistory&amp;view=history&amp;layout=modal&amp;tmpl=component&amp;item_id='
	. (int) $itemId . '&amp;type_id=' . $typeId . '&amp;type_alias='
	. $typeAlias . '&amp;' . JSession::getFormToken() . '=1';

echo JHtml::_(
	'bootstrap.renderModal',
	'versionsModal',
	array(
		'url'    => $link,
		'title'  => JText::_('COM_CONTENTHISTORY_MODAL_TITLE'),
		'height' => '300px',
		'width'  => '800px',
		'footer' => '<a class="btn" type="button" data-dismiss="modal" aria-hidden="true">'
			. JText::_('JTOOLBAR_CLOSE') . '</a>'
	)
);
?>
<button onclick="jQuery('#versionsModal').modal('show')" class="btn btn-small" data-toggle="modal">
	<span class="icon-archive" aria-hidden="true"></span><?php echo $title; ?>
</button>

