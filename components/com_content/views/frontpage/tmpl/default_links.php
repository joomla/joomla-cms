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
<h3><?php echo JText::_('More Articles...'); ?></h3>

<ol class="jlinks">
<?php foreach ($this->link_items as &$item) : ?>
	<li>
		<a href="<?php echo JRoute::_(ContentRoute::article($item->slug, $item->catslug)); ?>">
			<?php echo $item->title; ?></a>
	</li>
<?php endforeach; ?>
</ol>
