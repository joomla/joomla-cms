<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$div = new JHtmlElement('div', array('class' => 'categories-list' . $displayData->pageclass_sfx));

if ($displayData->params->get('show_page_heading'))
{
	$div->addChild('h1', array(), $displayData->escape($displayData->params->get('page_heading')));
}

if ($displayData->params->get('show_base_description'))
{
	$descDiv = $div->addChild('div', array('class' => 'category-desc base-desc'));

	// If there is a description in the menu parameters use that
	if($displayData->params->get('categories_description'))
	{
		$descDiv->setInnerHtml(JHtml::_('content.prepare', $displayData->params->get('categories_description'), '',  $displayData->get('extension') . '.categories'));
	}
	elseif($displayData->parent->description)
	{
		$descDiv->setInnerHtml(JHtml::_('content.prepare', $displayData->parent->description, '', $displayData->parent->extension . '.categories'));
	}
}

echo $div;
