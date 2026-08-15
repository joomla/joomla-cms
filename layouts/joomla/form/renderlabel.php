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

// Allow the layout to accept a pre-defined $id, otherwise fallback to $for . '-lbl'
$id  = $id ?? ($for ? $for . '-lbl' : '');
$tag = empty($for) ? 'span' : 'label';

if ($required) {
    $classes[] = 'required';
}

?>
<<?php echo $tag; ?><?php if ($id) : ?> id="<?php echo $id; ?>"<?php endif; ?><?php if (!empty($for)) : ?> for="<?php echo $for; ?>"<?php endif; ?><?php if (!empty($classes)) {
    echo ' class="' . implode(' ', $classes) . '"';
           } ?>>
    <?php echo $text; ?><?php if ($required) :
        ?><span class="star" aria-hidden="true">&#160;*</span><?php
    endif; ?>
</<?php echo $tag; ?>>
