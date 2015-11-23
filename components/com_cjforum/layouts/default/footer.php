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
$cache 				= JFactory::getCache();

$params 			= $displayData['params'];
$theme 				= $params->get('theme', 'default');
$profileComponent	= $params->get('profile_component', 'cjforum');
$displayName		= $params->get('display_name', 'name');

JLoader::import('statistics', JPATH_COMPONENT.'/models');
$model = JModelLegacy::getInstance( 'statistics', 'CjForumModel' );

$api = new CjLibApi();
$return = $cache->call(array($model, 'getForumStatistics'));
$latest = $return->latestMember ? $api->getUserProfileUrl($profileComponent, $return->latestMember->id, false, $this->escape($return->latestMember->$displayName)) : 'N/A';
?>
<?php if($params->get('show_footer_block', 1) == 1):?>

<h3 class="cjheader"><?php echo JText::sprintf('COM_CJFORUM_FORUM_STATISTICS', $app->getCfg('sitename'));?></h3>
<?php echo JText::sprintf('COM_CJFORUM_TOTAL_POSTS', $return->topics + $return->replies);?> &bull; 
<?php echo JText::sprintf('COM_CJFORUM_TOTAL_TOPICS', $return->topics);?> &bull; 
<?php echo JText::sprintf('COM_CJFORUM_TOTAL_MEMBERS', $return->users);?> &bull; 
<?php echo JText::sprintf('COM_CJFORUM_OUR_LATEST_MEMBER', $latest);?>

<?php endif;?>