<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JHtml::_('bootstrap.tooltip');

$tooltip = $displayData['tooltip'];
$legacy  = $displayData['legacy'];

?>
<?php if ($legacy) : ?>
	<span class="hasTooltip" title="<?php echo JHtml::tooltipText($tooltip . '', 0); ?>">
		<?php echo JHtml::_('image', 'system/checked_out.png', null, null, true); ?>
	</span>
	<?php echo JText::_('JLIB_HTML_CHECKED_OUT'); ?>
<?php else : ?>
	<span class="hasTooltip icon-lock" title="<?php echo JHtml::tooltipText($tooltip . '', 0); ?>"></span>
	<?php echo JText::_('JLIB_HTML_CHECKED_OUT'); ?>
<?php endif; ?>
