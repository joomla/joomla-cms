<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.codemirror
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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

$showToolbar = $params->get('toolbar.useToolbar', true);

$layoutPath = JPATH_PLUGINS . '/editors/codemirror/layouts';

if ($showToolbar)
{
	$options->toolbarId = $id . 'Toolbar';
}

JFactory::getDocument()->addScriptDeclaration('
	jQuery(function () {
		var id = ' . json_encode($id) . ', options = ' . json_encode($options) . ';
		Joomla.editors.instances[id] = CodeMirror.fromTextArea(document.getElementById(id), options);
	});
');
?>

<?php echo '<textarea name="', $name, '" id="', $id, '" cols="', $cols, '" rows="', $rows, '">', $content, '</textarea>'; ?>

<?php echo $displayData->buttons; ?>

<?php if ($showToolbar) : ?>
	<?php echo JLayoutHelper::render('editors.codemirror.toolbar', $displayData, $layoutPath, array('debug' => JDEBUG)); ?>
<?php endif; ?>
