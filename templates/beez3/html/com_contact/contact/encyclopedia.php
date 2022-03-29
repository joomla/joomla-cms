<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$cparams = JComponentHelper::getParams('com_media');
?>
<div class="contact<?php echo $this->pageclass_sfx?>">
		<?php $contactLink = ContactHelperRoute::getCategoryRoute($this->contact->catid);?>
		<h3>
			<span class="contact-category"><a href="<?php echo $contactLink; ?>">
				<?php echo $this->escape($this->contact->category_title); ?></a>
			</span>
		</h3>
	<?php if ($this->contact->name && $this->params->get('show_name')) : ?>
		<h2>
			<span class="contact-name"><?php echo $this->contact->name; ?></span>
		</h2>
	<?php endif;  ?>

	<?php echo $this->item->event->afterDisplayTitle; ?>

	<?php echo $this->item->event->beforeDisplayContent; ?>

	<div class="encyclopedia_col1">
		<?php if ($this->contact->image ) : ?>
			<div class="contact-image">
			<?php // We are going to use the contact address field for the main image caption.
				// If we have a caption load the caption behavior. ?>
			<?php if ($this->contact->address)
			{
				JHtml::_('behavior.caption');
			}?>
				<?php echo JHtml::_('image', $this->contact->image, $this->contact->name, array('align' => 'middle', 'class' => 'caption', 'title' => $this->contact->address)); ?>
			</div>
		<?php endif; ?>
	</div>
	<div class="encyclopedia_col2">
		<?php // We are going to use some of the standard content fields in non standard ways. ?>
				<div class="contact-miscinfo">

						<div class="contact-misc">
							<?php echo $this->contact->misc; ?>
						</div>
					</div>


		<?php //Let's use position for the scientific name. ?>
		<?php if ($this->contact->con_position && $this->params->get('show_position')) : ?>
			<p class="contact-position"><?php echo $this->contact->con_position; ?></p>
		<?php endif; ?>
		<?php //Let's use state to put the family name.  ?>
		<?php if ($this->contact->state && $this->params->get('show_state')) : ?>
			<p class="contact-state"><?php echo $this->contact->state; ?></p>
		<?php endif; ?>
		<?php // Let's use country to list the main countries it grows in. ?>
		<?php if ($this->contact->country && $this->params->get('show_country')) : ?>
			<p class="contact-country"><?php echo $this->contact->country; ?></p>
		<?php endif; ?>
	</div>

<div class="clr"> </div>
	<?php  if ($this->params->get('presentation_style') !== 'plain'):?>
		<?php  echo  JHtml::_($this->params->get('presentation_style').'.start', 'contact-slider'); ?>
	<?php endif ?>
<div class="encyclopedia_links">
<?php echo $this->loadTemplate('links'); ?>

</div>
	<?php if ($this->params->get('presentation_style') !== 'plain'):?>
			<?php echo JHtml::_($this->params->get('presentation_style').'.end'); ?>
			<?php endif; ?>
</div>
<?php echo $this->item->event->afterDisplayContent; ?>
