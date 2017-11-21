<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JHtml::_('bootstrap.framework');

$canEdit   = $displayData['params']->get('access-edit');
$articleId = $displayData['item']->id;

?>

<div class="icons">
	<?php if (empty($displayData['print'])) : ?>

		<?php if ($canEdit || $displayData['params']->get('show_print_icon') || $displayData['params']->get('show_email_icon')) : ?>
			<div class="btn-group float-right">
				<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton-<?php echo $articleId; ?>" aria-label="<?php echo JText::_('JUSER_TOOLS'); ?>"
					data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span class="fa fa-cog" aria-hidden="true"></span>
				</button>
				<div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton-<?php echo $articleId; ?>">
					<?php if ($displayData['params']->get('show_print_icon')) : ?>
						<?php echo JHtml::_('icon.print_popup', $displayData['item'], $displayData['params']); ?>
					<?php endif; ?>
					<?php if ($displayData['params']->get('show_email_icon')) : ?>
						<?php echo JHtml::_('icon.email', $displayData['item'], $displayData['params']); ?>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
						<?php echo JHtml::_('icon.edit', $displayData['item'], $displayData['params']); ?>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

	<?php else : ?>

		<div class="float-right">
			<?php echo JHtml::_('icon.print_screen', $displayData['item'], $displayData['params']); ?>
		</div>

	<?php endif; ?>
</div>
