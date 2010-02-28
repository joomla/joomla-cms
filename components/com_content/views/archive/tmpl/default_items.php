<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers');
?>
<ul id="archive-items">
<?php foreach ($this->items as $i => $item) : ?>
	<li class="row<?php echo $i % 2; ?>">

		<h2>
			<a href="<?php echo JRoute::_(ContentRoute::article($item->slug)); ?>">

				<?php echo $this->escape($item->title); ?></a>
		</h2>
			<div>

				<?php if ($this->params->get('show_category') && $item->catid) : ?>
					<span>
					<?php if ($this->params->get('link_category')) : ?>
						<?php // echo '<a href="'.JRoute::_(ContentRoute::getCategoryRoute($item->catslug, $item->sectionid)).'">'; ?>
					<?php endif; ?>
					<?php echo $item->category; ?>
					<?php if ($this->params->get('link_category')) : ?>
						<?php echo '</a>'; ?>
					<?php endif; ?>
					</span>
				<?php endif; ?>
			</div>

		<?php if ($this->params->get('show_create_date')) : ?>
			<span class="created-date">
				<?php echo JHTML::_('date',$item->created, JText::_('DATE_FORMAT_LC2')); ?>
			</span>
			<?php endif; ?>
			<?php if ($this->params->get('show_author')) : ?>
			<span class="created-by">
				<?php $author = $this->params->get('link_author', 0) ? JHTML::_('link',JRoute::_('index.php?option=com_users&view=profile&member_id='.$item->created_by),$item->author) : $item->author; ?>
				<?php echo JText::sprintf('Written_by', ($item->created_by_alias ? $item->created_by_alias : $author)); ?>
			</span>
		<?php endif; ?>

		<div class="intro">
			<?php echo substr($item->introtext, 0, 255); echo JText::_('  ...') ?>
		</div>
	</li>
<?php endforeach; ?>
</ul>
<div id="navigation">
	<span><?php echo $this->pagination->getPagesLinks(); ?></span>
	<span><?php echo $this->pagination->getPagesCounter(); ?></span>
</div>