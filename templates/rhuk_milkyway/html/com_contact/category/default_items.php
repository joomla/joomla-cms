<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	com_contact
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
JHtml::core();

$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
?>

<?php foreach($this->items as $i => $item) : ?>
<tr class="sectiontableentry<?php echo ($i % 2) ? "odd" : "even"; ?>">

	<td align="right" width="5">
		<?php echo $i+1; ?>
	</td>
	<td height="20">
		<a href="<?php echo JRoute::_(ContactHelperRoute::getContactRoute($item->slug, $item->catid)); ?>" class="category<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
			<?php echo $item->name; ?></a>
	</td>
	<?php if ( $this->params->get( 'show_position' ) ) : ?>
	<td>
		<?php echo $this->escape($item->con_position); ?>
	</td>
	<?php endif; ?>
	<?php if ( $this->params->get( 'show_email' ) ) : ?>
	<td width="20%">
		<?php echo $item->email_to; ?>
	</td>
	<?php endif; ?>
	<?php if ( $this->params->get( 'show_telephone' ) ) : ?>
	<td width="15%">
		<?php echo $this->escape($item->telephone); ?>
	</td>
	<?php endif; ?>
	<?php if ( $this->params->get( 'show_mobile' ) ) : ?>
	<td width="15%">
		<?php echo $this->escape($item->mobile); ?>
	</td>
	<?php endif; ?>
	<?php if ( $this->params->get( 'show_fax' ) ) : ?>
	<td width="15%">
		<?php echo $this->escape($item->fax); ?>
	</td>
	<?php endif; ?>
</tr>
<?php endforeach; ?>
