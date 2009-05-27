<?php // @version $Id: default_address.php 11215 2008-10-26 02:25:51Z ian $
defined('_JEXEC') or die;
?>

<?php if (($this->contact->params->get('address_check') > 0) && ($this->contact->address || $this->contact->suburb || $this->contact->state || $this->contact->country || $this->contact->postcode)) : ?>
<div class="contact_address">
	<address>

	<?php if ($this->contact->params->get('address_check') > 0) : ?>
	        <?php if (($this->contact->params->get('contact_icons') ==0) || (                $this->contact->params->get('contact_icons') ==1)): ?>
	<span class="marker"><?php echo $this->contact->params->get('marker_address'); ?></span>
	<br />
	<?php endif; ?>
	<?php endif; ?>

	<?php if ($this->contact->address && $this->contact->params->get('show_street_address')) : ?>
	<?php echo nl2br($this->contact->address); ?><br />
	<?php endif; ?>

	<?php if ($this->contact->suburb && $this->contact->params->get('show_suburb')) : ?>
	<?php echo $this->contact->suburb; ?><br />
	<?php endif; ?>

	<?php if ($this->contact->state && $this->contact->params->get('show_state')) : ?>
	<?php echo $this->contact->state; ?><br />
	<?php endif; ?>

	<?php if ($this->contact->country && $this->contact->params->get('show_country')) : ?>
	<?php echo $this->contact->country; ?><br />
	<?php endif; ?>

	<?php if ($this->contact->postcode && $this->contact->params->get('show_postcode')) : ?>
	<?php echo $this->contact->postcode; ?><br />
	<?php endif; ?>



<?php endif; ?>

<?php if (($this->contact->email_to && $this->contact->params->get('show_email')) || $this->contact->telephone || $this->contact->fax) : ?>

	<?php if ($this->contact->email_to && $this->contact->params->get('show_email')) : ?>
	    <?php if (($this->contact->params->get('contact_icons') ==0) || ($this->contact->params->get('contact_icons') ==1)): ?>
	<span class="marker"><?php echo $this->contact->params->get('marker_email'); ?></span>
	<?php endif; ?>
	<?php echo $this->contact->email_to; ?><br />
	<?php endif; ?>


	<?php if ($this->contact->telephone && $this->contact->params->get('show_telephone')) : ?>


    <?php if (($this->contact->params->get('contact_icons') ==0) || ($this->contact->params->get('contact_icons') ==1)): ?>
	<span class="marker"><?php echo $this->contact->params->get('marker_telephone'); ?></span>
	<?php endif; ?>
	<?php echo nl2br($this->contact->telephone); ?><br />

	<?php endif; ?>


	<?php if ($this->contact->fax && $this->contact->params->get('show_fax')) : ?>

        <?php if (($this->contact->params->get('contact_icons') ==0) || ($this->contact->params->get('contact_icons') ==1)): ?>
	<span class="marker"><?php echo $this->contact->params->get('marker_fax'); ?></span>
	<?php endif; ?>
	<?php echo nl2br($this->contact->fax); ?><br />
	<?php endif; ?>

	<?php if ($this->contact->mobile && $this->contact->params->get('show_mobile')) :?>
	 <?php if (($this->contact->params->get('contact_icons') ==0) || ($this->contact->params->get('contact_icons') ==1)): ?>
	<span class="marker"><?php echo $this->contact->params->get('marker_mobile'); ?></span>
	<?php endif; ?>
	<?php echo nl2br($this->contact->mobile); ?><br />
	<?php endif; ?>

	<?php if ($this->contact->webpage && $this->contact->params->get('show_webpage')) : ?>
	<a href="<?php echo $this->contact->webpage; ?>" target="_blank">
	<?php echo $this->contact->webpage; ?></a><br />
	<?php endif; ?>

<?php endif; ?>
 </address></div>
<?php if ($this->contact->misc && $this->contact->params->get('show_misc')) : ?>
<p>
<?php if (($this->contact->params->get('contact_icons') ==0) || ($this->contact->params->get('contact_icons') ==1)): ?>
<span class="marker"><?php echo $this->contact->params->get('marker_misc'); ?></span>
<?php echo $this->contact->misc; ?>
<?php endif; ?>
</p>
<?php endif; ?>