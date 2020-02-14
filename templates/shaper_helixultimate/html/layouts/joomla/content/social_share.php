<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();

$url = JRoute::_(ContentHelperRoute::getArticleRoute($displayData->id . ':' . $displayData->alias, $displayData->catid, $displayData->language));
$root = JURI::base();
$root = new JURI($root);
$url = $root->getScheme() . '://' . $root->getHost() . $url;
$params = JFactory::getApplication()->getTemplate(true)->params;
$tmpl_params = JFactory::getApplication()->getTemplate(true)->params;
$socialShares = $tmpl_params->get("social_share_lists");

if( is_array($socialShares) && $params->get('social_share') ) : ?>
<div class="article-social-share">
	<div class="social-share-icon">
		<ul>
			<?php foreach( $socialShares as $socialSite ): ?>
				<?php if( $socialSite == 'facebook'): ?>
				<li>
					<a class="facebook" onClick="window.open('http://www.facebook.com/sharer.php?u=<?php echo $url; ?>','Facebook','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://www.facebook.com/sharer.php?u=<?php echo $url; ?>" title="<?php echo JText::_('HELIX_ULTIMATE_SHARE_FACEBOOK'); ?>">
						<span class="fa fa-facebook"></span>
					</a>
				</li>
				<?php endif; ?>
				<?php if( $socialSite == 'twitter'): ?>
				<li>
					<a class="twitter" title="<?php echo JText::_('HELIX_ULTIMATE_SHARE_TWITTER'); ?>" onClick="window.open('http://twitter.com/share?url=<?php echo $url; ?>&amp;text=<?php echo str_replace(" ", "%20", $displayData->title); ?>','Twitter share','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://twitter.com/share?url=<?php echo $url; ?>&amp;text=<?php echo str_replace(" ", "%20", $displayData->title); ?>">
						<span class="fa fa-twitter"></span>
					</a>
				</li>
				<?php endif; ?>
				<?php if( $socialSite == 'linkedin'): ?>
					<li>
						<a class="linkedin" title="<?php echo JText::_('HELIX_ULTIMATE_SHARE_LINKEDIN'); ?>" onClick="window.open('http://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url; ?>','Linkedin','width=585,height=666,left='+(screen.availWidth/2-292)+',top='+(screen.availHeight/2-333)+''); return false;" href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url; ?>" >
							<span class="fa fa-linkedin-square"></span>
						</a>
					</li>
				<?php endif; ?>
			<?php endforeach; ?>
			</ul>
		</div>
	</div>
<?php endif; ?>
