<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

$user    			= JFactory::getUser();
$avatarComponent  	= $this->params->get('avatar_component', 'cjforum');
$profileComponent 	= $this->params->get('profile_component', 'cjforum');
$points				= $this->params->get('points_component', 'cjforum');
$layout 			= $this->params->get('layout', 'default');
$params  			= $this->item->params;
$theme 				= $params->get('theme', 'default');
$rowClass 			= $params->get('layout', 'default') != 'bs3' ? 'row-fluid' : 'row';
$displayName		= $params->get('display_name', 'name');

$api				= new CjLibApi();
$profileUrl 		= $api->getUserProfileUrl($profileComponent, $this->item->id);
$profileUri 		= CjForumHelperRoute::getProfileRoute($this->item->id);
$avatarImage 		= $api->getUserAvatarImage($avatarComponent, $this->item->id, $this->item->email, 192);
$authorName			= $this->escape($this->item->$displayName);
$age 				= JFactory::getDate($this->item->birthday)->diff(JFactory::getDate())->y;

$microdata 			= new JMicrodata('Person');

$apps = array();
$apps['topics'] = array('icon'=>'fa fa-file-o');

if($params->get('integrate_articles', 0) == 1)
{
	$apps['articles'] = array('icon'=>'fa fa-edit');
}

if($params->get('integrate_answers', 0) == 1)
{
	$apps['questions'] = array('icon'=>'fa fa-question-circle');
}

if($params->get('integrate_polls', 0) == 1)
{
	$apps['polls'] = array('icon'=>'fa fa-pie-chart');
}

if($params->get('integrate_quizzes', 0) == 1)
{
	$apps['quizzes'] = array('icon'=>'fa fa-bar-chart');
}

if($params->get('integrate_quotes', 0) == 1)
{
	$apps['quotes'] = array('icon'=>'fa fa-quote-left');
}

if($params->get('integrate_tracks', 0) == 1)
{
	$apps['tracks'] = array('icon'=>'fa fa-map-marker');
}

if($params->get('integrate_surveys', 0) == 1)
{
	$apps['surveys']	= array('icon'=>'fa fa-area-chart');
}
?>

