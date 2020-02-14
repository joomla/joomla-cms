<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$url  	=  JRoute::_(ContentHelperRoute::getArticleRoute($displayData->id . ':' . $displayData->alias, $displayData->catid, $displayData->language));
$root 	= JURI::base();
$root 	= new JURI($root);
$url  	= $root->getScheme() . '://' . $root->getHost() . $url;

$params 	= JFactory::getApplication()->getTemplate(true)->params;

if( $params->get('social_share') ) { ?>
	<div class="helix-social-share">
		<div class="helix-social-share-icon">
			<ul>
				
				<li>
					<div class="facebook" data-toggle="tooltip" data-placement="top" title="<?php echo JText::_('HELIX_SHARE_FACEBOOK'); ?>">

						<a class="facebook" onClick="window.open('http://www.facebook.com/sharer.php?u=<?php echo $url; ?>','Facebook','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://www.facebook.com/sharer.php?u=<?php echo $url; ?>">

							<i class="fa fa-facebook"></i>
						</a>

					</div>
				</li>
				<li>
					<div class="twitter"  data-toggle="tooltip" data-placement="top" title="<?php echo JText::_('HELIX_SHARE_TWITTER'); ?>">
						
						<a class="twitter" onClick="window.open('http://twitter.com/share?url=<?php echo $url; ?>&amp;text=<?php echo str_replace(" ", "%20", $displayData->title); ?>','Twitter share','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;" href="http://twitter.com/share?url=<?php echo $url; ?>&amp;text=<?php echo str_replace(" ", "%20", $displayData->title); ?>">
							<i class="fa fa-twitter"></i>
						</a>

					</div>
				</li>
				<li>
					<div class="google-plus">
						<a class="gplus" data-toggle="tooltip" data-placement="top" title="<?php echo JText::_('HELIX_SHARE_GOOGLE_PLUS'); ?>" onClick="window.open('https://plus.google.com/share?url=<?php echo $url; ?>','Google plus','width=585,height=666,left='+(screen.availWidth/2-292)+',top='+(screen.availHeight/2-333)+''); return false;" href="https://plus.google.com/share?url=<?php echo $url; ?>" >
						<i class="fa fa-google-plus"></i></a>
					</div>
				</li>
				
				<li>
					<div class="linkedin">
						<a class="linkedin" data-toggle="tooltip" data-placement="top" title="<?php echo JText::_('HELIX_SHARE_LINKEDIN'); ?>" onClick="window.open('http://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url; ?>','Linkedin','width=585,height=666,left='+(screen.availWidth/2-292)+',top='+(screen.availHeight/2-333)+''); return false;" href="http://www.linkedin.com/shareArticle?mini=true&url=<?php echo $url; ?>" >
							
						<i class="fa fa-linkedin-square"></i></a>
					</div>
				</li>
			</ul>
		</div>		
	</div> <!-- /.helix-social-share -->
<?php } ?>














