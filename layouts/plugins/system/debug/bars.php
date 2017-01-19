<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$bars  = is_array($displayData['bars']) ? $displayData['bars'] : array();
$class = isset($displayData['class']) ? $displayData['class'] : '';
$id    = isset($displayData['id']) ? $displayData['id'] : null;

?>

<div class="progress dbg-bars dbg-bars-<?php echo $class; ?>">
	<?php foreach ($bars as $i => $bar) : ?>
		<?php if (isset($bar->pre) && $bar->pre) : ?>
			<div class="dbg-bar-spacer" style="width:<?php echo $bar->pre; ?>%;"></div>
		<?php endif; ?>
		<?php $barClass = trim('bar dbg-bar progress-bar ' . (isset($bar->class) ? $bar->class : '')); ?>
		<?php if ($id !== null && $i == $id) : ?>
			<?php $barClass .= ' dbg-bar-active'; ?>
		<?php endif; ?>
		<?php $tip = ''; ?>
		<?php if (isset($bar->tip) && $bar->tip) : ?>
			<?php $barClass .= ' hasTooltip'; ?>
			<?php $tip = JHtml::tooltipText($bar->tip, '', 0); ?>
		<?php endif; ?>
		<a class="bar dbg-bar <?php echo $barClass; ?>"
			title="<?php echo $tip; ?>"
			style="width: <?php echo $bar->width; ?>%;"
			href="#dbg-<?php echo $class, '-', ($i + 1); ?>"></a>
	<?php endforeach; ?>
</div>
