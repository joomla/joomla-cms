<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.tooltip');

$tooltip = $displayData['tooltip'];

// @deprecated  4.0  The legacy icon flag will be removed from this layout in 4.0
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
