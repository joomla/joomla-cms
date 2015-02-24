<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.modal');

$button = $displayData;
//'&amp;fieldid=imageModal_' . $button->editor .
$link = 'index.php?option=com_media&amp;view=images&amp;tmpl=component&amp;e_name=' . $button->editor . '&amp;asset=' . $button->asset . '&amp;author=' . $button->author;

// Render the modal
echo JHtmlBootstrap::renderModal(
	'imageModal_'. $button->editor, array(
		'url' => $link,
		'title' => JText::_('JLIB_FORM_CHANGE_IMAGE'),
		'width' => '800px',
		'height' => '565px')
);
?>

<a href="#imageModal_<?php echo $button->editor; ?>" class="<?php echo $button->class; ?>" role="button" title="<?php echo $button->text; ?>" data-toggle="modal" onclick="<?php echo $button->text; ?>"><i class="icon-<?php echo $button->name; ?>"></i> <?php echo $button->text; ?></a>