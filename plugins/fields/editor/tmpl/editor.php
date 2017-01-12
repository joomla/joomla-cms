<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Editor
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$value = $field->value;

if ($value == '')
{
	return;
}

echo JHTML::_('content.prepare', $value);
