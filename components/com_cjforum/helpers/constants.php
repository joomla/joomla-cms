<?php 
/**
 * @package     corejoomla.site
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

defined('CF_CURR_VERSION') or define('CF_CURR_VERSION',				'1.1.0');
defined('CF_MEDIA_DIR') or define('CF_MEDIA_DIR',					JPATH_ROOT.'/media/com_cjforum/');
defined('CF_MEDIA_URI') or define('CF_MEDIA_URI',					JURI::root(true).'/media/com_cjforum/');
defined('CF_BADGES_BASE_DIR') or define('CF_BADGES_BASE_DIR',		JPATH_ROOT.'/images/badges/');
defined('CF_BADGES_BASE_URI') or define('CF_BADGES_BASE_URI',		JURI::root(true).'/images/badges/');
defined('CF_AVATAR_BASE_DIR') or define('CF_AVATAR_BASE_DIR',		JPATH_ROOT.'/images/avatar/');
defined('CF_AVATAR_BASE_URI') or define('CF_AVATAR_BASE_URI',		JURI::root(true).'/images/avatar/');
defined('CF_PLUGINS_BASE_DIR') or define('CF_PLUGINS_BASE_DIR',		JPATH_ROOT.'/media/cjforum/plugins/');
defined('CF_RANK_IMAGES_URI') or define('CF_RANK_IMAGES_URI', 		JUri::root(true).'/images/ranks/');
defined('CF_ATTACHMENTS_DIR') or define('CF_ATTACHMENTS_DIR',		JPATH_ROOT.'/media/cjforum/attachments/');
defined('CF_ATTACHMENTS_PATH') or define('CF_ATTACHMENTS_PATH',		'media/cjforum/attachments');
defined('CF_ATTACHMENTS_URI') or define('CF_ATTACHMENTS_URI',		JURI::root(true).'/media/cjforum/attachments');

defined('ITEM_TYPE_TOPIC') or define('ITEM_TYPE_TOPIC', 			1);
defined('ITEM_TYPE_REPLY') or define('ITEM_TYPE_REPLY', 			2);
defined('CF_ASSET_ID') or define('CF_ASSET_ID', 					10);
?>