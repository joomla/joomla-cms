<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_feed
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<?php if (!empty($feed) && is_string($feed)) : ?>
	<?php echo $feed; ?>
<?php else : ?>
	<?php $lang = JFactory::getLanguage(); ?>
	<?php $myrtl = $params->get('rssrtl'); ?>
	<?php $direction = ''; ?>
	<?php $isRtl = $lang->isRtl(); ?>
	<?php if ($isRtl && $myrtl == 0) : ?>
		<?php $direction = 'redirect-rtl'; ?>
	<?php elseif ($isRtl && $myrtl == 1) : ?>
		<?php // Feed description ?>
		<?php $direction = 'redirect-ltr'; ?>
	<?php elseif ($isRtl && $myrtl == 2) : ?>
		<?php $direction = 'redirect-rtl'; ?>
	<?php elseif ($myrtl == 0) : ?>
		<?php $direction = 'redirect-ltr'; ?>
	<?php elseif ($myrtl == 1) : ?>
		<?php $direction = 'redirect-ltr'; ?>
	<?php elseif ($myrtl == 2) : ?>
		<?php $direction = 'redirect-rtl'; ?>
	<?php endif; ?>
	<?php if ($feed !== false) : ?>
		<?php // Image handling ?>
		<?php $iUrl   = isset($feed->image) ? $feed->image : null; ?>
		<?php $iTitle = isset($feed->imagetitle) ? $feed->imagetitle : null; ?>
		<div style="direction: <?php echo $rssrtl ? 'rtl' : 'ltr'; ?>; text-align: <?php echo $rssrtl ? 'right' : 'left'; ?> !important" class="feed<?php echo $moduleclass_sfx; ?>">
			<?php // Feed description ?>
			<?php if ($feed->title !== null && $params->get('rsstitle', 1)) : ?>
				<h2 class="<?php echo $direction; ?>">
					<a href="<?php echo htmlspecialchars($rssurl, ENT_COMPAT, 'UTF-8'); ?>" target="_blank">
						<?php echo $feed->title; ?>
					</a>
				</h2>
			<?php endif; ?>
			<?php // Feed description ?>
			<?php if ($params->get('rssdesc', 1)) : ?>
				<?php echo $feed->description; ?>
			<?php endif; ?>
			<?php // Feed image ?>
			<?php if ($iUrl && $params->get('rssimage', 1)) : ?>
				<img src="<?php echo $iUrl; ?>" alt="<?php echo @$iTitle; ?>" />
			<?php endif; ?>
			<!-- Show items -->
			<?php if (!empty($feed)) : ?>
				<ul class="newsfeed<?php echo $params->get('moduleclass_sfx'); ?>">
					<?php for ($i = 0, $max = min(count($feed), $params->get('rssitems', 5)); $i < $max; $i++) : ?>
						<?php $uri   = (!empty($feed[$i]->uri) || $feed[$i]->uri !== null) ? trim($feed[$i]->uri) : trim($feed[$i]->guid); ?>
						<?php $uri   = strpos($uri, 'http') !== 0 ? $params->get('rsslink') : $uri; ?>
						<?php $text  = !empty($feed[$i]->content) || $feed[$i]->content !== null ? trim($feed[$i]->content) : trim($feed[$i]->description); ?>
						<?php $title = trim($feed[$i]->title); ?>
						<li>
							<?php if (!empty($uri)) : ?>
								<span class="feed-link">
									<a href="<?php echo htmlspecialchars($uri, ENT_COMPAT, 'UTF-8'); ?>" target="_blank">
										<?php echo $feed[$i]->title; ?>
									</a>
								</span>
							<?php else : ?>
								<span class="feed-link">
									<?php echo $title; ?>
								</span>
							<?php endif; ?>
							<?php if (!empty($text) && $params->get('rssitemdesc')) : ?>
								<div class="feed-item-description">
									<?php // Strip the images. ?>
									<?php $text = JFilterOutput::stripImages($text); ?>
									<?php $text = JHtml::_('string.truncate', $text, $params->get('word_count')); ?>
									<?php echo str_replace('&apos;', "'", $text); ?>
								</div>
							<?php endif; ?>
						</li>
					<?php endfor; ?>
				</ul>
			<?php endif; ?>
		</div>
	<?php endif; ?>
<?php endif; ?>
