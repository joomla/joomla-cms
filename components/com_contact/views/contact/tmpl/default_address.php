<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/* marker_class: Class based on the selection of text, none, or icons
 * jicon-text, jicon-none, jicon-icon
 */
?>
<?php if (($this->contact->params->get('address_check') > 0) &&  ($this->contact->address || $this->contact->suburb  || $this->contact->state || $this->contact->country || $this->contact->postcode)) : ?>
<div class="jcontact-address">

<?php if ($this->contact->params->get('address_check') > 0) : ?>
	<span class="<?php echo $this->contact->params->get('marker_class'); ?>" >
		<?php echo $this->contact->params->get('marker_address'); ?>
	</span>
<?php endif; ?>
<?php if ($this->contact->address && $this->contact->params->get('show_street_address')) : ?>
	<div class="jcontact-street">
		<?php echo nl2br($this->contact->address); ?>
	</div>
<?php endif; ?>
<?php if ($this->contact->suburb && $this->contact->params->get('show_suburb')) : ?>
	<div class="jcontact-suburb">
		<?php echo $this->contact->suburb; ?>
	</div>
<?php endif; ?>
<?php if ($this->contact->state && $this->contact->params->get('show_state')) : ?>
	<div class="jcontact-state">
		<?php echo $this->contact->state; ?>
	</div>
		<?php endif; ?>
<?php if ($this->contact->postcode && $this->contact->params->get('show_postcode')) : ?>
	<div class="jcontact-postcode">
		<?php echo $this->contact->postcode; ?>
	</div>
<?php endif; ?>
<?php if ($this->contact->country && $this->contact->params->get('show_country')) : ?>
	<div class="jcontact-country">
		<?php echo $this->contact->country; ?>
	</div>
<?php endif; ?>
</div>

<?php endif; ?>

<div class="jcontact-contactinfo">

<?php if ($this->contact->email_to && $this->contact->params->get('show_email')) : ?>
	<span class="<?php echo $this->contact->params->get('marker_class'); ?>" >
		<?php echo $this->contact->params->get('marker_email'); ?>
	</span>
	<span class="jcontact-emailto">
		<?php echo $this->contact->email_to; ?>
	</span>
<?php endif; ?>

<?php if ($this->contact->telephone && $this->contact->params->get('show_telephone')) : ?>
	<span class="<?php echo $this->contact->params->get('marker_class'); ?>" >
		<?php echo $this->contact->params->get('marker_telephone'); ?>
	</span>
	<span class="jcontact-telephone">
		<?php echo nl2br($this->contact->telephone); ?>
	</span>
<?php endif; ?>
<?php if ($this->contact->fax && $this->contact->params->get('show_fax')) : ?>
	<span class="<?php echo $this->contact->params->get('marker_class'); ?>" >
		<?php echo $this->contact->params->get('marker_fax'); ?>
	</span>
	<span class="jcontact-fax">
		<?php echo nl2br($this->contact->fax); ?>
	</span>
<?php endif; ?>
<?php if ($this->contact->mobile && $this->contact->params->get('show_mobile')) :?>
	<span class="<?php echo $this->contact->params->get('marker_class'); ?>" >
	<?php echo $this->contact->params->get('marker_mobile'); ?>
	</span>
	<span class="jcontact-mobile">
		<?php echo nl2br($this->contact->mobile); ?>
	</span>
<?php endif; ?>
<?php if ($this->contact->webpage && $this->contact->params->get('show_webpage')) : ?>
	<span class="<?php echo $this->contact->params->get('marker_class'); ?>" >
	</span>
	<span class="jcontact-webpage">
		<a href="<?php echo $this->contact->webpage; ?>" target="_blank">
			<?php echo $this->contact->webpage; ?></a>
	</span>
<?php endif; ?>
</div>




