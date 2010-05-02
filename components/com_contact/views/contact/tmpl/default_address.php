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
<?php if (($this->params->get('address_check') > 0) &&  ($this->contact->address || $this->contact->suburb  || $this->contact->state || $this->contact->country || $this->contact->postcode)) : ?>

<?php if ($this->params->get('address_check') > 0) : ?>
	<span class="<?php echo $this->params->get('marker_class'); ?>" >
		<?php echo $this->params->get('marker_address'); ?>
	</span>
	<div class="jcontact-address">
	<address>
<?php endif; ?>
<?php if ($this->contact->address && $this->params->get('show_street_address')) : ?>
	<span class="jcontact-street">
		<?php echo nl2br($this->contact->address); ?>
	</span>
<?php endif; ?>
<?php if ($this->contact->suburb && $this->params->get('show_suburb')) : ?>
	<span class="jcontact-suburb">
		<?php echo $this->contact->suburb; ?>
	</span>
<?php endif; ?>
<?php if ($this->contact->state && $this->params->get('show_state')) : ?>
	<span class="jcontact-state">
		<?php echo $this->contact->state; ?>
	</span>
		<?php endif; ?>
<?php if ($this->contact->postcode && $this->params->get('show_postcode')) : ?>
	<span class="jcontact-postcode">
		<?php echo $this->contact->postcode; ?>
	</span>
<?php endif; ?>
<?php if ($this->contact->country && $this->params->get('show_country')) : ?>
	<span class="jcontact-country">
		<?php echo $this->contact->country; ?>
	</span>
<?php endif; ?>


<?php endif; ?>
<?php if ($this->params->get('address_check') > 0) : ?>
</address>
</div>
<?php endif; ?>


<?php if($this->params->get('show_email') || $this->params->get('show_telephone')||$this->params->get('show_fax')||$this->params->get('show_mobile')|| $this->params->get('show_webpage') ) : ?>
<div class="jcontact-contactinfo">
<?php endif; ?>
<?php if ($this->contact->email_to && $this->params->get('show_email')) : ?>
<p>
	<span class="<?php echo $this->params->get('marker_class'); ?>" >
		<?php echo $this->params->get('marker_email'); ?>
	</span>
	<span class="jcontact-emailto">
		<?php echo $this->contact->email_to; ?>
	</span>
</p>
<?php endif; ?>

<?php if ($this->contact->telephone && $this->params->get('show_telephone')) : ?>
<p>
	<span class="<?php echo $this->params->get('marker_class'); ?>" >
		<?php echo $this->params->get('marker_telephone'); ?>
	</span>
	<span class="jcontact-telephone">
		<?php echo nl2br($this->contact->telephone); ?>
	</span>
	</p>
<?php endif; ?>
<?php if ($this->contact->fax && $this->params->get('show_fax')) : ?>
<p>
	<span class="<?php echo $this->params->get('marker_class'); ?>" >
		<?php echo $this->params->get('marker_fax'); ?>
	</span>
	<span class="jcontact-fax">
		<?php echo nl2br($this->contact->fax); ?>
	</span>
	</p>
<?php endif; ?>
<?php if ($this->contact->mobile && $this->params->get('show_mobile')) :?>
<p>
	<span class="<?php echo $this->params->get('marker_class'); ?>" >
	<?php echo $this->params->get('marker_mobile'); ?>
	</span>
	<span class="jcontact-mobile">
		<?php echo nl2br($this->contact->mobile); ?>
	</span>
	</p>
<?php endif; ?>
<?php if ($this->contact->webpage && $this->params->get('show_webpage')) : ?>
<p>
	<span class="<?php echo $this->params->get('marker_class'); ?>" >
	</span>
	<span class="jcontact-webpage">
		<a href="<?php echo $this->contact->webpage; ?>" target="_blank">
			<?php echo $this->contact->webpage; ?></a>
	</span>
	</p>
<?php endif; ?>
<?php if($this->params->get('show_email') || $this->params->get('show_telephone')||$this->params->get('show_fax')||$this->params->get('show_mobile')|| $this->params->get('show_webpage') ) : ?>
</div>

<?php endif; ?>
