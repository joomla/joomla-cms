<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string   $name       The radio list name
 * @var  string   $id_text    The radio list id
 * @var  string   $optKey     The radio list key
 * @var  string   $optText    The radio list text
 * @var  bool     $translate  Is it translatable?
 * @var  array    $selected   Is it selected?
 * @var  array    $attribs    The radio list attributes
 * @var  array    $data       The radio list data
 */

extract($displayData);


if (is_array($attribs))
{
	$attribs = JArrayHelper::toString($attribs);
}
?>

<div class="controls">
<?php foreach ($data as $obj) : ?>
<?php
	// We will do some processing
	$k = $obj->$optKey;
	$t = $translate ? JText::_($obj->$optText) : $obj->$optText;
	$id = (isset($obj->id) ? $obj->id : null);
	$extra = '';
	$id = $id ? $obj->id : $id_text . $k;
	if (is_array($selected))
	{
		foreach ($selected as $val)
		{
			$k2 = is_object($val) ? $val->$optKey : $val;
			if ($k == $k2)
			{
				$extra .= ' selected="selected" ';
				break;
			}
		}
	}
	else
	{
		$extra .= ((string) $k == (string) $selected ? ' checked="checked" ' : '');
	}
	// Now we can write the repeatable part ?>
	<label for="<?php echo $id; ?>" id="<?php echo $id; ?>-lbl" class="radio">
		<input type="radio" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo $k; ?>" <?php echo $extra; ?><?php echo $attribs; ?>><?php echo $t; ?>
	</label>
<?php endforeach; ?>
</div>
