<?php
/**
 * @version		
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die; ?>
<div>
	<strong><?php echo JText::_( 'MORE_ARTICLES' ); ?></strong>
</div>
<ul>
<?php foreach ($this->link_items as &$item) : ?>
	<li>
			<a class="blogsection" href="<?php echo JRoute::_(ContentHelperRoute::getArticleRoute($link->slug, $link->catslug, $link->sectionid)); ?>">
			<?php echo $item->title; ?></a>
	</li>
<?php endforeach; ?>
</ul>
