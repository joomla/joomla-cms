<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_popular
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\Module\ArticlesPopular\Site\Helper\ArticlesPopularHelper;

// Exit early if hits are disabled.
if (!ComponentHelper::getParams('com_content')->get('record_hits', 1))
{
	echo Text::_('JGLOBAL_RECORD_HITS_DISABLED');

	return;
}

$list = ArticlesPopularHelper::getList($params);

require ModuleHelper::getLayoutPath('mod_articles_popular', $params->get('layout', 'default'));
