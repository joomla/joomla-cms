<?php
/**
 * @version		$Id: default_parents.php 12416 2009-07-03 08:49:14Z eddieajau $
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<?php if (empty($this->parents)) : ?>
<p><?php  echo JText::_('JContent_No_Parents'); ?></p>
<?php else : ?>
	<h3><?php  echo JText::_('JContent_Parents'); ?></h3>
	<ul>
		<?php foreach ($this->parents as &$item) : ?>
		<li>
			<a href="<?php echo JRoute::_(WeblinksRoute::category($item->slug)); ?>">
				<?php echo $this->escape($item->title); ?></a>
		</li>
		<?php endforeach; ?>
	</ul>

<?php endif; ?>
