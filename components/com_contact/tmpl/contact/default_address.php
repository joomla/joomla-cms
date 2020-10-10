<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\String\PunycodeHelper;

/**
 * Marker_class: Class based on the selection of text, none, or icons
 * jicon-text, jicon-none, jicon-icon
 */
?>
<dl class="com-contact__address contact-address dl-horizontal" itemprop="address" itemscope itemtype="https://schema.org/PostalAddress">
	<?php if (($this->params->get('address_check') > 0) &&
		($this->item->address || $this->item->suburb  || $this->item->state || $this->item->country || $this->item->postcode)) : ?>
		<dt>
			<span class="<?php echo $this->params->get('marker_class'); ?>">
				<?php echo $this->params->get('marker_address'); ?>
			</span>
		</dt>

		<?php if ($this->item->address && $this->params->get('show_street_address')) : ?>
			<dd>
				<span class="contact-street" itemprop="streetAddress">
					<?php echo nl2br($this->item->address); ?>
					<br>
				</span>
			</dd>
		<?php endif; ?>

		<?php if ($this->item->suburb && $this->params->get('show_suburb')) : ?>
			<dd>
				<span class="contact-suburb" itemprop="addressLocality">
					<?php echo $this->item->suburb; ?>
					<br>
				</span>
			</dd>
		<?php endif; ?>
		<?php if ($this->item->state && $this->params->get('show_state')) : ?>
			<dd>
				<span class="contact-state" itemprop="addressRegion">
					<?php echo $this->item->state; ?>
					<br>
				</span>
			</dd>
		<?php endif; ?>
		<?php if ($this->item->postcode && $this->params->get('show_postcode')) : ?>
			<dd>
				<span class="contact-postcode" itemprop="postalCode">
					<?php echo $this->item->postcode; ?>
					<br>
				</span>
			</dd>
		<?php endif; ?>
		<?php if ($this->item->country && $this->params->get('show_country')) : ?>
		<dd>
			<span class="contact-country" itemprop="addressCountry">
				<?php echo $this->item->country; ?>
				<br>
			</span>
		</dd>
		<?php endif; ?>
	<?php endif; ?>

<?php if ($this->item->email_to && $this->params->get('show_email')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>" itemprop="email">
			<?php echo nl2br($this->params->get('marker_email')); ?>
		</span>
	</dt>
	<dd>
		<span class="contact-emailto">
			<?php echo $this->item->email_to; ?>
		</span>
	</dd>
<?php endif; ?>

<?php if ($this->item->telephone && $this->params->get('show_telephone')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
			<?php echo $this->params->get('marker_telephone'); ?>
		</span>
	</dt>
	<dd>
		<span class="contact-telephone" itemprop="telephone">
			<?php echo $this->item->telephone; ?>
		</span>
	</dd>
<?php endif; ?>
<?php if ($this->item->fax && $this->params->get('show_fax')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
			<?php echo $this->params->get('marker_fax'); ?>
		</span>
	</dt>
	<dd>
		<span class="contact-fax" itemprop="faxNumber">
		<?php echo $this->item->fax; ?>
		</span>
	</dd>
<?php endif; ?>
<?php if ($this->item->mobile && $this->params->get('show_mobile')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
			<?php echo $this->params->get('marker_mobile'); ?>
		</span>
	</dt>
	<dd>
		<span class="contact-mobile" itemprop="telephone">
			<?php echo $this->item->mobile; ?>
		</span>
	</dd>
<?php endif; ?>
<?php if ($this->item->webpage && $this->params->get('show_webpage')) : ?>
	<dt>
		<span class="<?php echo $this->params->get('marker_class'); ?>">
		</span>
	</dt>
	<dd>
		<span class="contact-webpage">
			<a href="<?php echo $this->item->webpage; ?>" target="_blank" rel="noopener noreferrer" itemprop="url">
			<?php echo PunycodeHelper::urlToUTF8($this->item->webpage); ?></a>
		</span>
	</dd>
<?php endif; ?>
</dl>
