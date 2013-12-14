<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', false, true);

// Initialize some field attributes.
$max      = !empty($displayData['max'] ? ' max="' . $displayData['max'] . '"' : '';
$min      = !empty($displayData['min']) ? ' min="' . $displayData['min'] . '"' : '';
$step     = !empty($displayData['step']) ? ' step="' . $displayData['step'] . '"' : '';

$class = $displayData['class'] ? ' class="radio ' .  $displayData['class'] . '"' : ' class="radio"';
$required  = $displayData['required'] ? ' required aria-required="true"' : '';
$autofocus = $displayData['autofocus'] ? ' autofocus' : '';
$disabled  = $displayData['disabled'] ? ' disabled' : '';
$readonly  = $displayData['readonly'] ? ' readonly' : '';

$value = (float)$displayData['value'];

// Initialize JavaScript field attributes.
$onchange = !empty($displayData['onchange']) ? ' onchange="' . $displayData['onchange'] . '"' : '';

?>
<input type="range" name="<?php echo  $displayData['field']->name; ?>" id="<?php echo  $displayData['field']->id; ?>" value="<?php echo 
 htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"<?php echo  $class . $disabled . $readonly
 $onchange . $max . $step . $min . $autofocus; ?> />
