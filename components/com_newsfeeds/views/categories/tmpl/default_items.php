<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Add the helper for the category URL in the JLayout
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers');

// Collate the data to pass into the JLayout (some of these variables are private
// so we can't pass them directly into the JLayout)
$displayData = new stdClass;
$displayData->parent = $this->parent;
$displayData->extension = $this->extension;
$displayData->items = $this->items;
$displayData->params = $this->params;
$displayData->maxLevelcat = $this->maxLevelcat;

echo JLayoutHelper::render('joomla.content.categories_default_items', $displayData);
