<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
			<a href="<?php echo JRoute::_(ContentRoute::category($item->slug)); ?>">
				<?php echo $this->escape($item->title); ?></a>
		</li>
		<?php endforeach; ?>
	</ol>

<?php endif; ?>