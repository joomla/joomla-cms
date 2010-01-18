<?php
/**
 * @version		$Id: default_children.php 12416 2009-07-03 08:49:14Z eddieajau $
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<?php if (empty($this->children)) : ?>
	no children
<?php else : ?>
	<h5>Children</h5>
	<ol>
		<?php foreach ($this->children as &$item) : ?>
		<li>
			<a href="<?php echo JRoute::_(ContactRoute::category($item->slug)); ?>">
				<?php echo $this->escape($item->title); ?></a>
		</li>
		<?php endforeach; ?>
	</ol>

<?php endif; ?>
