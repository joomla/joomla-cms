<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$params = $displayData['params'];
$item   = $displayData['item'];
$print  = $displayData['print'];

// Check if associations are implemented. If they are, define the parameter.
$assocParam = (JLanguageAssociations::isEnabled() && $params->get('show_associations'));
JHtml::_('behavior.caption');
?>
<?php if ($params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1> <?php echo $params->get('page_heading'); ?> </h1>
	</div>
<?php endif; ?>

<?php // Todo Not that elegant would be nice to group the params ?>
<?php $useDefList = ($params->get('show_modify_date') || $params->get('show_publish_date') || $params->get('show_create_date')
	|| $params->get('show_hits') || $params->get('show_category') || $params->get('show_parent_category') || $params->get('show_author') || $assocParam); ?>

<?php if (!$useDefList && $print) : ?>
	<div id="pop-print" class="btn hidden-print">
		<?php // echo JHtml::_('icon.print_screen', $item, $params); ?>
	</div>
	<div class="clearfix"> </div>
<?php endif; ?>
<?php if ($params->get('show_title') || $params->get('show_author')) : ?>
	<div class="page-header">
		<?php if ($params->get('show_title')) : ?>
			<h2 itemprop="headline">
				<?php echo $item->title; ?>
			</h2>
		<?php endif; ?>
		<?php if ($item->state == 0) : ?>
			<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
		<?php endif; ?>
		<?php if (strtotime($item->publish_up) > strtotime(JFactory::getDate())) : ?>
			<span class="label label-warning"><?php echo JText::_('JNOTPUBLISHEDYET'); ?></span>
		<?php endif; ?>
		<?php if ((strtotime($item->publish_down) < strtotime(JFactory::getDate())) && $item->publish_down != JFactory::getDbo()->getNullDate()) : ?>
			<span class="label label-warning"><?php echo JText::_('JEXPIRED'); ?></span>
		<?php endif; ?>
	</div>
<?php endif; ?>
