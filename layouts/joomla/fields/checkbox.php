<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// Initialize some field attributes.
$class     = !empty($displayData['class']) ? ' class="' . $displayData['class'] . '"' : '';
$disabled  = $displayData['disabled'] ? ' disabled' : '';
$value     = !empty($displayData['default']) ? $displayData['default'] : '1';
$required  = $displayData['required'] ? ' required aria-required="true"' : '';
$autofocus = $displayData['autofocus'] ? ' autofocus' : '';
$checked   = $displayData['checked'] || !empty($displayData['value']) ? ' checked' : '';

// Initialize JavaScript field attributes.
$onclick  = $displayData['onclick'] ;
$onchange = $displayData['onchange'] ;

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', false, true);
?>
<input type="checkbox" name="<?php echo $displayData['field']->name; ?>" id="<?php echo $displayData['field']->id; ?>" value="<?php echo 
 htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?>"<?php echo $class . $checked . $disabled . $onclick . $onchange
	. $required . $autofocus ?> />
