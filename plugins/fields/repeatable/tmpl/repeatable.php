<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Repeatable
 *
 * @copyright   © 2016 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$fieldValue = $field->value;

if ($fieldValue === '')
{
	return;
}

// Get the values
$fieldValues = json_decode($fieldValue, true);

if (empty($fieldValues))
{
	return;
}

$html = '<ul>';

foreach ($fieldValues as $value)
{
	$html .= '<li>' . implode(', ', $value) . '</li>';
}

$html .= '</ul>';

echo $html;
