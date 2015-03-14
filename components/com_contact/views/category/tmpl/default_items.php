<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<?php if (empty($this->items)) : ?>
	<p> <?php echo JText::_('COM_CONTACT_NO_CONTACTS'); ?>	 </p>
<?php else : ?>

	<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
	<?php if ($this->params->get('filter_field') != 'hide' || $this->params->get('show_pagination_limit')) :?>
	<fieldset class="filters btn-toolbar">
		<?php if ($this->params->get('filter_field') != 'hide') :?>
			<div class="btn-group">
				<label class="filter-search-lbl element-invisible" for="filter-search"><span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span><?php echo JText::_('COM_CONTACT_FILTER_LABEL') . '&#160;'; ?></label>
				<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_CONTACT_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_CONTACT_FILTER_SEARCH_DESC'); ?>" />
			</div>
		<?php endif; ?>

		<?php if ($this->params->get('show_pagination_limit')) : ?>
			<div class="btn-group pull-right">
				<label for="limit" class="element-invisible">
					<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
				</label>
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		<?php endif; ?>
	</fieldset>
	<?php endif; ?>

		<ul class="category list-striped">
			<?php foreach ($this->items as $i => $item) : ?>

				<?php if (in_array($item->access, $this->user->getAuthorisedViewLevels())) : ?>
					<?php if ($this->items[$i]->published == 0) : ?>
						<li class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
					<?php else: ?>
						<li class="cat-list-row<?php echo $i % 2; ?>" >
					<?php endif; ?>

						<span class="pull-right">
							<?php if ($this->params->get('show_telephone_headings') AND !empty($item->telephone)) : ?>
								<?php echo JTEXT::sprintf('COM_CONTACT_TELEPHONE_NUMBER', $item->telephone); ?><br />
							<?php endif; ?>

							<?php if ($this->params->get('show_mobile_headings') AND !empty ($item->mobile)) : ?>
									<?php echo JTEXT::sprintf('COM_CONTACT_MOBILE_NUMBER', $item->mobile); ?><br />
							<?php endif; ?>

							<?php if ($this->params->get('show_fax_headings') AND !empty($item->fax) ) : ?>
								<?php echo JTEXT::sprintf('COM_CONTACT_FAX_NUMBER', $item->fax); ?><br />
							<?php endif; ?>
					</span>

					<p>
						<div class="list-title">
							<a href="<?php echo JRoute::_(ContactHelperRoute::getContactRoute($item->slug, $item->catid)); ?>">
								<?php echo $item->name; ?></a>
							<?php if ($this->items[$i]->published == 0) : ?>
								<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
							<?php endif; ?>
						</div>
						<?php if ($this->params->get('show_position_headings')) : ?>
								<?php echo $item->con_position; ?><br />
						<?php endif; ?>
						<?php if ($this->params->get('show_email_headings')) : ?>
								<?php echo $item->email_to; ?>
						<?php endif; ?>
						<?php if ($this->params->get('show_suburb_headings') AND !empty($item->suburb)) : ?>
							<?php echo $item->suburb . ', '; ?>
						<?php endif; ?>

						<?php if ($this->params->get('show_state_headings') AND !empty($item->state)) : ?>
							<?php echo $item->state . ', '; ?>
						<?php endif; ?>

						<?php if ($this->params->get('show_country_headings') AND !empty($item->country)) : ?>
							<?php echo $item->country; ?><br />
						<?php endif; ?>
					</p>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>

		<?php if ($this->params->get('show_pagination', 2)) : ?>
		<div class="pagination">
			<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<p class="counter">
				<?php echo $this->pagination->getPagesCounter(); ?>
			</p>
			<?php endif; ?>
			<?php echo $this->pagination->getPagesLinks(); ?>
		</div>
		<?php endif; ?>
		<div>
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		</div>
</form>
<?php endif; ?>
