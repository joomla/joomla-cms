<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('JPATH_BASE') or die();
$params = \JFactory::getApplication()->getTemplate(true)->params;
?>

<?php if($params->get('author_info', 0)) : ?>
	<div class="article-author-information">
		<?php 
			$author = \JFactory::getUser( (int) $displayData->created_by );
			$profile = \JUserHelper::getProfile( (int) $displayData->created_by );
		?>
		<div class="media">
			<img class="mr-3" src="https://www.gravatar.com/avatar/<?php echo md5($author->get('email')); ?>?s=64&d=identicon&r=PG" alt="<?php echo $author->name; ?>">
			<div class="media-body">
				<h5 class="mt-0"><?php echo $author->name; ?></h5>
				<?php if(isset($profile->profile['aboutme']) && $profile->profile['aboutme']) : ?>
					<div class="author-bio">
						<?php echo $profile->profile['aboutme']; ?>
						<?php if(isset($profile->profile['website']) && $profile->profile['website']) : ?>
							<div class="author-website mt-2">
								<strong><?php echo \Jtext::_('HELIX_ULTIMATE_BLOG_AUTHOR_WEBSITE'); ?>:</strong> <a target="_blank" href="<?php echo strip_tags($profile->profile['website'], ''); ?>"><?php echo strip_tags($profile->profile['website'], ''); ?></a>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
<?php endif; ?>
