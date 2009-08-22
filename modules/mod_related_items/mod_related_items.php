<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_related_items
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
*/

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'helper.php';

$list = modRelatedItemsHelper::getList($params); // get return results from the helper
$articleView = modRelatedItemsHelper::isArticle(); // is this an article?
$subtitle = '';


if (!count($list)) {  // no articles to list. check whether we want to show some text
	//return;
	if ($articleView != 'true' && ($params->def('notArticleText','')))
	{
		$subtitle = $params->def('notArticleText','');
	}
	else if ($params->def('noRelatedText','') && $articleView == 'true')
	{
		$subtitle = $params->def('noRelatedText','');
	}
	else 
	{
		return;
	}
}

// choose layout based on ordering parameter
if ($params->get('ordering') == 'keyword_article' && count($list))
{
	$layout = 'keyword';
	$outputArray = modRelatedItemsHelper::getListByKeyword($list, $params);
}
else 
{
	$layout = 'default';
}
$path = JModuleHelper::getLayoutPath('mod_related_items', $layout);
if (file_exists($path)) {
	require($path);
}
