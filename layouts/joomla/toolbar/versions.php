<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

// Set variables
$footer = $footer = '<button class="btn" data-dismiss="modal" aria-hidden="true">' . JText::_('JTOOLBAR_CLOSE') . '</a>';
$height = $displayData['height'];
$width  = $displayData['width'];
$link   = 'index.php?option=com_contenthistory&amp;view=history&amp;layout=modal&amp;tmpl=component&amp;item_id='
		. (int) $displayData['itemId'] . '&amp;type_id=' . $displayData['typeId'] . '&amp;type_alias=' . $displayData['typeAlias']
		. '&amp;' . JSession::getFormToken() . '=1';
$title  = $displayData['title'];

// Create modal
echo JHtml::_(
	'bootstrap.renderModal',
	'contenthistoryModal',
	array(
		'title'       => $title,
		'backdrop'    => 'static',
		'keyboard'    => true,
		'closeButton' => true,
		'footer'      => $footer,
		'url'         => $link,
		'height'      => '300px',
		'width'       => '500px',
	)
);
?>

<a href="#contenthistoryModal" role="button" class="btn btn-small" data-toggle="modal" title="<?php echo $title; ?>">
	<span class="icon-archive"></span> <?php echo $title; ?>
</a>