<div id="cj-wrapper" class="profile-details <?php echo $this->pageclass_sfx;?>">
	<?php echo JLayoutHelper::render($layout.'.toolbar', array('params'=>$this->params, 'state'=>$this->state));?>
	
	<div class="container-fluid no-space-left no-space-right" <?php echo $microdata->displayScope();?>>
		<div class="profile-header margin-bottom-10">
			<h1 class="page-header no-space-top no-margin-bottom">
			
				<?php echo $microdata->content($authorName)->property('name')->fallback('Thing', 'name')->display();?>
				
				<?php if($this->item->id == $user->id || $user->authorise('core.manage', 'com_cjforum')):?>
				<small>
					&nbsp;
					<a href="<?php echo JRoute::_('index.php?option=com_cjforum&task=profile.edit&p_id='.$this->item->id.'&return='.base64_encode(JRoute::_($profileUri)));?>">
						(<?php echo JText::_('JGLOBAL_EDIT');?>)
					</a>
				</small>
				<?php endif;?>
			</h1>
		</div>
		<div class="<?php echo $rowClass;?>">
			<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span3' : 'col-lg-3 col-md-3 col-sm-3';?>">
				<div class="profile-avatar">
					<a <?php echo $microdata->property('url')->display();?> href="<?php echo $profileUrl;?>" class="thumbnail margin-bottom-5">
						<img <?php echo $microdata->property('image')->display();?> src="<?php echo $avatarImage;?>" alt="<?php echo $this->escape($this->item->name)?>" 
							class="media-object" style="width: 100%;;"/>
					</a>
				</div>
				<div class="profile-info margin-bottom-5">
					<div class="center text-center user_rank_image">
						<?php echo $microdata->content(CjForumApi::getUserRankImage($this->item->id, $this->params->get('rank_profile', 'default')))->property('jobTitle')->display();?>
					</div>
				</div>
				<div class="profile-icons inline center text-center margin-bottom-10">
					<?php if(!empty($this->item->twitter)):?>
					<a href="https://twitter.com/<?php echo $this->escape($this->item->twitter);?>" title="Twitter" data-toggle="tooltip" target="_blank">
						<i class="fa fa-twitter fa-border"></i>
					</a>
					<?php endif;?>
					<?php if(!empty($this->item->facebook)):?>
					<a href="https://www.facebook.com/<?php echo $this->escape($this->item->facebook);?>" title="Facebook" data-toggle="tooltip" target="_blank">
						<i class="fa fa-facebook fa-border"></i>
					</a>
					<?php endif;?>
					<?php if(!empty($this->item->gplus)):?>
					<a href="https://plus.google.com/<?php echo $this->escape($this->item->gplus);?>" title="Google+" data-toggle="tooltip" target="_blank">
						<i class="fa fa-google-plus fa-border"></i>
					</a>
					<?php endif;?>
					<?php if(!empty($this->item->linkedin)):?>
					<a href="https://www.linkedin.com/profile/view?id=<?php echo $this->escape($this->item->linkedin);?>" title="Linkedin" data-toggle="tooltip" target="_blank">
						<i class="fa fa-linkedin fa-border"></i>
					</a>
					<?php endif;?>
					<?php if(!empty($this->item->flickr)):?>
					<a href="https://www.flickr.com/photos/<?php echo $this->escape($this->item->flickr);?>" title="Flickr" data-toggle="tooltip" target="_blank">
						<i class="fa fa-flickr fa-border"></i>
					</a>
					<?php endif;?>
					<?php if(!empty($this->item->skype)):?>
					<a href="skype:<?php echo $this->escape($this->item->skype);?>" title="Skype" data-toggle="tooltip">
						<i class="fa fa-skype fa-border"></i>
					</a>
					<?php endif;?>
				</div>
			</div>
			<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span4' : 'col-lg-4 col-md-4 col-sm-4';?>">
				<table class="table table-striped table-hover table-condensed">
					<tbody>
						<?php if(!empty($this->item->website) && $this->params->get('profile_show_website', 1) == 1):?>
						<tr>
							<th><?php echo JText::_('COM_CJFORUM_PROFILE_FIELD_WEBSITE');?></th>
							<td><?php echo $this->escape($this->item->website);?></td>
						</tr>
						<?php endif;?>
						
						<?php if(!empty($this->item->location) && $this->params->get('profile_show_location', 1) == 1):?>
						<tr>
							<th><?php echo JText::_('COM_CJFORUM_PROFILE_FIELD_LOCATION');?></th>
							<td><?php echo $microdata->content($this->escape($this->item->location))->property('homeLocation')->display();?></td>
						</tr>
						<?php endif;?>
						
						<?php if($this->params->get('profile_show_gender', 1) == 1):?>
						<tr>
							<th><?php echo JText::_('COM_CJFORUM_PROFILE_FIELD_GENDER');?></th>
							<td><?php echo $microdata->content(JText::sprintf('COM_CJFORUM_PROFILE_GENDER_'.((int)$this->item->gender)))->property('gender')->display();?></td>
						</tr>
						<?php endif;?>
						
						<?php if($this->params->get('profile_show_age', 1) == 1):?>
						<tr>
							<th><?php echo JText::_('COM_CJFORUM_PROFILE_FIELD_AGE');?></th>
							<td><?php echo ($age > 150 || $age < 2) ? JText::_('COM_CJFORUM_NOT_SPECIFIED') : JText::sprintf('COM_CJFORUM_PROFILE_AGE', $age);?></td>
						</tr>
						<?php endif;?>
						
						<?php if($this->params->get('profile_show_member_since', 1) == 1):?>
						<tr>
							<th><?php echo JText::_('COM_CJFORUM_MEMBER_SINCE');?></th>
							<td><?php echo JFactory::getDate($this->item->registerDate)->format('F jS, Y');?></td>
						</tr>
						<?php endif;?>
						
						<?php if($this->params->get('profile_show_last_visit', 1) == 1):?>
						<tr>
							<th><?php echo JText::_('COM_CJFORUM_LAST_SEEN');?></th>
							<td><?php echo CjLibDateUtils::getHumanReadableDate($this->item->lastvisitDate);?></td>
						</tr>
						<?php endif;?>
						
						<tr class="info">
							<th><?php echo JText::_('COM_CJFORUM_TOPICS');?></th>
							<td><?php echo CjLibUtils::formatNumber($this->item->topics);?></td>
						</tr>
						<tr class="warning">
							<th><?php echo JText::_('COM_CJFORUM_REPLIES');?></th>
							<td>
								<span data-toggle="tooltip" title="<?php echo $this->item->replies;?>">
									<?php echo CjLibUtils::formatNumber($this->item->replies);?>
								</span>
							</td>
						</tr>
						<?php if($points != 'none'):?>
						<tr class="success">
							<th><?php echo JText::_('COM_CJFORUM_POINTS');?></th>
							<td>
								<span data-toggle="tooltip" title="<?php echo $this->item->points;?>">
									<?php echo $microdata->content(CjLibUtils::formatNumber($this->item->points))->property('award')->display();?>
								</span>
							</td>
						</tr>
						<?php endif;?>
					</tbody>
				</table>
			</div>
			<div class="<?php echo $this->params->get('layout', 'default') != 'bs3' ? 'span5' : 'col-lg-5 col-md-5 col-sm-5';?>">
				<div class="panel panel-<?php echo $theme?>" style="max-height: 250px; overflow-y: auto;">
					<div class="panel-body">
						<?php 
						if(!empty($this->item->about))
						{
							echo $this->item->about;
						}
						else 
						{
							echo JText::sprintf('COM_CJFORUM_MESSAGE_NO_INFORMATION_PROVIDED', $this->escape($this->item->name));
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<?php if(count($this->item->fields)):?>
	<div class="custom-profile-fields">
		<div class="panel panel-<?php echo $layout;?>">
			<div class="panel-body">
				<dl class="dl-horizontal">
					<?php foreach ($this->item->fields as $label=>$value):?>
					<dt><?php echo JText::_('PLG_CJFORUM_'.$label.'_LABEL', true);?></dt>
					<dd><?php echo $this->escape($value);?></dd>
					<?php endforeach;?>
				</dl>
			</div>
		</div>
	</div>
	<?php endif;?>
	
	<?php if(count(JModuleHelper::getModules('profile-view-above-summary'))):?>
	<div class="margin-top-10">
		<?php echo CJFunctions::load_module_position('profile-view-above-summary');?>
	</div>
	<?php endif;?>
	
	<div class="margin-top-10">
		<div class="tabpanel" id="profile-tabs">
			<ul class="nav nav-tabs" role="tablist" style="border-bottom: none;">
				<li role="presentation"<?php echo $this->layout == 'summary' ? ' class="active"' : '';?>>
					<a href="<?php echo JRoute::_($profileUri.'&layout=summary');?>"><i class="fa fa-home"></i> <?php echo JText::_('COM_CJFORUM_SUMMARY');?></a>
				</li>
				<li role="presentation"<?php echo $this->layout == 'reputation' ? ' class="active"' : '';?>>
					<a href="<?php echo JRoute::_($profileUri.'&layout=reputation');?>"><i class="fa fa-trophy"></i> <?php echo JText::_('COM_CJFORUM_REPUTATION');?></a>
				</li>
				<li role="presentation"<?php echo $this->layout == 'activity' ? ' class="active"' : '';?>>
					<a href="<?php echo JRoute::_($profileUri.'&layout=activity');?>"><i class="fa fa-tasks"></i> <?php echo JText::_('COM_CJFORUM_ACTIVITY');?></a>
				</li>
				<li role="presentation" class="dropdown<?php echo in_array($this->layout, array_keys($apps)) ? ' active' : '';?>">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-expanded="false">
						<i class="fa fa-cogs"></i> <?php echo JText::_('COM_CJFORUM_APPS_LABEL');?> <span class="caret"></span>
					</a>
					<?php 
					$currentIdx = 0;
					$totalRows = count($apps);
					$numCols = $totalRows > 5 ? 2 : 1;
					$span = 12 / $numCols;
					$maxRows = ceil($totalRows / $numCols);
					?>
					<ul class="dropdown-menu multi-column columns-<?php echo $numCols;?>" role="menu">
						<li>
							<div class="row">
								<?php 
								foreach ($apps as $appName=>$options)
								{
									if($currentIdx == 0 || $currentIdx == $maxRows)
									{
										?>
										<div class="span<?php echo $span;?> col-sm-<?php echo $span;?>">
											<ul class="multi-column-dropdown">
										<?php
									}
									?>
									<li role="presentation"<?php echo $this->layout == $appName ? ' class="active"' : '';?>>
										<a href="<?php echo JRoute::_($profileUri.'&layout='.$appName);?>">
											<i class="<?php echo $options['icon'];?>"></i> <?php echo JText::_('COM_CJFORUM_'.$appName.'_LABEL');?>
										</a>
									</li>
									<?php
									if($currentIdx == $maxRows - 1 || $currentIdx == $totalRows)
									{
										?>
											</ul>
										</div>
										<?php
									}
									$currentIdx++;
								}
								?>
							</div>
						</li>
					</ul>
				</li>
			</ul>
		</div>
		<hr class="no-space-top">
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active">
				<?php echo $this->loadTemplate($this->layout);?>
			</div>
		</div>
	</div>
	
	<?php echo JLayoutHelper::render($layout.'.credits', array('params'=>$this->params));?>
	<div style="display: none">
		<input id="cjforum_pageid" value="profile" type="hidden">
	</div>
</div>