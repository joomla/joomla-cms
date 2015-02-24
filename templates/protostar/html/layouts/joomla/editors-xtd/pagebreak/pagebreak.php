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

// Render the modal
echo JHtmlBootstrap::renderModal(
	'modal_'. $button->named, array(
		'url' => $button->link ,
		'title' => JText::_('JLIB_FORM_CHANGE_IMAGE'),
		'width' => '800px',
		'height' => '565px')
);
?>

<a href="#modal_<?php echo $button->named; ?>" class="<?php echo $button->class; ?>" role="button" title="<?php echo $button->text; ?>" data-toggle="modal"><i class="icon-<?php echo $button->name; ?>"></i> <?php echo $button->text; ?></a>