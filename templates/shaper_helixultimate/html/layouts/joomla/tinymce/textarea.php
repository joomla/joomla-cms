<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('JPATH_BASE') or die;

$data = $displayData;

$doc = \JFactory::getDocument();
$doc->addStylesheet(JURI::root(true) . '/plugins/system/helixultimate/assets/css/icomoon.css');

?>
<textarea
	name="<?php echo $data->name; ?>"
	id="<?php echo $data->id; ?>"
	cols="<?php echo $data->cols; ?>"
	rows="<?php echo $data->rows; ?>"
	style="width: <?php echo $data->width; ?>; height: <?php echo $data->height; ?>;"
	class="<?php echo empty($data->class) ? 'mce_editable form-control' : 'form-control ' . $data->class; ?>"
	<?php echo $data->readonly ? ' readonly disabled' : ''; ?>
>
	<?php echo $data->content; ?>
</textarea>
