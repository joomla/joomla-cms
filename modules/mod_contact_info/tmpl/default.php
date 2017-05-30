<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_contact_info
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<div class="contact_info<?php echo $moduleclass_sfx ?>">

	<?php if ($params->get('name_linked') == "1") : ?>
		<?php $url = JRoute::_("index.php?option=com_contact&view=contact&id=" . $dataContact->id); ?>
		<a href="<?php echo $url; ?>"><strong><?php echo $dataContact->name; ?></strong></a>
		<br>
	<?php else : ?>
		<strong> <?php echo $dataContact->name; ?></strong>
		<br>
	<?php endif ?>
	<?php if ($params->get('position') == "1" && !empty($dataContact->con_position)) : ?>
		<?php echo $dataContact->con_position; ?>
		<br>
	<?php endif ?>
	<?php if ($params->get('address') == "1" && !empty($dataContact->address)) : ?>
			<?php echo $dataContact->address; ?>
			<br>
			<?php if(!empty($dataContact->suburb)) : ?>
				<span class="contact-suburb">
					<?php echo $dataContact->suburb; ?>
					<br>
				</span>
			<?php endif ?>
			<?php if(!empty($dataContact->state)) : ?>
				<span class="contact-state">
					<?php echo $dataContact->state; ?>
					<br>
				</span>
			<?php endif ?>
			<?php if(!empty($dataContact->country)) : ?>
				<span class="contact-country">
					<?php echo $dataContact->country; ?>
					<br>
				</span>
			<?php endif ?>
	<?php endif ?>
	<?php if ($params->get('postcode') == "1" && !empty($dataContact->postcode)) : ?>
		<span class="contact-postcode">
			<?php echo $dataContact->postcode; ?>
			<br>
		</span>
	<?php endif ?>
	<?php if ($params->get('telephone') == "1" && !empty($dataContact->telephone)) : ?>
		<span class="contact-telephone">
			<?php echo $dataContact->telephone; ?>
			<br>
		</span>
	<?php endif ?>
	<?php if ($params->get('email') == "1" && !empty($dataContact->email_to)) : ?>
		<?php if ($params->get('email_visible') == "1") : ?>
			<?php echo JHtml::_('email.cloak', $dataContact->email_to); ?>
			<br>
		<?php else : ?>
			<?php echo JHtml::_('email.cloak', $dataContact->email_to, 1, JText::_('Email'), 0); ?>
			<br>
		<?php endif ?>
	<?php endif ?>
</div>
