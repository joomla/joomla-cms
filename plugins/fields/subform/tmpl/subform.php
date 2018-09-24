<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Subform
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * One possible way to render a subform field, just by iterating over the rendered values:
 */
echo '<ul>';
foreach ($field->value as $row)
{
	echo '<li>';
	$buffer = array();
	foreach ((array)$row as $key => $value)
	{
		$buffer[] = ($key . ': ' . $value);
	}
	echo implode(', ', $buffer);
	echo '</li>';
}
echo '</ul>';

/**
 * Sometimes the above is not what you want, because you want access to the
 * raw (unrendered) values of all subfields. Then this way could be a possibility:
 */
//echo '<ul>';
//foreach ($field->subfield_rows as $subfield_objects)
//{
//	echo '<li>';
//	$buffer = array();
//	foreach (array_keys(get_object_vars($subfield_objects)) as $subfield_name)
//	{
//		$buffer[] = (
//			$subfield_name . ': '
//			. $subfield_objects->{$subfield_name}->rawvalue
//		);
//	}
//	echo implode(', ', $buffer);
//	echo '</li>';
//}
//echo '</ul>';
/**
 * This example maybe looks a bit odd, but the the idea is that you could use
 * something like this:
 *
 * <ul>
 * <?php foreach ($field->subfield_rows as $row): ?>
 *     <li>
 *         Name: <?php echo $row->name->rawvalue; ?><br />
 *         Image: <?php echo $row->image->value; ?><br />
 *         etc...
 *     </li>
 * <?php endforeach; ?>
 * </ul>
 *
 * This means you have better control over how the output of your subform fields
 * really is because you don't rely on the rendered values of the subform fields
 * themselves.
 */
