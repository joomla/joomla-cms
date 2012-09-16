<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.framework');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<?php if (empty($this->items)) : ?>
	<p> <?php echo JText::_('COM_CONTACT_NO_ARTICLES'); ?>	 </p>
<?php else : ?>

	<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" name="adminForm" id="adminForm">
		<?php if ($this->params->get('filter_field') != 'hide') : ?>
			<fieldset class="filters">
				<legend class="element-invisible">
					<?php echo JText::_('JGLOBAL_FILTER_LABEL'); ?>
				</legend>

				<div class="filter-search">
					<label class="filter-search-lbl" for="filter-search"><?php echo JText::_('COM_CONTACT_'.$this->params->get('filter_field').'_FILTER_LABEL').'&#160;'; ?></label>
					<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('list.filter')); ?>" class="inputbox" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_CONTACT_FILTER_SEARCH_DESC'); ?>" />
				</div>
		<?php endif; ?>

		<?php if ($this->params->get('show_pagination_limit')) : ?>
			<div class="display-limit">
				<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;
				<?php echo $this->pagination->getLimitBox(); ?>
			</div>
		<?php endif; ?>

		<?php if ($this->params->get('filter_field') != 'hide') :?>
			</fieldset>
		<?php endif; ?>

		<ul class="category list-striped">
			<?php foreach ($this->items as $i => $item) : ?>

				<?php if (in_array($item->access, $this->user->getAuthorisedViewLevels())) : ?>
					<?php if ($this->items[$i]->state == 0) : ?>
						<li class="system-unpublished cat-list-row<?php echo $i % 2; ?>">
					<?php else: ?>
						<li class="cat-list-row<?php echo $i % 2; ?>" >
					<?php endif; ?>

						<p class="pull-right">
							<small>
							<?php if ($this->params->get('show_telephone_headings') AND !empty($item->telephone)) : ?>
								<?php echo $item->telephone; ?><br/>
							<?php endif; ?>

							<?php if ($this->params->get('show_mobile_headings') AND !empty ($item->mobile)) : ?>
									<?php echo $item->mobile; ?><br/>
							<?php endif; ?>

							<?php if ($this->params->get('show_fax_headings') AND !empty($item->fax) ) : ?>
								<?php echo $item->fax; ?><br/>
							<?php endif; ?>
							</small>
						</p>
						
						<p class="pull-right">
							<small>
							<?php if ($this->params->get('show_suburb_headings') AND !empty($item->suburb)) : ?>
								<?php echo $item->suburb; ?></br>
							<?php endif; ?>

							<?php if ($this->params->get('show_state_headings') AND !empty($item->state)) : ?>
								<?php echo $item->state; ?><br/>
							<?php endif; ?>

							<?php if ($this->params->get('show_country_headings') AND !empty($item->country)) : ?>
								<?php echo $item->country; ?><br/>
							<?php endif; ?>
							</small>
						</p>
						<p >
						<strong class="list-title">
							<a href="<?php echo JRoute::_(ContactHelperRoute::getContactRoute($item->slug, $item->catid)); ?>">
								<?php echo $item->name; ?></a>
							<?php if ($this->items[$i]->published == 0): ?>
								<span class="label label-warning">Unpublished</span>
							<?php endif; ?>

						</strong><br/>
						<?php if ($this->params->get('show_position_headings')) : ?>
								<?php echo $item->con_position; ?><br/>
						<?php endif; ?>

						<?php if ($this->params->get('show_email_headings')) : ?>
								<?php echo $item->email_to; ?>
						<?php endif; ?></div>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>


		<?php if ($this->params->get('show_pagination')) : ?>
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
