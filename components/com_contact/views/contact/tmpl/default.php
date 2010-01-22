<?php
 /**
 * $Id$
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$cparams = JComponentHelper::getParams ('com_media');
?>

<div class="jcontact<?php echo $this->params->get('pageclass_sfx')?>">
	<?php if ($this->params->get('show_page_title', 1) && !$this->contact->params->get('popup') && $this->params->get('page_title') != $this->contact->name) : ?>
		<h2>
			<?php if ($this->escape($this->params->get('page_heading'))) :?>
				<?php echo $this->escape($this->params->get('page_heading')); ?>
			<?php else : ?>
				<?php echo $this->escape($this->params->get('page_title')); ?>
			<?php endif; ?>
		</h2>
	<?php endif; ?>
	<?php if ($this->contact->name && $this->contact->params->get('show_name')) : ?>
		<h3>
			<span class="jcontact-name"><?php echo $this->contact->name; ?></span>
		</h3>
	<?php endif; ?>
	<?php if ($this->contact->image && $this->contact->params->get('show_image')) : ?>
		<span class="jcontact-image">
			<?php echo JHtml::_('image', 'images/'.$this->contact->image, JText::_('Contact'), array('align' => 'middle')); ?>
		</span>
	<?php endif; ?>
<?php echo  JHtml::_('sliders.start', 'contact-slider'); ?>
	<?php echo JHtml::_('sliders.panel',JText::_('Contact_Details'), 'basic-detailss'); ?>
	<?php if ($this->params->get('show_contact_list') && count($this->contacts) > 1) : ?>
		<form action="<?php echo JRoute::_('index.php') ?>" method="post" name="selectForm" id="selectForm">
			<?php echo JText::_('CONTACT_SELECT_CONTACT'); ?>:
			<?php echo JHtml::_('select.genericlist',  $this->contacts, 'contact_id', 'class="inputbox" onchange="this.form.submit()"', 'id', 'name', $this->contact->id);?>
			<input type="hidden" name="option" value="com_contact" />
		</form>
	<?php endif; ?>



	<?php if ($this->contact->con_position && $this->contact->params->get('show_position')) : ?>
		<span class="jcontact-position"><?php echo $this->contact->con_position; ?></span>
	<?php endif; ?>



	<?php echo $this->loadTemplate('address'); ?>



	<?php if ($this->contact->params->get('allow_vcard')) : 	//TODO either reimplement vcard or delete this.?>
		<?php echo JText::_('Download information as a');?>
			<a href="<?php echo JURI::base(); ?>index.php?option=com_contact&amp;task=vcard&amp;contact_id=<?php echo $this->contact->id; ?>&amp;format=raw&amp;tmpl=component">
				<?php echo JText::_('VCard');?></a>
	<?php endif; ?>

	<?php if ($this->contact->params->get('show_email_form') && ($this->contact->email_to )) : ?>
		<?php echo $this->loadTemplate('form');  ?>
	<?php endif; ?>
	<?php if ($this->contact->params->get('show_links')) : ?>
	<?php echo $this->loadTemplate('links'); ?>			
	<?php endif; ?>
	<?php if ($this->contact->params->get('show_articles') &&  $this->contact->user_id) : ?>
	<?php echo JHtml::_('sliders.panel', JText::_('Contact_Articles'), 'display-articles'); ?>
		<?php echo $this->loadTemplate('articles'); ?>
	<?php endif; ?>
	<?php if ($this->contact->misc && $this->contact->params->get('show_misc')) : ?>
			<?php echo JHtml::_('sliders.panel', JText::_('Contact_Other_Information'), 'display-misc'); ?>
				<div class="jcontact-miscinfo">
					<span class="<?php echo $this->contact->params->get('marker_class'); ?>">
						<?php echo $this->contact->params->get('marker_misc'); ?>
					</span>
					<span class="jcontact-misc">
						<?php echo $this->contact->misc; ?>
					</span>
				</div>
	<?php endif; ?>
	<?php if ($this->contact->params->get('show_profile') &&  $this->contact->user_id) : ?>
	<?php echo JHtml::_('sliders.panel', JText::_('Contact_Profile'), 'display-profile'); ?>
		<?php echo $this->loadTemplate('profile'); ?>
	<?php endif; ?>
			<?php echo 	 JHtml::_('sliders.end'); ?>	
</div>
