<?php
/**
 * @package     corejoomla.site
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2015 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT . '/components/com_cjlib/framework.php';
require_once JPATH_ROOT . '/components/com_cjforum/helpers/route.php';
require_once JPATH_ROOT . '/modules/mod_cjforumcategories/helper.php';

CJLib::import('corejoomla.framework.core');
jimport('joomla.application.categories');
CJFunctions::load_jquery(array('libs' => array('treeview')));

$app = JFactory::getApplication();
$categories = JCategories::getInstance('CjForum', array('countItems' => true, 'assetid' => 'cjforumcategories'));

if (is_object($categories))
{
	$root = intval($params->get('catid', 0));
	$excluded = trim($params->get('excluded', ''));
	$excluded = explode(',', $excluded);
	JArrayHelper::toInteger($excluded);
	
	$nodes = $categories->get($root);
	$fields = array();
	$script = '';
	
	if ($nodes)
	{
		$nodes = $nodes->getChildren(false);
		$catid = $app->input->getInt('id', 0);
		$appname = $app->input->getCmd('option', '');
		$view = $app->input->getCmd('view', '');
		
		if ($view == 'category' && $appname == 'com_cjforum' && $catid > 0)
		{
			$script = '
				jQuery(".cjforumcategories").find("li[rel=\'' . $catid . '\']").find(".expandable-hitarea:first").click();
				jQuery(".cjforumcategories").find("li[rel=\'' . $catid . '\']").parents("li.expandable").find(".expandable-hitarea:first").click();
				jQuery(".cjforumcategories").find("li[rel=\'' . $catid . '\']").find("a:first").css("font-weight", "bold");';
		}
		
		JFactory::getDocument()->addScriptDeclaration(
			'jQuery(document).ready(function($){'.
				'jQuery(".cjforumcategories").find(".cat-list:first").treeview({"collapsed": true});' . $script  . 'jQuery(".cjforumcategories").show();'.
			'});'
		);
		
		echo '<div class="cjforumcategories" style="display: none;">' . CjForumCategoriesHelper::getCategories($nodes, $excluded) . '</div>';
	}
}