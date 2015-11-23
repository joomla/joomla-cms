<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  mod_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

// CJLib includes
require_once JPATH_ROOT.'/components/com_cjlib/framework.php';
require_once JPATH_SITE.'/components/com_cjforum/router.php';
require_once JPATH_SITE.'/components/com_cjforum/lib/api.php';
require_once JPATH_SITE.'/components/com_cjforum/helpers/route.php';
CJLib::import('corejoomla.framework.core');

// Get the properties
$user 				= JFactory::getUser();
$language			= JFactory::getLanguage();
$api 				= new CjLibApi();

$appParams 			= JComponentHelper::getParams('com_cjforum');
$avatarComponent	= trim($appParams->get('avatar_component', 'none'));
$profileComponent 	= trim($appParams->get('profile_component', 'none'));

$show_trending		= intval($params->get('show_trending', '1'));
$show_latest		= intval($params->get('show_latest', '1'));
$show_popular		= intval($params->get('show_popular', '1'));
$show_most_liked	= intval($params->get('show_liked', '1'));

$show_category		= intval($params->get('show_category', '1'));
$show_author		= intval($params->get('show_author', '1'));
$show_date			= intval($params->get('show_date', '1'));
$show_replies		= intval($params->get('show_replies', '1'));
$show_description 	= intval($params->get('show_description', '1'));
$show_avatar 		= intval($params->get('show_avatar', '1'));
$show_tabs			= intval($params->get('show_tabs', '1'));
$avatar_size 		= intval($params->get('avatar_size', '32'));
$topics_limit		= intval($params->get('topics_count', '5'));
$introtext_length	= intval($params->get('introtext_length', '250'));
$load_bootstrap		= intval($params->get('load_bootstrap', '1'));
$tab_order			= trim($params->get('tabs_order', 'T,P,L'));
$categories			= $params->get('filter_category', array());

if($load_bootstrap)
{
	CjLib::behavior('bootstrap', array('loadcss' => false));
	CJLib::behavior('bscore', array('customtag'=>false));
}

$language->load('com_cjforum', JPATH_ROOT);

// get the items to display from the helper
JLoader::import('topics', JPATH_ROOT.'/components/com_cjforum/models');
$model = JModelLegacy::getInstance( 'topics', 'CjForumModel' );
$state = $model->getState(); // access the state first so that it can be modified
$model->setState('list.limit', $topics_limit);
$model->setState('filter.category_id', $categories);

$suggestions 	= array();
$order 			= explode(',', $tab_order);

foreach ($order as $i=>$tab)
{
	if((strcmp($tab,"T") == 0) && $show_trending)
	{
		$model->setState('list.ordering', 'a.replied');
		$model->setState('list.direction', 'desc');
		
		$suggestion 		= new stdClass();
		$suggestion->title	= JText::_('MOD_CJFORUM_TRENDING_TOPICS_LABEL');
		$suggestion->list	= $model->getItems();
		$suggestions[]		= $suggestion;
	}
	 
	if((strcmp($tab,"P") == 0) && $show_popular)
	{
		$model->setState('list.ordering', 'a.hits');
		$model->setState('list.direction', 'desc');
		
		$suggestion 		= new stdClass();
		$suggestion->title	= JText::_('MOD_CJFORUM_POPULAR_TOPICS_LABEL');
		$suggestion->list	= $model->getItems();
		$suggestions[]		= $suggestion;
	}

	if((strcmp($tab,"L") == 0) && $show_latest)
	{
		$model->setState('list.ordering', 'a.created');
		$model->setState('list.direction', 'desc');
		
		$suggestion 		= new stdClass();
		$suggestion->title	= JText::_('MOD_CJFORUM_LATEST_TOPICS_LABEL');
		$suggestion->list	= $model->getItems();
		$suggestions[]		= $suggestion;
	}

	if((strcmp($tab,"M") == 0) && $show_most_liked)
	{
		$model->setState('list.ordering', 'a.likes');
		$model->setState('list.direction', 'desc');
	
		$suggestion 		= new stdClass();
		$suggestion->title	= JText::_('MOD_CJFORUM_MOST_LIKED_TOPICS_LABEL');
		$suggestion->list	= $model->getItems();
		$suggestions[]		= $suggestion;
	}
}

echo '<div id="cj-wrapper">';
if($show_tabs)
{
	echo '<ul class="nav nav-tabs" data-tabs="tabs" style="margin-bottom: 10px;">';
	foreach ($suggestions as $i=>$suggestion)
	{
		echo '<li'.($i == 0 ? ' class="active"' : '').'><a href="#cjforumtab-'.($i+1).'" data-toggle="tab">'.$suggestion->title.'</a></li>';
	}
	reset($suggestions);
	echo '</ul>';
	echo '<div class="tab-content">';
}

foreach ($suggestions as $i=>$suggestion)
{
	echo '<div id="cjforumtab-'.($i + 1).'"'.($show_tabs ? ' class="tab-pane fade'.($i == 0 ? ' in active' : '').'"' : '').'>';
	require(JModuleHelper::getLayoutPath("mod_cjforum"));
	echo '</div>';
}

if($show_tabs)
{
	echo '</div>';
}
echo '</div>';