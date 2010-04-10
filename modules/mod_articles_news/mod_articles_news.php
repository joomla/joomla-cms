<?php
/**
 * @version		$Id: mod_articles_news.php 15191 2010-03-05 06:59:51Z klascommit $
 * @package		Joomla.Site
 * @subpackage	mod_articles_news
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).DS.'helper.php';

$list = modArticlesNewsHelper::getList($params);
require JModuleHelper::getLayoutPath('mod_articles_news', $params->get('layout', 'default'));
