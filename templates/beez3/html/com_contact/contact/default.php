<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$cparams = JComponentHelper::getParams('com_media');
?>

<div class="contact<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	<?php endif; ?>
	<?php if ($this->contact->name && $this->params->get('show_name')) : ?>
		<div class="page-header">
			<h2>
				<span class="contact-name"><?php echo $this->contact->name; ?></span>
			</h2>
		</div>
	<?php endif;  ?>
	<?php if ($this->params->get('show_contact_category') === 'show_no_link') : ?>
		<h3>
			<span class="contact-category"><?php echo $this->contact->category_title; ?></span>
		</h3>
	<?php endif; ?>
	<?php if ($this->params->get('show_contact_category') === 'show_with_link') : ?>
		<?php $contactLink = ContactHelperRoute::getCategoryRoute($this->contact->catid);?>
		<h3>
			<span class="contact-category"><a href="<?php echo $contactLink; ?>">
				<?php echo $this->escape($this->contact->category_title); ?></a>
			</span>
		</h3>
	<?php endif; ?>

	<?php echo $this->item->event->afterDisplayTitle; ?>

	<?php if ($this->params->get('show_contact_list') && count($this->contacts) > 1) : ?>
		<form action="#" method="get" name="selectForm" id="selectForm">
			<label for="select_contact"><?php echo JText::_('COM_CONTACT_SELECT_CONTACT'); ?></label>
			<?php echo JHtml::_('select.genericlist', $this->contacts, 'select_contact', 'class="inputbox" onchange="document.location.href = this.value"', 'link', 'name', $this->contact->link); ?>
		</form>
	<?php endif; ?>

	<?php if (!empty($this->item->tags->itemTags) && $this->params->get('show_tags', 1)) : ?>
		<?php $this->item->tagLayout = new JLayoutFile('joomla.content.tags'); ?>
		<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
	<?php endif; ?>

	<?php echo $this->item->event->beforeDisplayContent; ?>

	<?php  if ($this->params->get('presentation_style') === 'sliders') : ?>
		<?php echo JHtml::_('sliders.start', 'panel-sliders', array('useCookie' => '1')); ?>
		<?php echo JHtml::_('sliders.panel', JText::_('COM_CONTACT_DETAILS'), 'basic-details'); ?>
	<?php endif; ?>
	<?php  if ($this->params->get('presentation_style') === 'tabs') : ?>
		<?php echo JHtmlTabs::start('tabs', array('useCookie' => '1')); ?>
		<?php echo JHtmlTabs::panel(JText::_('COM_CONTACT_DETAILS'), 'basic-details'); ?>

	<?php endif; ?>
	<?php if ($this->params->get('presentation_style') === 'plain'):?>
		<?php  echo '<h3>' . JText::_('COM_CONTACT_DETAILS') . '</h3>';  ?>
	<?php endif; ?>

	<?php if ($this->contact->image && $this->params->get('show_image')) : ?>
		<div class="thumbnail pull-right">
			<?php echo JHtml::_('image', $this->contact->image, htmlspecialchars($this->contact->name,  ENT_QUOTES, 'UTF-8'), array('style' => 'vertical-align: middle;')); ?>
		</div>
	<?php endif; ?>

	<?php if ($this->contact->con_position && $this->params->get('show_position')) : ?>
		<dl class="contact-position dl-horizontal">
		<dt><?php echo JText::_('COM_CONTACT_POSITION'); ?>:</dt>
			<dd>
				<?php echo $this->contact->con_position; ?>
			</dd>
		</dl>
	<?php endif; ?>

	<?php echo $this->loadTemplate('address'); ?>

	<?php if ($this->params->get('allow_vcard')) :	?>
		<?php echo JText::_('COM_CONTACT_DOWNLOAD_INFORMATION_AS');?>
			<a href="<?php echo JRoute::_('index.php?option=com_contact&amp;view=contact&amp;id='.$this->contact->id . '&amp;format=vcf'); ?>">
			<?php echo JText::_('COM_CONTACT_VCARD');?></a>
	<?php endif; ?>

	<?php if (($this->contact->email_to || $this->contact->user_id) && $this->params->get('show_email_form')) : ?>

		<?php if ($this->params->get('presentation_style') === 'sliders') : ?>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_CONTACT_EMAIL_FORM'), 'display-form'); ?>
		<?php endif; ?>
		<?php if ($this->params->get('presentation_style') === 'tabs') : ?>
			<?php echo JHtmlTabs::panel(JText::_('COM_CONTACT_EMAIL_FORM'), 'display-form'); ?>
		<?php endif; ?>
		<?php if ($this->params->get('presentation_style') === 'plain'):?>
			<?php  echo '<h3>'. JText::_('COM_CONTACT_EMAIL_FORM').'</h3>';  ?>
		<?php endif; ?>
		<?php  echo $this->loadTemplate('form');  ?>

	<?php endif; ?>

	<?php if ($this->params->get('show_links')) : ?>
		<?php echo $this->loadTemplate('links'); ?>
	<?php endif; ?>

	<?php if ($this->contact->user_id && $this->contact->articles && $this->params->get('show_articles')) : ?>

		<?php if ($this->params->get('presentation_style') === 'sliders') :
			echo JHtml::_('sliders.panel', JText::_('JGLOBAL_ARTICLES'), 'display-articles'); ?>
		<?php endif; ?>
		<?php if ($this->params->get('presentation_style') === 'tabs') : ?>
			<?php echo JHtmlTabs::panel(JText::_('JGLOBAL_ARTICLES'), 'display-articles'); ?>
		<?php endif; ?>
		<?php if  ($this->params->get('presentation_style') === 'plain'):?>
			<?php echo '<h3>'. JText::_('JGLOBAL_ARTICLES').'</h3>'; ?>
		<?php endif; ?>

		<?php echo $this->loadTemplate('articles'); ?>

	<?php endif; ?>

	<?php if ($this->contact->user_id && $this->params->get('show_profile') && JPluginHelper::isEnabled('user', 'profile')) : ?>

		<?php if ($this->params->get('presentation_style') === 'sliders') :
			echo JHtml::_('sliders.panel', JText::_('COM_CONTACT_PROFILE'), 'display-profile'); ?>
		<?php endif; ?>
		<?php if ($this->params->get('presentation_style') === 'tabs') : ?>
			<?php echo JHtmlTabs::panel(JText::_('COM_CONTACT_PROFILE'), 'display-profile'); ?>
		<?php endif; ?>
		<?php if ($this->params->get('presentation_style') === 'plain'):?>
			<?php echo '<h3>'. JText::_('COM_CONTACT_PROFILE').'</h3>'; ?>
		<?php endif; ?>

		<?php echo $this->loadTemplate('profile'); ?>

	<?php endif; ?>

	<?php if ($this->contactUser && $this->params->get('show_user_custom_fields')) : ?>
		<?php echo $this->loadTemplate('user_custom_fields'); ?>
	<?php endif; ?>

	<?php if ($this->contact->misc && $this->params->get('show_misc')) : ?>

		<?php if ($this->params->get('presentation_style') === 'sliders') :
			echo JHtml::_('sliders.panel', JText::_('COM_CONTACT_OTHER_INFORMATION'), 'display-misc'); ?>
		<?php endif; ?>
		<?php if ($this->params->get('presentation_style') === 'tabs') : ?>
			<?php echo JHtmlTabs::panel(JText::_('COM_CONTACT_OTHER_INFORMATION'), 'display-misc'); ?>
		<?php endif; ?>
		<?php if ($this->params->get('presentation_style') === 'plain'):?>
			<?php echo '<h3>'. JText::_('COM_CONTACT_OTHER_INFORMATION').'</h3>'; ?>
		<?php endif; ?>

		<div class="contact-miscinfo">
			<dl class="dl-horizontal">
				<dt>
					<span class="<?php echo $this->params->get('marker_class'); ?>">
						<?php echo $this->params->get('marker_misc'); ?>
					</span>
				</dt>
				<dd>
					<span class="contact-misc">
						<?php echo $this->contact->misc; ?>
					</span>
				</dd>
			</dl>
		</div>
	<?php endif; ?>

	<?php if ($this->params->get('presentation_style') === 'sliders') :
		echo JHtml::_('sliders.end');
	endif; ?>

	<?php echo $this->item->event->afterDisplayContent; ?>
</div>
