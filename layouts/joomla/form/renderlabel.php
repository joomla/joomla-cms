<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

/**
 * Layout variables
 * ---------------------
 * 	$text         : (string)  The label text
 * 	$description  : (string)  An optional description to use in a tooltip
 * 	$for          : (string)  The id of the input this label is for
 * 	$required     : (boolean) True if a required field
 * 	$classes      : (array)   A list of classes
 * 	$position     : (string)  The tooltip position. Bottom for alias
 */

$classes = array_filter((array) $classes);

$id    = $for . '-lbl';
$title = '';

if (!empty($description))
{
	if (!$position && Factory::getLanguage()->isRtl())
	{
		$position = 'left';
	}
	elseif ($position === 'top')
	{
		$position = 'up';
	}
	else
	{
		$position = 'up';
	}

	if ($text && $text !== $description)
	{
		$title = ' data-balloon="'
			. HTMLHelper::_(
					'uitips.tipText',
					'',
					htmlspecialchars($text . $description),
					false,
					true
			) . '"'
			. ' data-balloon-pos="' . $position . '"'
			. ' data-balloon-length="medium"';
	}
	else
	{
		$title = ' data-balloon="'
			. HTMLHelper::_('uitips.tipText', '', htmlspecialchars($text), false, true) . '"'
			. ' data-balloon-pos="' . $position . '"'
			. ' data-balloon-length="medium"';
	}
}

if ($required)
{
	$classes[] = 'required';
}
?>
<label id="<?php echo $id; ?>" for="<?php echo $for; ?>"<?php if (!empty($classes)) echo ' class="' . implode(' ', $classes) . '"'; ?><?php echo $title; ?>>
	<?php echo $text; ?><?php if ($required) : ?><span class="star">&#160;*</span><?php endif; ?>
</label>
