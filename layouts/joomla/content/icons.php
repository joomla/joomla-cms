<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('bootstrap.framework');

$canEdit = $displayData['params']->get('access-edit');
$articleId = $displayData['item']->id;

?>

<div class="icons">
	<?php if (empty($displayData['print'])) : ?>

		<?php if ($canEdit || $displayData['params']->get('show_print_icon') || $displayData['params']->get('show_email_icon')) : ?>
			<div class="btn-group pull-right">
				<button class="btn dropdown-toggle" type="button" id="dropdownMenuButton-<?php echo $articleId; ?>" aria-label="<?php echo JText::_('JUSER_TOOLS'); ?>"
				data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					<span class="icon-cog" aria-hidden="true"></span>
					<span class="caret" aria-hidden="true"></span>
				</button>
				<?php // Note the actions class is deprecated. Use dropdown-menu instead. ?>
				<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton-<?php echo $articleId; ?>">
					<?php if ($displayData['params']->get('show_print_icon')) : ?>
						<li class="print-icon"> <?php echo JHtml::_('icon.print_popup', $displayData['item'], $displayData['params']); ?> </li>
					<?php endif; ?>
					<?php if ($displayData['params']->get('show_email_icon')) : ?>
						<li class="email-icon"> <?php echo JHtml::_('icon.email', $displayData['item'], $displayData['params']); ?> </li>
					<?php endif; ?>
					<?php if ($canEdit) : ?>
						<li class="edit-icon"> <?php echo JHtml::_('icon.edit', $displayData['item'], $displayData['params']); ?> </li>
					<?php endif; ?>
				</ul>
			</div>
		<?php endif; ?>

	<?php else : ?>

		<div class="pull-right">
			<?php echo JHtml::_('icon.print_screen', $displayData['item'], $displayData['params']); ?>
		</div>

	<?php endif; ?>
</div>
