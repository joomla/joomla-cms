<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// JLayout for standard handling of associations fields in the administrator items edit screens.
if ($displayData->getForm()->getValue('id') != 0 && $displayData->getForm()->getValue('language') != "*")
{
	echo $displayData->getForm()->renderFieldset('item_associations');
}
else
{
	echo '<div class="alert alert-info">' . JText::_('JGLOBAL_ASSOC_NOT_POSSIBLE') . '</div>';
	echo '<div class="hidden">' . $displayData->getForm()->renderFieldset('item_associations') . '</div>';
}