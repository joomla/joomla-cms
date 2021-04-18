<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

?>

<?php if (!empty($this->msg)) : ?>
	<?php echo $this->msg; ?>
<?php else : ?>
	<?php $lang      = Factory::getLanguage(); ?>
	<?php $myrtl     = $this->item->rtl; ?>
	<?php $direction = ' '; ?>
	<?php $isRtl     = $lang->isRtl(); ?>
	<?php if ($isRtl && $myrtl == 0) : ?>
		<?php $direction = ' redirect-rtl'; ?>
	<?php elseif ($isRtl && $myrtl == 1) : ?>
		<?php $direction = ' redirect-ltr'; ?>
	<?php elseif ($isRtl && $myrtl == 2) : ?>
		<?php $direction = ' redirect-rtl'; ?>
	<?php elseif ($myrtl == 0) : ?>
		<?php $direction = ' redirect-ltr'; ?>
	<?php elseif ($myrtl == 1) : ?>
		<?php $direction = ' redirect-ltr'; ?>
	<?php elseif ($myrtl == 2) : ?>
		<?php $direction = ' redirect-rtl'; ?>
	<?php endif; ?>
	<?php $images = json_decode($this->item->images); ?>
	<div class="com-newsfeeds-newsfeed newsfeed<?php echo $direction; ?>">
		<?php if ($this->params->get('display_num')) : ?>
		<h1 class="<?php echo $direction; ?>">
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
		<?php endif; ?>
		<h2 class="<?php echo $direction; ?>">
			<?php if ($this->item->published == 0) : ?>
				<span class="badge bg-warning text-light"><?php echo Text::_('JUNPUBLISHED'); ?></span>
			<?php endif; ?>
			<a href="<?php echo $this->item->link; ?>" target="_blank" rel="noopener">
				<?php echo str_replace('&apos;', "'", $this->item->name); ?>
			</a>
		</h2>

		<?php if ($this->params->get('show_tags', 1)) : ?>
			<?php $this->item->tagLayout = new FileLayout('joomla.content.tags'); ?>
			<?php echo $this->item->tagLayout->render($this->item->tags->itemTags); ?>
		<?php endif; ?>

		<!-- Show Images from Component -->
		<?php if (isset($images->image_first) && !empty($images->image_first)) : ?>
			<?php $imgfloat = empty($images->float_first) ? $this->params->get('float_first') : $images->float_first; ?>
			<?php $alt = empty($images->image_first_alt) && empty($images->image_first_alt_empty)
				? ''
				: 'alt="' . htmlspecialchars($images->image_first_alt, ENT_COMPAT, 'UTF-8') . '"'; ?>

			<div class="com-newsfeeds-newsfeed__first-image img-intro-<?php echo htmlspecialchars($imgfloat, ENT_COMPAT, 'UTF-8'); ?>">
				<img
				<?php if ($images->image_first_caption) : ?>
					<?php echo 'class="caption" title="' . htmlspecialchars($images->image_first_caption, ENT_COMPAT, 'UTF-8') . '"'; ?>
				<?php endif; ?>
				src="<?php echo htmlspecialchars($images->image_first, ENT_COMPAT, 'UTF-8'); ?>" <?php echo $alt; ?>>
			</div>
		<?php endif; ?>

		<?php if (isset($images->image_second) and !empty($images->image_second)) : ?>
			<?php $imgfloat = empty($images->float_second) ? $this->params->get('float_second') : $images->float_second; ?>
			<?php $alt = empty($images->image_second_alt) && empty($images->image_second_alt_empty)
				? ''
				: 'alt="' . htmlspecialchars($images->image_second_alt, ENT_COMPAT, 'UTF-8') . '"'; ?>
			<div class="com-newsfeeds-newsfeed__second-image float-<?php echo htmlspecialchars($imgfloat, ENT_COMPAT, 'UTF-8'); ?> item-image">
				<img
				<?php if ($images->image_second_caption) : ?>
					<?php echo 'class="caption" title="' . htmlspecialchars($images->image_second_caption) . '"'; ?>
				<?php endif; ?>
				src="<?php echo htmlspecialchars($images->image_second, ENT_COMPAT, 'UTF-8'); ?>" <?php echo $alt; ?>>
			</div>
		<?php endif; ?>
		<!-- Show Description from Component -->
		<?php echo $this->item->description; ?>
		<!-- Show Feed's Description -->

		<?php if ($this->params->get('show_feed_description')) : ?>
			<div class="com-newsfeeds-newsfeed__description feed-description">
				<?php echo str_replace('&apos;', "'", $this->rssDoc->description); ?>
			</div>
		<?php endif; ?>

		<!-- Show Image -->
	  <?php if ($this->rssDoc->image && $this->params->get('show_feed_image')) : ?>
			<div class="com-newsfeeds-newsfeed__feed-image">
				<img src="<?php echo $this->rssDoc->image->uri; ?>" alt="<?php echo $this->rssDoc->image->title; ?>" />
			</div>
		<?php endif; ?>

		<!-- Show items -->
		<?php if (!empty($this->rssDoc[0])) : ?>
			<ol class="com-newsfeeds-newsfeed__items">
				<?php for ($i = 0; $i < $this->item->numarticles; $i++) : ?>
					<?php if (empty($this->rssDoc[$i])) : ?>
						<?php break; ?>
					<?php endif; ?>
					<?php $uri  = $this->rssDoc[$i]->uri || !$this->rssDoc[$i]->isPermaLink ? trim($this->rssDoc[$i]->uri) : trim($this->rssDoc[$i]->guid); ?>
					<?php $uri  = !$uri || stripos($uri, 'http') !== 0 ? $this->item->link : $uri; ?>
					<?php $text = $this->rssDoc[$i]->content !== '' ? trim($this->rssDoc[$i]->content) : ''; ?>
					<li>
						<?php if (!empty($uri)) : ?>
							<h3 class="feed-link">
								<a href="<?php echo htmlspecialchars($uri); ?>" target="_blank" rel="noopener">
									<?php echo trim($this->rssDoc[$i]->title); ?>
								</a>
							</h3>
						<?php else : ?>
							<h3 class="feed-link"><?php echo trim($this->rssDoc[$i]->title); ?></h3>
						<?php endif; ?>

						<?php if ($this->params->get('show_item_description') && $text !== '') : ?>
							<div class="feed-item-description">
								<?php if ($this->params->get('show_feed_image', 0) == 0) : ?>
									<?php $text = OutputFilter::stripImages($text); ?>
								<?php endif; ?>
								<?php $text = HTMLHelper::_('string.truncate', $text, $this->params->get('feed_character_count')); ?>
								<?php echo str_replace('&apos;', "'", $text); ?>
							</div>
						<?php endif; ?>
					</li>
				<?php endfor; ?>
			</ol>
		<?php endif; ?>
	</div>
<?php endif; ?>
