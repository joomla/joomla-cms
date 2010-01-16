<?php
/**
 * @version		$Id: default_siblings.php 12416 2009-07-03 08:49:14Z eddieajau $
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>

<? print_r ($this); ?>
<?php if (empty($this->siblings)) : ?>
<p><?php  echo JText::_('JContent_No_Siblings'); ?></p>
<?php else : ?>
	<h3><?php  echo JText::_('JContent_Siblings'); ?></h3>
	<ul>
		<?php foreach ($this->siblings as &$item) : ?>
		<li>
			<?php if ($item->id != $this->item->id) : ?>
			<a href="<?php echo JRoute::_(WeblinksRoute::category($item->slug)); ?>">
				<?php echo $this->escape($item->title); ?></a>
			<?php else : ?>
				<?php echo $this->escape($item->title); ?>
			<?php endif; ?>
		</li>
		<?php endforeach; ?>
	</ul>

<?php endif; ?>
