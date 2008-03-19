<?php // @version $Id$
defined('_JEXEC') or die('Restricted access');
?>

<?php if ($this->user->authorize('com_content', 'edit', 'content', 'all') || $this->user->authorize('com_content', 'edit', 'content', 'own')) : ?>
<div class="contentpaneopen_edit<?php echo $this->item->params->get('pageclass_sfx'); ?>">
	<?php echo JHTML::_('icon.edit', $this->item, $this->item->params, $this->access); ?>
</div>
<?php endif; ?>

<?php if ($this->item->params->get('show_title')) : ?>
<h2 class="contentheading<?php echo $this->item->params->get('pageclass_sfx'); ?>">
	<?php if ($this->item->params->get('link_titles') && $this->item->readmore_link != '') : ?>
		<a href="<?php echo $this->item->readmore_link; ?>" class="contentpagetitle<?php echo $this->item->params->get('pageclass_sfx'); ?>">
			<?php echo $this->escape($this->item->title); ?></a>
	<?php else :
		echo $this->escape($this->item->title);
	endif; ?>
</h2>
<?php endif; ?>

<?php if (!$this->item->params->get('show_intro')) :
	echo $this->item->event->afterDisplayTitle;
endif; ?>

<?php if ($this->item->params->get('show_pdf_icon') || $this->item->params->get('show_print_icon') || $this->item->params->get('show_email_icon')) : ?>
<p class="buttonheading">
	<img src="<?php echo $this->baseurl ?>/templates/beez/images/trans.gif" alt="<?php echo JText::_('attention open in a new window'); ?>" />
	<?php if ($this->item->params->get('show_pdf_icon')) :
		echo JHTML::_('icon.pdf', $this->item, $this->item->params, $this->access);
	endif;
	if ($this->item->params->get('show_print_icon')) :
		echo JHTML::_('icon.print_popup', $this->item, $this->item->params, $this->access);
	endif;
	if ($this->item->params->get('show_email_icon')) :
		echo JHTML::_('icon.email', $this->item, $this->item->params, $this->access);
	endif; ?>
</p>
<?php endif; ?>

<?php if (($this->item->params->get('show_section') && $this->item->sectionid) || ($this->item->params->get('show_category') && $this->item->catid)) : ?>
<p class="pageinfo">
    <?php if ($this->item->params->get('show_section') && $this->item->sectionid && isset($this->item->section)) : ?>
        <span>
            <?php if ($this->item->params->get('link_section')) : ?>
                <?php echo '<a href="'.JRoute::_(ContentHelperRoute::getSectionRoute($this->item->sectionid)).'">'; ?>
            <?php endif; ?>
            <?php echo $this->item->section; ?>
            <?php if ($this->item->params->get('link_section')) : ?>
                <?php echo '</a>'; ?>
            <?php endif; ?>
			<?php if ($this->item->params->get('show_category')) : ?>
                <?php echo ' - '; ?>
            <?php endif; ?>
        </span>
        <?php endif; ?>
        <?php if ($this->item->params->get('show_category') && $this->item->catid) : ?>
        <span>
            <?php if ($this->item->params->get('link_category')) : ?>
                <?php echo '<a href="'.JRoute::_(ContentHelperRoute::getCategoryRoute($this->item->catslug, $this->item->sectionid)).'">'; ?>
            <?php endif; ?>
            <?php echo $this->item->category; ?>
            <?php if ($this->item->params->get('link_category')) : ?>
                <?php echo '</a>'; ?>
            <?php endif; ?>
        </span>
	<?php endif; ?>
</p>
<?php endif; ?>

<?php if ((!empty ($this->item->modified) && $this->item->params->get('show_modify_date')) || ($this->item->params->get('show_author') && ($this->item->author != "")) || ($this->item->params->get('show_create_date'))) : ?>
<p class="iteminfo">
	<?php if (!empty ($this->item->modified) && $this->item->params->get('show_modify_date')) : ?>
	<span class="modifydate">
		<?php echo JText::_('Last Updated').' ('.JHTML::_('date', $this->item->modified, JText::_('DATE_FORMAT_LC2')).')'; ?>
	</span>
	<?php endif; ?>

	<?php if (($this->item->params->get('show_author')) && ($this->item->author != "")) : ?>
	<span class="createdby">
		<?php JText::printf('Written by', ($this->item->created_by_alias ? $this->item->created_by_alias : $this->item->author)); ?>
	</span>
	<?php endif; ?>

	<?php if ($this->item->params->get('show_create_date')) : ?>
	<span class="createdate">
		<?php echo JHTML::_('date', $this->item->created, JText::_('DATE_FORMAT_LC2')); ?>
	</span>
	<?php endif; ?>
</p>
<?php endif; ?>

<?php echo $this->item->event->beforeDisplayContent; ?>

<?php if ($this->item->params->get('show_url') && $this->item->urls) : ?>
<span class="small">
	<a href="<?php echo $this->item->urls; ?>" target="_blank">
		<?php echo $this->item->urls; ?></a>
</span>
<?php endif; ?>

<?php if (isset ($this->item->toc)) :
	echo $this->item->toc;
endif; ?>

<?php echo JFilterOutput::ampReplace($this->item->text);  ?>

<?php if ($this->item->params->get('show_readmore') && $this->item->readmore) : ?>
<p>
	<a href="<?php echo $this->item->readmore_link; ?>" class="readon<?php echo $this->item->params->get('pageclass_sfx'); ?>">
		<?php if ($this->item->readmore_register) :
			echo JText::_('Register to read more...');
		elseif ($readmore = $this->item->params->get('readmore')) :
			echo $readmore;
		else :
			echo JText::sprintf('Read more', $this->item->title);
		endif; ?></a>
</p>
<?php endif; ?>

<?php echo $this->item->event->afterDisplayContent;
