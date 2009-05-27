<?php // @version $Id: default.php 11307 2008-11-24 00:21:48Z ian $
defined('_JEXEC') or die;
$cparams = JComponentHelper::getParams ('com_media');
?>

<?php if ($this->params->get('show_page_title',1) && $this->params->get('page_title') != $this->contact->name) : ?>
<h1 class="componentheading<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php echo $this->escape($this->params->get('page_title')); ?>
</h1>
<?php endif; ?>

<div class="contact<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_contact_list') && count($this->contacts) > 1) : ?>
	<form method="post" name="selectForm" id="selectForm">
		<?php echo JText::_('Select Contact'); ?>
		<br />
		<?php echo JHtml::_('select.genericlist', $this->contacts, 'contact_id', 'class="inputbox" onchange="this.form.submit()"', 'id', 'name', $this->contact->id); ?>
		<input type="hidden" name="option" value="com_contact" />
	</form>
	<?php endif; ?>

	<?php if ($this->contact->name && $this->contact->params->get('show_name')) : ?>
	<p>
		<?php echo $this->contact->name; ?>
	</p>
	<?php endif; ?>

	<?php if ($this->contact->con_position && $this->contact->params->get('show_position')) : ?>
	<p>
		<?php echo $this->contact->con_position; ?>
	</p>
	<?php endif; ?>

	<?php if ($this->contact->image && $this->contact->params->get('show_image')) : ?>
	<div style="float: right;">
		<?php echo JHtml::_('image', 'images/stories' . '/'.$this->contact->image, JText::_('Contact'), array('align' => 'middle')); ?>
	</div>
	<?php endif; ?>

	<?php echo $this->loadTemplate('address'); ?>

	<?php if ($this->contact->params->get('allow_vcard')) : ?>
	<p>
		<?php echo JText::_('Download information as a'); ?>
		<a href="index.php?option=com_contact&amp;task=vcard&amp;contact_id=<?php echo $this->contact->id; ?>&amp;format=raw">
			<?php echo JText::_('VCard'); ?></a>
	</p>
	<?php endif; ?>

	<?php if ($this->contact->params->get('show_email_form')) :
		echo $this->loadTemplate('form');
	endif; ?>
</div>
