<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $text      The label text
 * @var   string   $for       The id of the input this label is for
 * @var   boolean  $required  True if a required field
 * @var   array    $classes   A list of classes
 */

$classes = array_filter((array) $classes);

// $labelId is the id of the label element itself (always $for . '-lbl').
// NOTE: Do not use $id here — $id in $displayData is the input control's id,
// not the label's own id. Using $id ?? ... would silently reuse the input id
// because extract() already sets $id to a non-null value for every field.
$labelId = $for ? $for . '-lbl' : null;
$tag     = empty($for) ? 'span' : 'label';

if ($required) {
    $classes[] = 'required';
}

?>
<<?php echo $tag; ?><?php if ($labelId) : ?> id="<?php echo $labelId; ?>"<?php endif; ?><?php if (!empty($for)) : ?> for="<?php echo $for; ?>"<?php endif; ?><?php if (!empty($classes)) {
    echo ' class="' . implode(' ', $classes) . '"';
           } ?>>
    <?php echo $text; ?><?php if ($required) :
        ?><span class="star" aria-hidden="true">&#160;*</span><?php
    endif; ?>
</<?php echo $tag; ?>>
