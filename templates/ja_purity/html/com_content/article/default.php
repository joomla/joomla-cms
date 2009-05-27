<?php // no direct access
defined('_JEXEC') or die; ?>
<?php if ($this->params->get('show_page_title', 1) && $this->params->get('page_title') != $this->article->title) : ?>
<div class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"><?php echo $this->escape($this->params->get('page_title')); ?></div>
<?php endif; ?>
<?php if (($this->user->authorize('com_content', 'edit', 'content', 'all') || $this->user->authorize('com_content', 'edit', 'content', 'own')) && !$this->print) : ?>
	<div class="contentpaneopen_edit<?php echo $this->params->get('pageclass_sfx'); ?>" >
		<?php echo JHtml::_('icon.edit', $this->article, $this->params, $this->access); ?>
	</div>
<?php endif; ?>

<?php if ($this->params->get('show_title',1)) : ?>
<h2 class="contentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('link_titles') && $this->article->readmore_link != '') : ?>
	<a href="<?php echo $this->article->readmore_link; ?>" class="contentpagetitle<?php echo $this->params->get('pageclass_sfx'); ?>">
		<?php echo $this->escape($this->article->title); ?>
	</a>
	<?php else : ?>
		<?php echo $this->article->title; ?>
	<?php endif; ?>
</h2>
<?php endif; ?>

<?php  if (!$this->params->get('show_intro')) :
	echo $this->article->event->afterDisplayTitle;
endif; ?>

<?php
if (
($this->params->get('show_create_date'))
|| (($this->params->get('show_author')) && ($this->article->author != ""))
|| (($this->params->get('show_section') && $this->article->sectionid) || ($this->params->get('show_category') && $this->article->catid))
|| ($this->params->get('show_print_icon') || $this->params->get('show_email_icon'))
|| ($this->params->get('show_url') && $this->article->urls)
) :
?>
<div class="article-tools">
	<div class="article-meta">
	<?php if ($this->params->get('show_create_date')) : ?>
		<span class="createdate">
			<?php echo JHtml::_('date', $this->article->created, JText::_('DATE_FORMAT_LC2')) ?>
		</span>
	<?php endif; ?>

	<?php if (($this->params->get('show_author')) && ($this->article->author != "")) : ?>
		<span class="createby">
			<?php JText::printf(($this->article->created_by_alias ? $this->article->created_by_alias : $this->article->author)); ?>
		</span>
	<?php endif; ?>

	<?php if (($this->params->get('show_section') && $this->article->sectionid) || ($this->params->get('show_category') && $this->article->catid)) : ?>
		<?php if ($this->params->get('show_section') && $this->article->sectionid && isset($this->article->section)) : ?>
		<span class="article-section">
			<?php if ($this->params->get('link_section')) : ?>
				<?php echo '<a href="'.JRoute::_(ContentHelperRoute::getSectionRoute($this->article->sectionid)).'">'; ?>
			<?php endif; ?>
			<?php echo $this->article->section; ?>
			<?php if ($this->params->get('link_section')) : ?>
				<?php echo '</a>'; ?>
			<?php endif; ?>
				<?php if ($this->params->get('show_category')) : ?>
				<?php echo ' - '; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>
		<?php if ($this->params->get('show_category') && $this->article->catid) : ?>
		<span class="article-section">
			<?php if ($this->params->get('link_category')) : ?>
				<?php echo '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->article->catslug, $this->article->sectionid)).'">'; ?>
			<?php endif; ?>
			<?php echo $this->article->category; ?>
			<?php if ($this->params->get('link_category')) : ?>
				<?php echo '</a>'; ?>
			<?php endif; ?>
		</span>
		<?php endif; ?>
	<?php endif; ?>
	</div>

	<?php if ($this->params->get('show_print_icon') || $this->params->get('show_email_icon')) : ?>
	<div class="buttonheading">
		<?php if (!$this->print) : ?>
			<?php if ($this->params->get('show_email_icon')) : ?>
			<span>
			<?php echo JHtml::_('icon.email',  $this->article, $this->params, $this->access); ?>
			</span>
			<?php endif; ?>

			<?php if ($this->params->get('show_print_icon')) : ?>
			<span>
			<?php echo JHtml::_('icon.print_popup',  $this->article, $this->params, $this->access); ?>
			</span>
			<?php endif; ?>
		<?php else : ?>
			<span>
			<?php echo JHtml::_('icon.print_screen',  $this->article, $this->params, $this->access); ?>
			</span>
		<?php endif; ?>
	</div>
	<?php endif; ?>

	<?php if ($this->params->get('show_url') && $this->article->urls) : ?>
		<span class="article-url">
			<a href="http://<?php echo $this->article->urls ; ?>" target="_blank">
				<?php echo $this->article->urls; ?></a>
		</span>
	<?php endif; ?>
</div>
<?php endif; ?>

<?php echo $this->article->event->beforeDisplayContent; ?>

<div class="article-content">
<?php if (isset ($this->article->toc)) : ?>
	<?php echo $this->article->toc; ?>
<?php endif; ?>
<?php echo $this->article->text; ?>
</div>

<?php if (intval($this->article->modified) !=0 && $this->params->get('show_modify_date')) : ?>
	<span class="modifydate">
		<?php echo JText::sprintf('LAST_UPDATED2', JHtml::_('date', $this->article->modified, JText::_('DATE_FORMAT_LC2'))); ?>
	</span>
<?php endif; ?>

<span class="article_separator">&nbsp;</span>
<?php echo $this->article->event->afterDisplayContent; ?>
