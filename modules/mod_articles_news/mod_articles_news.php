<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_news
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\ArticlesNews\Site\Helper\ArticlesNewsHelper;

$list = ArticlesNewsHelper::getList($params);

require ModuleHelper::getLayoutPath('mod_articles_news', $params->get('layout', 'horizontal'));
