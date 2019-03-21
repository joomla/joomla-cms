<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_search
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Module\Search\Site\Helper\SearchHelper;

$set_Itemid = (int) $params->get('set_itemid', 0);
$mitemid    = $set_Itemid > 0 ? $set_Itemid : $app->input->getInt('Itemid');

if ($params->get('opensearch', 1))
{
	$ostitle = $params->get('opensearch_title', Text::_('MOD_SEARCH_SEARCHBUTTON_TEXT') . ' ' . $app->get('sitename'));
	$app->getDocument()->addHeadLink(
		Uri::getInstance()->toString(array('scheme', 'host', 'port')) . Route::_('&option=com_search&format=opensearch&Itemid=' . $mitemid),
		'search',
		'rel',
		[
			'title' => htmlspecialchars($ostitle, ENT_COMPAT, 'UTF-8'),
			'type' => 'application/opensearchdescription+xml'
		]
	);
}

$upper_limit = $app->getLanguage()->getUpperLimitSearchWord();
$button      = $params->get('button', 0);
$imagebutton = $params->get('imagebutton', 0);
$button_text = htmlspecialchars($params->get('button_text', Text::_('MOD_SEARCH_SEARCHBUTTON_TEXT')), ENT_COMPAT, 'UTF-8');
$maxlength   = $upper_limit;
$text        = htmlspecialchars($params->get('text', Text::_('MOD_SEARCH_SEARCHBOX_TEXT')), ENT_COMPAT, 'UTF-8');
$label       = htmlspecialchars($params->get('label', Text::_('MOD_SEARCH_LABEL_TEXT')), ENT_COMPAT, 'UTF-8');

if ($imagebutton)
{
	$img = SearchHelper::getSearchImage($button_text);
}

require ModuleHelper::getLayoutPath('mod_search', $params->get('layout', 'default'));
