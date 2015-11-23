<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

$app 				= JFactory::getApplication();
$user 				= JFactory::getUser();

$params 			= $displayData['params'];
$theme 				= $params->get('theme', 'default');
$avatarComponent	= $params->get('avatar_component', 'cjforum');
$avatarSize			= $params->get('topic_avatar_size', 96);
$profile 			= $params->get('profile_component', 'cjforum');

$api 				= new CjLibApi();
$profileUrl			= $api->getUserProfileUrl($profile, $user->id);
$profileName		= $user->guest ? JText::_('COM_CJFORUM_GUEST') : $this->escape($user->name);
$profileImage 		= $api->getUserAvatarImage($avatarComponent, $user->id, $user->email, 48, true);
?>
<?php if($params->get('show_header_block', 1) == 1):?>
<div class="panel panel-<?php echo $theme;?>">
	<div class="panel-heading">
		<h3 class="panel-title"><?php echo JText::sprintf('COM_CJFORUM_FORUM_HEADER', $app->getCfg('sitename'));?></h3>
	</div>
	<div class="panel-body">
		<?php if(!$user->guest):?>
		<div class="pull-right hidden-phone">
			<div><a href="#" onclick="document.toolbarAuthorForm.submit();">&diams; <?php echo JText::_('COM_CJFORUM_VIEW_YOUR_TOPICS');?></a></div>
			<div><a href="<?php echo JRoute::_($profileUrl.'?layout=activity')?>">&diams; <?php echo JText::_('COM_CJFORUM_VIEW_YOUR_ACTIVITY');?></a></div>
		</div>
		<?php endif;?>
		<div class="media no-space-top" style="margin-top: 0;">
			<?php if($avatarComponent != 'none'):?>
			<div class="media-left hidden-phone hidden-xs">
				<a href="<?php echo $profileUrl?>" class="thumbnail no-margin-bottom" style="margin-bottom: 0;">
					<img alt="<?php echo $profileName;?>" src="<?php echo $profileImage;?>" class="media-object" style="max-width: 48px;">
				</a>
			</div>
			<?php endif;?>
			<div class="media-body">
				<div><strong><?php echo JText::sprintf('COM_CJFORUM_WELCOME_TEXT', $user->guest ? $profileName : JHtml::link($profileUrl, $profileName));?></strong></div>
				
				<?php 
				if($user->guest)
				{
					require_once JPATH_ADMINISTRATOR . '/components/com_users/helpers/users.php';
					$twofactormethods = UsersHelper::getTwoFactorMethods();
					?>
					<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="cj-login-form" class="form-inline margin-top-10 no-margin-bottom">
						<input id="modlgn-username" type="text" name="username" class="input-small" tabindex="0" size="18" placeholder="<?php echo JText::_('COM_CJFORUM_USERNAME') ?>" />
						<input id="modlgn-passwd" type="password" name="password" class="input-small" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" />
						
						<?php if (count($twofactormethods) > 1): ?>
						<input id="modlgn-secretkey" autocomplete="off" type="text" name="secretkey" class="input-small" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY') ?>" />
						<?php endif; ?>
						
						<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
						<label for="rememberme" class="checkbox"><input id="rememberme" type="checkbox" name="remember" class="inputbox" value="yes"/> <?php echo JText::_('COM_CJFORUM_REMEMBER_ME') ?></label>
						<?php endif; ?>
						
						<button type="submit" tabindex="0" name="Submit" class="btn btn-primary"><?php echo JText::_('JLOGIN') ?></button>
						<input type="hidden" name="option" value="com_users" />
						<input type="hidden" name="task" value="user.login" />
						<input type="hidden" name="return" value="<?php echo base64_encode(JUri::getInstance()->toString());?>" />
						<?php echo JHtml::_('form.token'); ?>
					</form>
					<?php 
				}
				else 
				{
					?>
					<div><?php echo JText::sprintf('COM_CJFORUM_USER_LAST_VISITED_ON', CjLibDateUtils::getHumanReadableDate($user->lastvisitDate));?></div>
					<a href="#" onclick="document.cjforum_logout_form.submit(); return false;"><?php echo JText::_('JLOGOUT');?></a>
					<form id="cw_logout_form" name="cjforum_logout_form" action="<?php echo JRoute::_('index.php', true, $params->get('usesecure'));?>" method="post" style="display: none;">
						<input type="hidden" name="option" value="com_users"/> 
						<input type="hidden" name="task" value="user.logout"/> 
						<input type="hidden" name="return" value="<?php echo base64_encode(JRoute::_('index.php', false, $params->get('usesecure')));?>"/>
						<?php echo JHTML::_( 'form.token' ); ?>
					</form>
					<?php 
				}
				?>
			</div>
		</div>
	</div>
</div>
<?php endif;?>