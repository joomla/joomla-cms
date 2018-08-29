<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

$options = $displayData->options;
$params  = $displayData->params;
$name    = $displayData->name;
$id      = $displayData->id;
$cols    = $displayData->cols;
$rows    = $displayData->rows;
$content = $displayData->content;
$buttons = $displayData->buttons;
$modifier = $params->get('fullScreenMod', '') !== '' ? implode($params->get('fullScreenMod', ''), ' + ') . ' + ' : '';

?>

<p class="label">
    <?php echo JText::sprintf('PLG_CODEMIRROR_TOGGLE_FULL_SCREEN', $modifier, $params->get('fullScreen', 'F10')); ?>
</p>

<?php
	echo '<textarea class="codemirror-source" name="', $name,
		'" id="', $id,
		'" cols="', $cols,
		'" rows="', $rows,
		'" data-options="', htmlspecialchars(json_encode($options)),
		'">', $content, '</textarea>';
?>

<?php echo $buttons; ?>
