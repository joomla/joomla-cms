<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Editor
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

$value = $field->value;

if ($value == '')
{
	return;
}

echo HTMLHelper::_('content.prepare', $value);
