<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_latest
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\ArticlesLatest\Site\Helper\ArticlesLatestHelper;

$list            = ArticlesLatestHelper::getList($params);

require ModuleHelper::getLayoutPath('mod_articles_latest', $params->get('layout', 'default'));
