<?php
/**
 * @package     Joomla.Cms
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$microdata = $displayData['item']->metadata;
$location  = $microdata->get('microdata_event_location');
$startDate = $microdata->get('microdata_event_startDate');
$endDate   = $microdata->get('microdata_event_endDate');
?>
<dd class="location">
	<?php echo JText::sprintf('JFIELD_MICRODATA_EVENT_LOCATION_LABEL') . ':';?>
	<span data-sd="location">
		<?php echo $location;?>
	</span>
</dd>
<dd class="startDate">
	<span class="icon-calendar"></span>
	<time datetime="<?php echo JHtml::_('date', $startDate, 'c'); ?>" data-sd="startDate">
		<?php echo JText::sprintf('JFIELD_MICRODATA_EVENT_STARTDATE_LABEL') . ': ' . JHtml::_('date', $startDate, JText::_('DATE_FORMAT_LC2')); ?>
	</time>
</dd>
<dd class="endDate">
	<span class="icon-calendar"></span>
	<time datetime="<?php echo JHtml::_('date', $endDate, 'c'); ?>" data-sd="endDate">
		<?php echo JText::sprintf('JFIELD_MICRODATA_EVENT_ENDDATE_LABEL') . ': ' . JHtml::_('date', $endDate, JText::_('DATE_FORMAT_LC2')); ?>
	</time>
</dd>