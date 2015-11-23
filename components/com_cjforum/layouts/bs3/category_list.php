<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

$params 		= $displayData['params'];
$theme 			= $params->get('theme', 'default');
$item 			= $displayData['category'];
$maxlevel 		= $displayData['maxlevel'];
$sectionNo		= $displayData['section_num'];
$user 			= JFactory::getUser();
$access 		= $user->getAuthorisedViewLevels();

$itemParams		= new JRegistry;
$itemParams->loadString($item->params);
?>
<div class="panel panel-<?php echo $theme;?>">
	<div class="panel-heading">
		<h3 class="panel-title">
			<a href="<?php echo JRoute::_(CjForumHelperRoute::getCategoryRoute($item));?>">
				<i class="<?php echo $itemParams->get('cjforum_small_icon_class', 'fa fa-folder-open');?>"></i> <?php echo $this->escape($item->title). ($item->numitems ? ' <small>('.$item->numitems.')</small>'  : '');?> 
			</a>
		</h3>
	</div>
	<?php 
	if(!empty($item->description) && $params->get('show_description'))
	{
		?>
		<div class="panel-body">
			<?php echo JHtml::_('content.prepare', $item->description, '', 'com_cjforum.categories'); ?>
		</div>
		<?php
	}
	
	if ($maxlevel != 0 && count($item->getChildren()) > 0) 
	{
		$categories = $item->getChildren();
		?>
		<ul class="list-group no-margin-left">
		<?php
		$categoryNo = 1;
		foreach ($categories as $node)
		{
			if(in_array($node->access, $access) && $user->authorise('core.view', 'com_cjforum.category.'.$node->id))
			{
				$itemParams	= new JRegistry;
				$itemParams->loadString($node->params);
				?>
				<li class="list-group-item pad-bottom-5 <?php echo $itemParams->get('cjforum_category_row_class', '')?>">
					<div class="media">
						<div class="media-left hidden-phone hidden-xs category-icon">
							<a href="<?php echo JRoute::_(CjForumHelperRoute::getCategoryRoute($node));?>" class="media-object">
								<?php if ($params->get('show_description_image', 1) == 1) : ?>
									<?php if($node->getParams()->get('image')):?>
									<img src="<?php echo $node->getParams()->get('image'); ?>"/>
									<?php else:?>
									<i class="<?php echo $itemParams->get('cjforum_big_icon_class', 'fa fa-folder-open fa-3x');?> fa-fw"></i>
									<?php endif;?>
								<?php endif;?>
							</a>
						</div>
						<div class="media-body">
							<h4 class="media-heading no-margin-top">
								<a href="<?php echo JRoute::_(CjForumHelperRoute::getCategoryRoute($node));?>">
									<?php echo $this->escape($node->title);?>
									<small><span class="text-muted visible-phone visible-xs">(<?php echo JText::plural('COM_CJFORUM_NUM_TOPICS', $node->numitems);?>)</span></small>
								</a>
								<a href="<?php echo JRoute::_(CjForumHelperRoute::getCategoryRoute($node).'&format=feed&type=rss');?>" class="hidden-phone hidden-xs" 
									title="<?php echo JText::_('COM_CJFORUM_RSS_FEED');?>" data-toggle="tooltip">
									<sup class="margin-left-5"><small><i class="fa fa-rss-square"></i></small></sup>
								</a>
							</h4>
							
							<div class="forum-info">
							<?php 
							if(!empty($node->description) && $params->get('show_description'))
							{
								echo '<p>'.JHtml::_('content.prepare', $node->description, '', 'com_cjforum.categories').'</p>';
							}
							
							if (count($node->getChildren()) > 0) 
							{
								$children = $node->getChildren();
								?>
								<ul class="child-forums inline list-inline">
									<?php
									foreach ($children as $child)
									{
									?>
									<li>
										<a href="<?php echo JRoute::_(CjForumHelperRoute::getCategoryRoute($child));?>">
											<i class="<?php echo $itemParams->get('cjforum_small_icon_class', 'fa fa-folder-open');?>"></i>
											<?php echo $this->escape($child->title).' <span class="text-muted">('.$child->numitems.')</span>';?>
										</a>
									</li>
									<?php 
									}
								?>
								</ul>
								<?php
							}
							?>
							</div>
						</div>
						<div class="media-right hidden-phone hidden-xs">
							<div class="panel panel-<?php echo $theme;?> item-count-box">
								<div class="panel-body center item-count-num"><?php echo CjLibUtils::formatNumber($node->numitems);?></div>
								<div class="panel-footer text-nowrap text-muted item-count-caption"><?php echo JText::_('COM_CJFORUM_TOPICS');?></div>
							</div>
						</div>
					</div>
				</li>
				<?php
				if(count(JModuleHelper::getModules('forums-view-after-forum-'.$sectionNo.'-'.$categoryNo)))
				{
					echo CJFunctions::load_module_position('forums-view-after-forum-'.$sectionNo.'-'.$categoryNo);
				}
				$categoryNo++;
			}
		}
		?>
		</ul>
		<?php
	}
	?>
</div>