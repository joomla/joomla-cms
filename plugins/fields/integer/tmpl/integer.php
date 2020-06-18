<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Integer
 *
 * @copyright   © 2006 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$value = $field->value;

if ($value == '')
{
	return;
}

if (is_array($value))
{
	$value = implode(', ', array_map('intval', $value));
}
else
{
	$value = (int) $value;
}

echo $value;
