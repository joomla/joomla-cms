<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Create shortcuts to some parameters.
$url   = $this->item->core_urls;
$imageName = $this->item->core_title;
?>

	<div class="item-page" itemscope="" itemtype="http://schema.org/Image">
		<div class="page-header">
			<h2 itemprop="name">
				<?php echo $imageName; ?>
			</h2>
		</div>

		<dl class="image-info muted">

		<dt class="image-info-term"><?php echo JText::_('COM_MEDIA_IMAGE_INFO'); ?></dt>

		<dd class="createdby" itemprop="author" itemscope itemtype="http://schema.org/Person">
			<?php $author = $this->item->core_created_by_alias ? $this->item->core_created_by_alias : ''; ?>
			<?php $author = '<span itemprop="name">' . $author . '</span>'; ?>
				<?php echo JText::sprintf('COM_MEDIA_CREATED_BY', $author); ?>
		</dd>
		<dd class="published">
			<span class="icon-calendar"></span>
			<time datetime="<?php echo JHtml::_('date', $this->item->core_publish_up, 'c'); ?>" itemprop="datePublished">
				<?php echo JText::sprintf('COM_MEDIA_PUBLISHED_DATE_ON', JHtml::_('date', $this->item->core_publish_up, JText::_('DATE_FORMAT_LC3'))); ?>
			</time>
		</dd>
		<dd class="create">
			<span class="icon-calendar"></span>
			<time datetime="<?php echo JHtml::_('date', $this->item->core_created_time, 'c'); ?>" itemprop="dateCreated">
				<?php echo JText::sprintf('COM_MEDIA_CREATED_DATE_ON', JHtml::_('date', $this->item->core_created_time, JText::_('DATE_FORMAT_LC3'))); ?>
			</time>
		</dd>
		<dd class="modified">
			<span class="icon-calendar"></span>
			<time datetime="<?php echo JHtml::_('date', $this->item->core_modified_time, 'c'); ?>" itemprop="dateModified">
				<?php echo JText::sprintf('COM_MEDIA_LAST_UPDATED', JHtml::_('date', $this->item->core_modified_time, JText::_('DATE_FORMAT_LC3'))); ?>
			</time>
		</dd>

		</dl>

		<img src="<?php  echo $url; ?>">

	</div>