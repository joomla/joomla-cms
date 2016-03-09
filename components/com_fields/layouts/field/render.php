<?php
/**
 * @package    Fields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

if (! key_exists('field', $displayData))
{
	return;
}

$field = $displayData['field'];
$label = $field->label;
$value = $field->value;
if (! $value)
{
	return;
}

$class = $field->render_class;
?>

<dd class="dpfield-entry <?php echo $class;?>" id="dpfield-entry-<?php echo $field->id;?>">
	<span class="dpfield-label"><?php echo htmlentities($label);?>: </span>
	<span class="dpfield-value"><?php echo $value;?></span>
</dd>
