<?php // @version $Id$
defined('_JEXEC') or die('Restricted access');
?>

<?php if (($this->contact->params->get('address_check') > 0) && ($this->contact->address || $this->contact->suburb || $this->contact->state || $this->contact->country || $this->contact->postcode)) : ?>
<div class="contact_address">
	<address>

	<?php if ( $this->contact->params->get('address_check') > 0) : ?>
	<span class="marker"><?php echo $this->contact->params->get('marker_address'); ?></span>
	<?php endif; ?>

	<?php if ($this->contact->address && $this->contact->params->get('show_street_address')) : ?>
	<p><?php echo nl2br($this->contact->address); ?></p>
	<?php endif; ?>

	<?php if ($this->contact->suburb && $this->contact->params->get('show_suburb')) : ?>
	<p><?php echo $this->contact->suburb; ?></p>
	<?php endif; ?>

	<?php if ($this->contact->state && $this->contact->params->get('show_state')) : ?>
	<p><?php echo $this->contact->state; ?></p>
	<?php endif; ?>

	<?php if ($this->contact->country && $this->contact->params->get('show_country')) : ?>
	<p><?php echo $this->contact->country; ?></p>
	<?php endif; ?>

	<?php if ($this->contact->postcode && $this->contact->params->get('show_postcode')) : ?>
	<p><?php echo $this->contact->postcode; ?></p>
	<?php endif; ?>

	</address>
</div>
<?php endif; ?>

<?php if (($this->contact->email_to && $this->contact->params->get('show_email')) || $this->contact->telephone || $this->contact->fax ) : ?>

	<?php if ($this->contact->email_to && $this->contact->params->get('show_email')) : ?>
	<p><span class="marker"><?php echo $this->contact->params->get('marker_email'); ?></span>
	<?php echo $this->contact->email_to; ?></p>
	<?php endif; ?>

	<?php if ($this->contact->telephone && $this->contact->params->get('show_telephone')) : ?>
	<p><span class="marker"><?php echo $this->contact->params->get('marker_telephone'); ?></span>
	<?php echo nl2br($this->contact->telephone); ?></p>
	<?php endif; ?>

	<?php if ($this->contact->fax && $this->contact->params->get('show_fax')) : ?>
	<p><span class="marker"><?php echo $this->contact->params->get('marker_fax'); ?></span>
	<?php echo nl2br($this->contact->fax); ?></p>
	<?php endif; ?>

	<?php if ( $this->contact->mobile && $this->contact->params->get( 'show_mobile' ) ) :?>
	<p><span class="marker"><?php echo $this->contact->params->get( 'marker_mobile' ); ?></span>
	<?php echo nl2br($this->contact->mobile); ?></p>
	<?php endif; ?>

	<?php if ($this->contact->webpage && $this->contact->params->get('show_webpage')) : ?>
	<p><a href="<?php echo $this->contact->webpage; ?>" target="_blank">
	<?php echo $this->contact->webpage; ?></a></p>
	<?php endif; ?>

<?php endif; ?>

<?php if ($this->contact->misc && $this->contact->params->get('show_misc')) : ?>
<p><span class="marker"><?php echo $this->contact->params->get('marker_misc'); ?></span>
<?php echo $this->contact->misc; ?></p>
<?php endif; ?>